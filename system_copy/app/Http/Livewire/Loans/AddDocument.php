<?php

namespace App\Http\Livewire\Loans;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use App\Models\LoansModel;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use App\Services\LoanApplication\LoanDocumentService;

class AddDocument extends Component
{
    use WithFileUploads;

    // Document upload properties
    public $documentFile;
    public $documentCategory = 'general';
    public $documentDescription = '';
    public $uploadedDocuments = [];
    public $uploadedDocumentsCount = 0;
    public $isUploading = false;
    public $uploadProgress = 0;
    public $isDragging = false;
    
    // Loan information
    public $loan;
    public $loanId;
    
    // Messages
    public $errorMessage = '';
    public $successMessage = '';
    
    // Validation warnings
    public $warnings = [];
    
    // Debug properties
    public $showDebugInfo = false;

    protected $listeners = [
        'refreshDocuments' => '$refresh',
        'tabCompleted' => 'markTabAsCompleted'
    ];

    protected $rules = [
        'documentFile' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png',
        'documentDescription' => 'required|min:3',
        'documentCategory' => 'required|in:general,identity,financial,collateral,other',
    ];

    protected $messages = [
        'documentFile.required' => 'Please select a file to upload.',
        'documentFile.max' => 'File size must not exceed 10MB.',
        'documentFile.mimes' => 'Only PDF, DOC, DOCX, JPG, and PNG files are allowed.',
        'documentDescription.required' => 'Please provide a description for the document.',
        'documentDescription.min' => 'Description must be at least 3 characters.',
        'documentCategory.required' => 'Please select a document category.',
    ];

    public function mount()
    {
        $this->loanId = Session::get('currentloanID');
        if ($this->loanId) {
            $this->loan = LoansModel::find($this->loanId);
            $this->loadExistingDocuments();
        }

       
        
        Log::info('AddDocument component mounted', [
            'loan_id' => $this->loanId,
            'existing_documents' => count($this->uploadedDocuments)
        ]);
    }

    public function loadExistingDocuments()
    {
        try {
            \Log::info('AddDocument: loadExistingDocuments called', [
                'loan_id' => $this->loan->id,
                'loan_loan_id' => $this->loan->loan_id ?? 'N/A',
                'loan_type' => $this->loan->loan_type_2 ?? 'New',
            ]);
            
            // Use the loan_id string field, not the numeric id
            // The loan_images table uses loan_id string field to match with loans.loan_id
            $loanIdToQuery = $this->loan->loan_id ?? $this->loan->id;
            
            // For restructure loans, get documents from the loan being restructured
            if ($this->loan->loan_type_2 === 'Restructure' && $this->loan->restructured_loan) {
                // The restructured_loan field should contain the loan_id string
                $loanIdToQuery = $this->loan->restructured_loan;
                \Log::info('AddDocument: Loading documents from restructured loan', [
                    'restructured_loan_id' => $loanIdToQuery,
                    'current_loan_id' => $this->loan->loan_id,
                ]);
            }
            
            // Load existing documents from loan_images table
            // Try both the string loan_id and numeric id
            $documents = DB::table('loan_images')
                ->where(function($query) use ($loanIdToQuery) {
                    $query->where('loan_id', $loanIdToQuery)
                          ->orWhere('loan_id', (string)$this->loan->id)
                          ->orWhere('loan_id', $this->loan->loan_id ?? 'none');
                })
                ->get();

            \Log::info('AddDocument: Documents loaded', [
                'document_count' => $documents->count(),
                'loan_id_queried' => $loanIdToQuery,
            ]);
            
            $this->uploadedDocuments = [];
            foreach ($documents as $doc) {
                $this->uploadedDocuments[] = [
                    'id' => $doc->id,
                    'filename' => $doc->original_name ?? $doc->filename,
                    'description' => $doc->document_descriptions,
                    'category' => $doc->category,
                    'size' => $doc->file_size,
                    'path' => $doc->url,
                    'is_existing' => $this->loan->loan_type_2 === 'Restructure' && $this->loan->restructured_loan ? true : false,
                    'original_document_id' => $doc->id
                ];
            }
            
            $this->uploadedDocumentsCount = count($this->uploadedDocuments);
            
            \Log::info('AddDocument: Documents pre-populated', [
                'uploaded_documents_count' => $this->uploadedDocumentsCount,
                'is_restructure_loan' => $this->loan->loan_type_2 === 'Restructure',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading existing documents: ' . $e->getMessage());
        }
    }

    public function uploadDocument()
    {
        \Log::info('AddDocument: uploadDocument method called', [
            'loan_id' => $this->loanId,
            'loan_loan_id' => $this->loan ? $this->loan->loan_id : 'N/A',
            'has_file' => $this->documentFile ? true : false,
            'category' => $this->documentCategory,
            'description' => $this->documentDescription,
        ]);
        
        $this->validate();

        $this->isUploading = true;
        $this->uploadProgress = 0;

        try {
            // Simulate initial progress
            $this->uploadProgress = 30;
            
            \Log::info('AddDocument: Calling LoanDocumentService', [
                'file_name' => $this->documentFile ? $this->documentFile->getClientOriginalName() : 'N/A',
                'file_size' => $this->documentFile ? $this->documentFile->getSize() : 0,
            ]);
            
            // Use LoanDocumentService for consistent file handling
            $uploadService = new LoanDocumentService();
            $uploadedFile = $uploadService->uploadDocument(
                $this->documentFile,
                $this->documentCategory,
                $this->documentDescription,
                $this->loanId
            );
            
            \Log::info('AddDocument: LoanDocumentService response', [
                'success' => $uploadedFile['success'] ?? false,
                'error' => $uploadedFile['error'] ?? 'N/A',
                'document' => $uploadedFile['document'] ?? [],
            ]);
            
            $this->uploadProgress = 60;

            if ($uploadedFile['success']) {
                // Save to loan_images table with loan association
                // Use the string loan_id field from the loan model
                $loanIdForSave = $this->loan ? $this->loan->loan_id : $this->loanId;
                
                \Log::info('AddDocument: Preparing to save to loan_images table', [
                    'loan_id_for_save' => $loanIdForSave,
                    'filename' => $uploadedFile['document']['filename'] ?? 'N/A',
                    'path' => $uploadedFile['document']['path'] ?? 'N/A',
                ]);
                
                $insertData = [
                    'loan_id' => $loanIdForSave,
                    'filename' => $uploadedFile['document']['filename'],
                    'original_name' => $uploadedFile['document']['original_name'] ?? $this->documentFile->getClientOriginalName(),
                    'url' => $uploadedFile['document']['path'],
                    'file_size' => $uploadedFile['document']['size'],
                    'mime_type' => $this->documentFile->getMimeType(),
                    'category' => $this->documentCategory,
                    'document_category' => $this->documentCategory,
                    'document_descriptions' => $this->documentDescription,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                
                \Log::info('AddDocument: Insert data prepared', [
                    'data' => $insertData
                ]);
                
                $documentId = DB::table('loan_images')->insertGetId($insertData);
                
                \Log::info('AddDocument: Document saved to loan_images table', [
                    'document_id' => $documentId,
                    'loan_id' => $loanIdForSave,
                ]);
                
                $this->uploadProgress = 90;
                
                // Add to uploaded documents array
                $newDocument = [
                    'id' => $documentId,
                    'filename' => $uploadedFile['document']['original_name'] ?? $this->documentFile->getClientOriginalName(),
                    'description' => $this->documentDescription,
                    'category' => $this->documentCategory,
                    'size' => $uploadedFile['document']['size'],
                    'path' => $uploadedFile['document']['path'],
                    'is_existing' => false
                ];
                
                $this->uploadedDocuments[] = $newDocument;
                $this->uploadedDocumentsCount = count($this->uploadedDocuments);
                
                // Store in session for loan application if needed
                if ($this->loan && in_array($this->loan->loan_type_2, ['Top-up', 'Restructuring', 'Restructure'])) {
                    $committedDocs = session('committedDocumentsData', []);
                    $committedDocs[] = $newDocument;
                    session(['committedDocumentsData' => $committedDocs]);
                }
                
                $this->uploadProgress = 100;
                
                // Clear form
                $this->reset(['documentFile', 'documentDescription']);
                $this->documentCategory = 'general';
                
                $this->successMessage = 'Document uploaded successfully.';
                session()->flash('success', 'Document uploaded successfully.');
                
                // Mark tab as completed if we have documents
                if ($this->uploadedDocumentsCount > 0) {
                    $this->emit('tabCompleted', 'addDocument');
                }
            } else {
                throw new \Exception($uploadedFile['error'] ?? 'Failed to upload document');
            }
            
            $this->isUploading = false;
            $this->uploadProgress = 0;
            
        } catch (\Exception $e) {
            Log::error('Error uploading document', [
                'loan_id' => $this->loanId,
                'error' => $e->getMessage()
            ]);
            $this->errorMessage = 'Error uploading document: ' . $e->getMessage();
            $this->isUploading = false;
            $this->uploadProgress = 0;
            session()->flash('error', 'Failed to upload document: ' . $e->getMessage());
        }
    }

    public function removeDocument($index)
    {
        if (isset($this->uploadedDocuments[$index])) {
            $document = $this->uploadedDocuments[$index];
            
            try {
                // Delete from loan_images table
                if (isset($document['id'])) {
                    DB::table('loan_images')
                        ->where('id', $document['id'])
                        ->delete();
                }
                
                // Delete file from storage
                if (Storage::disk('public')->exists($document['path'])) {
                    Storage::disk('public')->delete($document['path']);
                }
                
                // Remove from array
                unset($this->uploadedDocuments[$index]);
                $this->uploadedDocuments = array_values($this->uploadedDocuments);
                $this->uploadedDocumentsCount = count($this->uploadedDocuments);
                
                $this->successMessage = 'Document removed successfully.';
                session()->flash('success', 'Document removed successfully.');
                
            } catch (\Exception $e) {
                Log::error('Error removing document: ' . $e->getMessage());
                $this->errorMessage = 'Error removing document.';
            }
        }
    }

    public function downloadDocument($index)
    {
        if (isset($this->uploadedDocuments[$index])) {
            $document = $this->uploadedDocuments[$index];
            
            \Log::info('AddDocument: Downloading document', [
                'index' => $index,
                'filename' => $document['filename'],
                'path' => $document['path'],
                'is_existing' => $document['is_existing'] ?? false,
            ]);
            
            // For existing documents from restructure loans, get the original document
            if (isset($document['is_existing']) && $document['is_existing'] && isset($document['original_document_id'])) {
                $originalDocument = DB::table('loan_images')
                    ->where('id', $document['original_document_id'])
                    ->first();
                
                if ($originalDocument && Storage::disk('public')->exists($originalDocument->url)) {
                    return Storage::disk('public')->download($originalDocument->url, $document['filename']);
                }
            } else {
                // For regular documents
                if (Storage::disk('public')->exists($document['path'])) {
                    return Storage::disk('public')->download($document['path'], $document['filename']);
                }
            }
            
            \Log::warning('AddDocument: Document file not found for download', [
                'filename' => $document['filename'],
                'path' => $document['path'],
            ]);
        }
    }

    public function markTabAsCompleted()
    {
        if ($this->uploadedDocumentsCount > 0) {
            $this->emit('tabCompleted', 'addDocument');
        }
    }
    
    /**
     * Refresh document data for restructure loans
     * This method can be called to reload existing document data
     */
    public function refreshDocumentData()
    {
        \Log::info('AddDocument: refreshDocumentData called', [
            'loan_type' => $this->loan->loan_type_2 ?? 'New',
            'loan_id' => $this->loan->id,
        ]);
        
        if ($this->loan->loan_type_2 === 'Restructure') {
            // Reload existing documents
            $this->loadExistingDocuments();
            
            \Log::info('AddDocument: Document data refreshed for restructure loan', [
                'uploaded_documents_count' => $this->uploadedDocumentsCount,
                'existing_documents' => count(array_filter($this->uploadedDocuments, function($doc) {
                    return isset($doc['is_existing']) && $doc['is_existing'];
                })),
            ]);
        }
    }
    
    /**
     * Refresh debug information
     * This method reloads documents and debug data
     */
    public function refreshDebugInfo()
    {
        \Log::info('AddDocument: refreshDebugInfo called', [
            'loan_id' => $this->loanId,
            'showDebugInfo' => $this->showDebugInfo,
        ]);
        
        // Reload the loan
        if ($this->loanId) {
            $this->loan = LoansModel::find($this->loanId);
        }
        
        // Reload documents
        $this->loadExistingDocuments();
        
        // Force refresh the component
        $this->emit('$refresh');
        
        \Log::info('AddDocument: Debug info refreshed', [
            'uploaded_documents_count' => $this->uploadedDocumentsCount,
            'documents_array' => $this->uploadedDocuments,
        ]);
    }
    
    /**
     * Test method to directly save a document record to loan_images table
     * This is for debugging purposes
     */
    public function testSaveDocument()
    {
        try {
            \Log::info('AddDocument: testSaveDocument called', [
                'loan_id' => $this->loanId,
                'loan_loan_id' => $this->loan ? $this->loan->loan_id : 'N/A',
            ]);
            
            // Use the string loan_id from the loan model, which is what loan_images table expects
            $loanIdToUse = $this->loan ? $this->loan->loan_id : 'LN' . date('YmdHis');
            
            // Create a test document record
            $testData = [
                'loan_id' => $loanIdToUse,
                'filename' => 'test_document_' . time() . '.pdf',
                'original_name' => 'Test Document.pdf',
                'url' => 'loan_applications/documents/general/test_' . time() . '.pdf',
                'file_size' => 1024,
                'mime_type' => 'application/pdf',
                'category' => 'general',
                'document_category' => 'general',
                'document_descriptions' => 'Test document created for debugging',
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            \Log::info('AddDocument: Attempting to insert test document', [
                'data' => $testData
            ]);
            
            $insertId = DB::table('loan_images')->insertGetId($testData);
            
            \Log::info('AddDocument: Test document inserted successfully', [
                'insert_id' => $insertId
            ]);
            
            // Verify the insert
            $verifyRecord = DB::table('loan_images')->find($insertId);
            
            \Log::info('AddDocument: Verification of inserted record', [
                'record_found' => $verifyRecord ? true : false,
                'record' => $verifyRecord
            ]);
            
            // Add to the uploaded documents array for display
            $this->uploadedDocuments[] = [
                'id' => $insertId,
                'filename' => $testData['original_name'],
                'description' => $testData['document_descriptions'],
                'category' => $testData['category'],
                'size' => $testData['file_size'],
                'path' => $testData['url'],
                'is_existing' => false
            ];
            
            $this->uploadedDocumentsCount = count($this->uploadedDocuments);
            
            $this->successMessage = 'Test document created successfully with ID: ' . $insertId;
            session()->flash('success', 'Test document saved to database successfully!');
            
            // Reload documents to confirm
            $this->loadExistingDocuments();
            
        } catch (\Exception $e) {
            \Log::error('AddDocument: Error in testSaveDocument', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->errorMessage = 'Error saving test document: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.loans.add-document');
    }
}