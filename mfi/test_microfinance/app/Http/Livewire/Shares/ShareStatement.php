<?php

namespace App\Http\Livewire\Shares;

use Livewire\Component;
use App\Services\ShareStatementService;
use App\Models\sub_products;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class ShareStatement extends Component
{
    // Filter Properties
    public $clientNumber = '';
    public $selectedProduct = '';
    public $startDate;
    public $endDate;
    public $searchTerm = '';

    // Data Properties
    public $statementData = null;
    public $consolidatedData = null;
    public $members = [];
    public $products = [];
    public $summary = null;

    // UI State
    public $isLoading = false;
    public $showStatement = false;
    public $errorMessage = '';
    public $successMessage = '';

    // View Mode
    public $viewMode = 'search'; // 'search', 'statement', or 'consolidated'

    protected $shareStatementService;

    public function boot(ShareStatementService $shareStatementService)
    {
        $this->shareStatementService = $shareStatementService;
    }

    public function mount()
    {
        // Set default date range (current year)
        $this->startDate = Carbon::now()->startOfYear()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');

        $this->loadProducts();
        $this->loadSummary();
        $this->searchMembers(); // Load all members on initial load
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'searchTerm') {
            $this->searchMembers();
        }
    }

    /**
     * Load available share products
     */
    public function loadProducts()
    {
        try {
            $this->products = sub_products::where('product_type', 3000) // Share products
                ->where('status', 'ACTIVE')
                ->select('id', 'sub_product_id', 'product_name', 'nominal_price', 'interest_value')
                ->get();
        } catch (Exception $e) {
            Log::error('Error loading share products: ' . $e->getMessage());
            $this->products = collect();
        }
    }

    /**
     * Load summary statistics
     */
    public function loadSummary()
    {
        try {
            $result = $this->shareStatementService->getStatementSummary(
                $this->selectedProduct ?: null
            );

            if ($result['status'] === 'success') {
                $this->summary = $result['summary'];
            }
        } catch (Exception $e) {
            Log::error('Error loading summary: ' . $e->getMessage());
        }
    }

    /**
     * Search for members with shares
     */
    public function searchMembers()
    {
        try {
            $this->members = $this->shareStatementService->searchMembers($this->searchTerm);
        } catch (Exception $e) {
            Log::error('Error searching members: ' . $e->getMessage());
            $this->errorMessage = 'Failed to search members.';
        }
    }

    /**
     * Select a member to view statement
     */
    public function selectMember($clientNumber)
    {
        $this->clientNumber = $clientNumber;
        $this->generateStatement();
    }

    /**
     * Generate statement for selected member
     */
    public function generateStatement()
    {
        try {
            $this->isLoading = true;
            $this->errorMessage = '';
            $this->successMessage = '';

            // Validate inputs
            if (empty($this->clientNumber)) {
                $this->errorMessage = 'Please select a member.';
                return;
            }

            if (empty($this->startDate) || empty($this->endDate)) {
                $this->errorMessage = 'Please select both start and end dates.';
                return;
            }

            // Generate statement
            $result = $this->shareStatementService->generateStatement(
                $this->clientNumber,
                $this->selectedProduct ?: null,
                Carbon::parse($this->startDate),
                Carbon::parse($this->endDate)
            );

            if ($result['status'] === 'success') {
                $this->statementData = $result;
                $this->viewMode = 'statement';
                $this->showStatement = true;
                $this->successMessage = 'Statement generated successfully.';
            } else {
                $this->errorMessage = $result['message'] ?? 'Failed to generate statement.';
                $this->statementData = null;
            }

        } catch (Exception $e) {
            Log::error('Error generating statement: ' . $e->getMessage());
            $this->errorMessage = 'An error occurred while generating the statement.';
            $this->statementData = null;
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Generate consolidated statement for all members
     */
    public function generateAllMembersStatement()
    {
        try {
            $this->isLoading = true;
            $this->errorMessage = '';
            $this->successMessage = '';

            // Validate dates
            if (empty($this->startDate) || empty($this->endDate)) {
                $this->errorMessage = 'Please select both start and end dates.';
                return;
            }

            // Generate consolidated statement
            $result = $this->shareStatementService->generateConsolidatedStatement(
                $this->selectedProduct ?: null,
                Carbon::parse($this->startDate),
                Carbon::parse($this->endDate)
            );

            if ($result['status'] === 'success') {
                $this->consolidatedData = $result;
                $this->viewMode = 'consolidated';
                $this->successMessage = 'Consolidated statement generated successfully.';
            } else {
                $this->errorMessage = $result['message'] ?? 'Failed to generate consolidated statement.';
                $this->consolidatedData = null;
            }

        } catch (Exception $e) {
            Log::error('Error generating consolidated statement: ' . $e->getMessage());
            $this->errorMessage = 'An error occurred while generating the consolidated statement.';
            $this->consolidatedData = null;
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Back to search view
     */
    public function backToSearch()
    {
        $this->viewMode = 'search';
        $this->showStatement = false;
        $this->statementData = null;
        $this->consolidatedData = null;
        $this->errorMessage = '';
        $this->successMessage = '';

        // Reload members list
        $this->searchMembers();
    }

    /**
     * Refresh data
     */
    public function refreshData()
    {
        $this->loadProducts();
        $this->loadSummary();
        $this->searchMembers();
        $this->successMessage = 'Data refreshed successfully.';
    }

    /**
     * Clear filters
     */
    public function clearFilters()
    {
        $this->clientNumber = '';
        $this->selectedProduct = '';
        $this->startDate = Carbon::now()->startOfYear()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
        $this->searchTerm = '';
        $this->backToSearch(); // This will reload members
    }

    /**
     * Export to PDF
     */
    public function exportToPDF()
    {
        try {
            if (!$this->statementData) {
                $this->errorMessage = 'No statement data to export.';
                return;
            }

            // TODO: Implement PDF export
            $this->successMessage = 'PDF export functionality coming soon.';
        } catch (Exception $e) {
            Log::error('Error exporting to PDF: ' . $e->getMessage());
            $this->errorMessage = 'Failed to export statement.';
        }
    }

    /**
     * Export to Excel
     */
    public function exportToExcel()
    {
        try {
            if (!$this->statementData) {
                $this->errorMessage = 'No statement data to export.';
                return;
            }

            // TODO: Implement Excel export
            $this->successMessage = 'Excel export functionality coming soon.';
        } catch (Exception $e) {
            Log::error('Error exporting to Excel: ' . $e->getMessage());
            $this->errorMessage = 'Failed to export statement.';
        }
    }

    /**
     * Generate share certificate for a member
     */
    public function generateCertificate($clientNumber)
    {
        try {
            $this->errorMessage = '';
            $this->successMessage = '';

            // Get certificate data
            $result = $this->shareStatementService->getCertificateData($clientNumber);

            if ($result['status'] === 'error') {
                $this->errorMessage = $result['message'] ?? 'Failed to generate certificate.';
                return;
            }

            // Store certificate data in session for download route
            session(['certificate_data' => $result, 'certificate_client' => $clientNumber]);

            // Redirect to download route
            return redirect()->route('share.certificate.download');

        } catch (Exception $e) {
            Log::error('Error generating certificate: ' . $e->getMessage());
            $this->errorMessage = 'An error occurred while generating the certificate.';
        }
    }

    /**
     * Print statement
     */
    public function printStatement()
    {
        $this->dispatchBrowserEvent('print-statement');
    }

    /**
     * Clear messages
     */
    public function clearMessages()
    {
        $this->errorMessage = '';
        $this->successMessage = '';
    }

    public function render()
    {
        return view('livewire.shares.share-statement');
    }
}
