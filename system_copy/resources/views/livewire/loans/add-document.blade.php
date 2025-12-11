<div class="space-y-4">
   
    
    <!-- Existing Documents Notice for Restructure Loans -->
    @if(isset($loan) && $loan->loan_type_2 === 'Restructure')
        @php
            $existingDocuments = array_filter($uploadedDocuments, function($doc) {
                return isset($doc['is_existing']) && $doc['is_existing'];
            });
            $existingCount = count($existingDocuments);
        @endphp
        
        @if($existingCount > 0)
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-orange-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <h3 class="text-sm font-semibold text-orange-900">Existing Documents from Restructured Loan</h3>
                    </div>
                    <button type="button" 
                            wire:click="refreshDocumentData"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="px-2 py-1 bg-orange-600 text-white text-xs rounded hover:bg-orange-700 transition-colors font-medium inline-flex items-center">
                        <svg wire:loading.remove class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <svg wire:loading class="animate-spin w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove>Refresh</span>
                        <span wire:loading>Refreshing...</span>
                    </button>
                </div>
                <p class="text-xs text-orange-700">
                    {{ $existingCount }} document{{ $existingCount > 1 ? 's' : '' }} from the original loan have been pre-loaded. 
                    You can download them or upload additional documents below.
                </p>
            </div>
        @endif
    @endif


    
    <!-- Uploaded Documents List -->
    <div>
        <div class="flex items-center justify-between mb-3">
      
            @if($uploadedDocumentsCount > 0)
                <p class="text-xs text-green-600 flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Ready
                </p>
            @endif
        </div>
        
        @if($uploadedDocumentsCount > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-xs border border-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-2 py-1 text-left border-b border-r border-gray-200">File</th>
                            <th class="px-2 py-1 text-left border-b border-r border-gray-200">Description</th>
                            <th class="px-2 py-1 text-left border-b border-r border-gray-200">Category</th>
                            <th class="px-2 py-1 text-right border-b border-r border-gray-200">Size (KB)</th>
                            <th class="px-2 py-1 text-center border-b border-gray-200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($uploadedDocuments as $index => $doc)
                            <tr class="border-b">
                                <td class="px-2 py-1 border-r border-gray-200">
                                    <div class="flex items-center space-x-2">
                                        @php
                                            $extension = pathinfo($doc['filename'], PATHINFO_EXTENSION);
                                            $isPdf = in_array(strtolower($extension), ['pdf']);
                                            $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png']);
                                            $isDoc = in_array(strtolower($extension), ['doc', 'docx']);
                                        @endphp
                                        
                                        @if($isPdf)
                                            <div class="w-6 h-6 bg-red-100 rounded flex items-center justify-center">
                                                <svg class="w-3 h-3 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20M10,19L8,14H10L11,17L12,14H14L12,19H10Z"></path>
                                                </svg>
                                            </div>
                                        @elseif($isImage)
                                            <div class="w-6 h-6 bg-green-100 rounded flex items-center justify-center">
                                                <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        @elseif($isDoc)
                                            <div class="w-6 h-6 bg-blue-100 rounded flex items-center justify-center">
                                                <svg class="w-3 h-3 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20M13,17V11H10V13H8V11A2,2 0 0,1 10,9H14A2,2 0 0,1 16,11V17A2,2 0 0,1 14,19H10A2,2 0 0,1 8,17V15H10V17H13Z"></path>
                                                </svg>
                                            </div>
                                        @else
                                            <div class="w-6 h-6 bg-gray-100 rounded flex items-center justify-center">
                                                <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                        <span class="font-medium text-gray-900">{{ $doc['filename'] }}</span>
                                    </div>
                                </td>
                                <td class="px-2 py-1 border-r border-gray-200 text-gray-600">
                                    {{ $doc['description'] }}
                                </td>
                                <td class="px-2 py-1 border-r border-gray-200">
                                    <span class="px-1 py-0.5 bg-gray-100 text-gray-600 rounded text-xs">
                                        {{ ucfirst($doc['category']) }}
                                    </span>
                                    @if(isset($doc['is_existing']) && $doc['is_existing'])
                                        <span class="ml-1 px-1 py-0.5 bg-orange-100 text-orange-600 rounded text-xs">
                                            Existing
                                        </span>
                                    @endif
                                </td>
                                <td class="px-2 py-1 text-right border-r border-gray-200 font-semibold text-blue-900">
                                    {{ number_format((float)($doc['size'] / 1024), 1) }}
                                </td>
                                <td class="px-2 py-1 text-center">
                                    <div class="flex justify-center space-x-1">
                                        <button type="button" 
                                                wire:click="downloadDocument({{ $index }})"
                                                class="p-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 transition-colors"
                                                title="Download">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                                            </svg>
                                        </button>
                                        <button type="button" 
                                                wire:click="removeDocument({{ $index }})"
                                                class="p-1 bg-red-100 text-red-600 rounded hover:bg-red-200 transition-colors"
                                                title="Remove">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
          
        @else
            <div class="text-center py-8 bg-gray-50 rounded-lg border border-gray-200">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="mt-2 text-sm text-gray-600">No documents uploaded</p>
                
            </div>
        @endif
    </div>
</div>