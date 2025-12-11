<div class="container mx-auto p-6">
    {{-- Stepper Navigation --}}
    <div class="mb-6 flex items-center space-x-4">
        <div class="flex items-center">
            <div class="rounded-full h-8 w-8 flex items-center justify-center {{ $currentStep >= 1 ? 'bg-red-600 text-white' : 'bg-gray-300 text-gray-600' }}">1</div>
            <span class="ml-2 {{ $currentStep >= 1 ? 'font-bold text-red-700' : 'text-gray-500' }}">Upload</span>
        </div>
        <div class="h-1 w-8 bg-gray-300"></div>
        <div class="flex items-center">
            <div class="rounded-full h-8 w-8 flex items-center justify-center {{ $currentStep >= 2 ? 'bg-red-600 text-white' : 'bg-gray-300 text-gray-600' }}">2</div>
            <span class="ml-2 {{ $currentStep >= 2 ? 'font-bold text-red-700' : 'text-gray-500' }}">Preview</span>
        </div>
        <div class="h-1 w-8 bg-gray-300"></div>
        <div class="flex items-center">
            <div class="rounded-full h-8 w-8 flex items-center justify-center {{ $currentStep >= 3 ? 'bg-red-600 text-white' : 'bg-gray-300 text-gray-600' }}">3</div>
            <span class="ml-2 {{ $currentStep >= 3 ? 'font-bold text-red-700' : 'text-gray-500' }}">Process</span>
        </div>
        <div class="h-1 w-8 bg-gray-300"></div>
        <div class="flex items-center">
            <div class="rounded-full h-8 w-8 flex items-center justify-center {{ $currentStep == 4 ? 'bg-red-600 text-white' : 'bg-gray-300 text-gray-600' }}">4</div>
            <span class="ml-2 {{ $currentStep == 4 ? 'font-bold text-red-700' : 'text-gray-500' }}">Results</span>
        </div>
    </div>

    {{-- Step 1: Upload --}}
    @if($currentStep === 1)
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-semibold mb-4 text-gray-900">Bulk Client Registration Upload</h2>
            
            <div class="mb-6 p-4 bg-red-50 rounded-lg border border-red-200">
                <h3 class="font-semibold text-red-900 mb-2">Instructions:</h3>
                <ul class="text-sm text-red-800 space-y-1">
                    <li>• Download the template file to see the required format</li>
                    <li>• Fill in all required fields marked with *</li>
                    <li>• For Individual clients: fill personal details (First Name, Last Name, etc.)</li>
                    <li>• For Business/Group clients: fill business details (Business Name, Incorporation Number)</li>
                    <li>• Ensure guarantor client number exists and is active</li>
                    <li>• Phone numbers must be in format: 0XXXXXXXXX</li>
                    <li>• Date of Birth must be in format: YYYY-MM-DD</li>
                </ul>
            </div>

            <div class="mb-4">
                <button wire:click="downloadTemplate" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download Template
                </button>
            </div>

            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                <input type="file" wire:model="uploadFile" class="hidden" id="file-upload" accept=".csv,.xlsx,.xls">
                <label for="file-upload" class="cursor-pointer">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <p class="mt-1 text-sm text-gray-600">
                        <span class="font-medium text-red-600 hover:text-red-500">Click to upload</span> or drag and drop
                    </p>
                    <p class="mt-1 text-xs text-gray-500">Excel or CSV files up to 10MB</p>
                </label>
            </div>

            @error('uploadFile') 
                <div class="mt-2 text-red-600 text-sm">{{ $message }}</div> 
            @enderror

            <div wire:loading wire:target="uploadFile" class="mt-2 text-red-600 text-sm">
                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Uploading...
            </div>

            <!-- processing status -->
            <div class="mt-4 text-sm text-gray-600">
                {{ $processingStatus }}
            </div>
            
            @if($uploadFile)
                <div class="mt-4">
                    <button wire:click="processFilePreview" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors" @if($isUploading) disabled @endif>
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Preview File
                    </button>
                </div>
            @endif
        </div>
    @endif

    {{-- Step 2: Preview --}}
    @if($currentStep === 2)
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-semibold mb-4 text-gray-900">Preview & Validate</h2>
            <div class="mb-4 text-gray-600">Showing first {{ count($previewData) }} rows. Please review and fix any errors before proceeding.</div>
            
            <div class="mb-4 flex items-center justify-between">
                <div class="text-sm">
                    <span class="text-green-600 font-bold">{{ $successCount }}</span> valid,
                    <span class="text-red-600 font-bold">{{ $errorCount }}</span> errors,
                    <span class="text-gray-600 font-bold">{{ $skippedCount }}</span> skipped.
                </div>
                @if($errorCount > 0)
                    <div class="text-red-600 text-sm font-medium">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Please fix {{ $errorCount }} error(s) before proceeding.
                    </div>
                @else
                    <div class="text-green-600 text-sm font-medium">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        All data is valid. You can proceed.
                    </div>
                @endif
            </div>

            <div class="overflow-x-auto border rounded-lg">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="p-3 text-left font-medium text-gray-700">Row</th>
                            <th class="p-3 text-left font-medium text-gray-700">Membership Type</th>
                            <th class="p-3 text-left font-medium text-gray-700">Branch</th>
                            <th class="p-3 text-left font-medium text-gray-700">Phone</th>
                            <th class="p-3 text-left font-medium text-gray-700">Name</th>
                            <th class="p-3 text-left font-medium text-gray-700">Address</th>
                            <th class="p-3 text-left font-medium text-gray-700">Guarantor</th>
                            <th class="p-3 text-left font-medium text-gray-700">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($previewData as $i => $row)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-3 text-gray-600">{{ $i + 2 }}</td>
                                <td class="p-3">{{ $row[0] ?? '' }}</td>
                                <td class="p-3">{{ $row[1] ?? '' }}</td>
                                <td class="p-3">{{ $row[2] ?? '' }}</td>
                                <td class="p-3">
                                    @if(strtolower($row[0] ?? '') === 'individual')
                                        {{ ($row[14] ?? '') . ' ' . ($row[15] ?? '') }}
                                    @else
                                        {{ $row[21] ?? '' }}
                                    @endif
                                </td>
                                <td class="p-3">{{ $row[3] ?? '' }}</td>
                                <td class="p-3">{{ $row[11] ?? '' }}</td>
                                <td class="p-3">
                                    @if(isset($validationErrors[$i + 2]))
                                        <div class="text-red-600">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ count($validationErrors[$i + 2]) }} error(s)
                                        </div>
                                    @else
                                        <div class="text-green-600">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Valid
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @if(isset($validationErrors[$i + 2]))
                                <tr class="bg-red-50">
                                    <td colspan="8" class="p-3">
                                        <ul class="text-red-600 text-xs list-disc pl-4">
                                            @foreach($validationErrors[$i + 2] as $err)
                                                <li>{{ $err }}</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between mt-6">
                <button wire:click="goBack" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back
                </button>
                <button wire:click="processBulkUpload" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors" @if($errorCount > 0) disabled @endif>
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Process Bulk Import
                </button>
            </div>
        </div>
    @endif

    {{-- Step 3: Processing --}}
    @if($currentStep === 3)
        <div class="bg-white p-6 rounded-lg shadow-lg flex flex-col items-center">
            <h2 class="text-xl font-semibold mb-4 text-gray-900">Processing Bulk Import</h2>
            
            <div class="w-full max-w-md bg-gray-200 rounded-full h-4 mb-4">
                <div class="bg-red-600 h-4 rounded-full transition-all duration-300" style="width: {{ $uploadProgress }}%"></div>
            </div>
            
            <div class="text-gray-700 mb-2 text-center">{{ $processingStatus }}</div>
            <div class="text-sm text-gray-500 text-center">Please wait, do not close this window.</div>
            
            <div class="mt-4 text-sm text-gray-600">
                Processed: {{ $processedRecords }} / {{ $totalRecords }}
            </div>
        </div>
    @endif

    {{-- Step 4: Results --}}
    @if($currentStep === 4)
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-semibold mb-4 text-gray-900">Bulk Import Results</h2>
            
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div class="bg-green-100 p-3 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">{{ $successCount }}</div>
                        <div class="text-sm text-green-700">Successful</div>
                    </div>
                    <div class="bg-red-100 p-3 rounded-lg">
                        <div class="text-2xl font-bold text-red-600">{{ $errorCount }}</div>
                        <div class="text-sm text-red-700">Failed</div>
                    </div>
                    <div class="bg-gray-100 p-3 rounded-lg">
                        <div class="text-2xl font-bold text-gray-600">{{ $skippedCount }}</div>
                        <div class="text-sm text-gray-700">Skipped</div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto max-h-96 border rounded-lg">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr class="border-b">
                            <th class="p-3 text-left font-medium text-gray-700">Row</th>
                            <th class="p-3 text-left font-medium text-gray-700">Status</th>
                            <th class="p-3 text-left font-medium text-gray-700">Message</th>
                            <th class="p-3 text-left font-medium text-gray-700">Client Number</th>
                            <th class="p-3 text-left font-medium text-gray-700">Client Name</th>
                            <th class="p-3 text-left font-medium text-gray-700">Type</th>
                            <th class="p-3 text-left font-medium text-gray-700">Phone</th>
                            <th class="p-3 text-left font-medium text-gray-700">Control Numbers</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($processingResults as $result)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-3 text-gray-600">{{ $result['row'] ?? '' }}</td>
                                <td class="p-3">
                                    @if($result['status'] === 'success')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Success
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Error
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3 text-sm">
                                    <div class="max-w-xs truncate" title="{{ $result['message'] ?? '' }}">
                                        {{ $result['message'] ?? '' }}
                                    </div>
                                </td>
                                <td class="p-3 font-mono text-sm">{{ $result['reference'] ?? '' }}</td>
                                <td class="p-3">{{ $result['client'] ?? '' }}</td>
                                <td class="p-3">{{ $result['membership_type'] ?? '' }}</td>
                                <td class="p-3">{{ $result['phone'] ?? '' }}</td>
                                <td class="p-3 text-center">{{ $result['control_numbers'] ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between mt-6">
                <button wire:click="startNewUpload" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    New Upload
                </button>
                
                @if($successCount > 0)
                    <div class="text-green-600 text-sm">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $successCount }} clients successfully registered!
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                {{ session('error') }}
            </div>
        </div>
    @endif
</div>

<style nonce="{{ csp_nonce() }}">
    .animate-spin {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }
</style>
