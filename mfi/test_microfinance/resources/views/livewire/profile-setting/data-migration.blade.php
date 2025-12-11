<div class="space-y-6">
    {{-- Header Section --}}
    <div class="bg-gradient-to-r from-red-50 to-indigo-50 rounded-xl p-6 border border-red-100">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Data Migration Center
                </h3>
                <p class="mt-2 text-sm text-gray-600">Import and migrate data from external sources into the system</p>
            </div>
            <button wire:click="downloadTemplate" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Download Template
            </button>
        </div>
    </div>

    {{-- Configuration Section --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h4 class="text-md font-semibold text-gray-900 mb-4">Import Configuration</h4>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Data Type Selection --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Data Type</label>
                <select wire:model="dataType" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200">
                    <option value="combined">Combined (Members + Shares + Savings)</option>
                    <option value="members">Members Only</option>
                    <option value="accounts">Accounts</option>
                    <option value="loans">Loans</option>
                    <option value="savings">Savings Only</option>
                    <option value="shares">Shares Only</option>
                    <option value="transactions">Transactions</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">
                    @if($dataType === 'combined')
                        Import members with their shares and savings data in one file
                    @else
                        Select the type of data you want to import
                    @endif
                </p>
            </div>

            {{-- File Type Selection --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">File Format</label>
                <select wire:model="fileType" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200">
                    <option value="csv">CSV</option>
                    <option value="xlsx">Excel (XLSX)</option>
                    <option value="xls">Excel (XLS)</option>
                    <option value="json">JSON</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">Supported file formats for import</p>
            </div>

            {{-- Import Mode Selection --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Import Mode</label>
                <select wire:model="importMode" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200">
                    <option value="append">Append (Add New Records)</option>
                    <option value="update">Update (Modify Existing)</option>
                    <option value="replace">Replace (Delete & Import)</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">How to handle existing data</p>
            </div>
        </div>
    </div>

    {{-- Combined Import Information --}}
    @if($dataType === 'combined')
        <div class="bg-red-50 border border-red-200 rounded-xl p-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-red-800">Combined Import Format</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p>Your file should contain the following columns in order:</p>
                        <ul class="mt-2 list-disc list-inside space-y-1">
                            <li><strong>s/n</strong> - Serial number</li>
                            <li><strong>JINA KAMILI</strong> - Full name of the member</li>
                            <li><strong>ID</strong> - Member ID number</li>
                            <li><strong>NO OF SHARES</strong> - Number of shares owned</li>
                            <li><strong>VALUE</strong> - Total value of shares</li>
                            <li><strong>AKIBA</strong> - Savings balance</li>
                            <li><strong>GENDER</strong> - Male/Female</li>
                            <li><strong>DOB</strong> - Date of birth (MM/DD/YY format)</li>
                            <li><strong>PHONE NO</strong> - Phone number</li>
                            <li><strong>NIN</strong> - National ID number</li>
                        </ul>
                        <p class="mt-3">This will create:</p>
                        <ul class="mt-1 list-disc list-inside space-y-1">
                            <li>Member profile with all personal information (Active immediately)</li>
                            <li>Savings account with initial balance</li>
                            <li>Share register with ownership details</li>
                        </ul>
                        <p class="mt-3 font-medium">Note: Migrated members are set to ACTIVE status immediately without requiring approval.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- File Upload Section --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h4 class="text-md font-semibold text-gray-900 mb-4">File Upload</h4>
        
        {{-- Drag & Drop Upload Area --}}
        <div class="relative">
            <div 
                x-data="{ 
                    isDragging: false,
                    handleDrop(e) {
                        isDragging = false;
                        if (e.dataTransfer.files.length > 0) {
                            @this.upload('migrationFile', e.dataTransfer.files[0]);
                        }
                    }
                }"
                x-on:drop.prevent="handleDrop($event)"
                x-on:dragover.prevent="isDragging = true"
                x-on:dragleave.prevent="isDragging = false"
                x-on:drop="isDragging = false"
                class="border-2 border-dashed rounded-xl transition-all duration-200"
                :class="isDragging ? 'border-red-400 bg-red-50' : 'border-gray-300 hover:border-gray-400'"
            >
                <div class="p-8">
                    <div class="text-center">
                        {{-- Upload Icon --}}
                        <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>

                        {{-- Upload Status --}}
                        @if($migrationFile)
                            <div class="mb-4">
                                <div class="inline-flex items-center px-4 py-2 bg-green-50 border border-green-200 rounded-lg">
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-sm font-medium text-green-800">
                                        {{ $migrationFile->getClientOriginalName() }}
                                    </span>
                                    <button wire:click="$set('migrationFile', null)" class="ml-2 text-green-600 hover:text-green-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- Upload Instructions --}}
                        <div class="space-y-2">
                            <p class="text-gray-600">
                                <label for="file-upload" class="relative cursor-pointer text-red-600 hover:text-red-500 font-medium">
                                    <span>Click to upload</span>
                                    <input 
                                        id="file-upload" 
                                        wire:model="migrationFile" 
                                        type="file" 
                                        class="sr-only"
                                        accept=".csv,.xlsx,.xls,.json"
                                    >
                                </label>
                                <span class="text-gray-500"> or drag and drop</span>
                            </p>
                            <p class="text-xs text-gray-500">CSV, Excel or JSON files up to 10MB</p>
                        </div>

                        {{-- Upload Progress --}}
                        <div wire:loading wire:target="migrationFile" class="mt-4">
                            <div class="bg-gray-200 rounded-full h-2 overflow-hidden">
                                <div class="bg-red-600 h-full rounded-full animate-pulse" style="width: 50%"></div>
                            </div>
                            <p class="text-sm text-gray-600 mt-2">Uploading file...</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Validation Errors --}}
            @error('migrationFile')
                <div class="mt-2 text-sm text-red-600 flex items-start">
                    <svg class="w-4 h-4 mr-1 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>

    {{-- Data Preview Section --}}
    @if($showPreview && count($previewData) > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h4 class="text-md font-semibold text-gray-900 mb-4">Data Preview</h4>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            @if(isset($previewData[0]))
                                @foreach($previewData[0] as $header)
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ $header }}
                                    </th>
                                @endforeach
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach(array_slice($previewData, 1) as $row)
                            <tr class="hover:bg-gray-50">
                                @foreach($row as $cell)
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                        {{ $cell }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <p class="mt-3 text-xs text-gray-500">Showing first 5 rows of your data</p>
        </div>
    @endif

    {{-- Import Actions --}}
    @if($migrationFile && !$isProcessing)
        <div class="flex justify-between items-center">
            <button 
                wire:click="resetImport"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Reset
            </button>

            <button 
                wire:click="startImport"
                class="inline-flex items-center px-6 py-3 bg-red-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                Start Import
            </button>
        </div>
    @endif

    {{-- Processing Status --}}
    @if($isProcessing)
        <div class="bg-red-50 border border-red-200 rounded-xl p-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-red-600 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-red-800">Processing Import</h3>
                    <p class="mt-1 text-sm text-red-700">{{ $processingMessage }}</p>
                    
                    <div class="mt-3">
                        <div class="bg-red-100 rounded-full h-2 overflow-hidden">
                            <div class="bg-red-600 h-full rounded-full transition-all duration-500" style="width: {{ $processingProgress }}%"></div>
                        </div>
                        <p class="mt-1 text-xs text-red-600">{{ $processingProgress }}% Complete</p>
                    </div>

                    <button 
                        wire:click="cancelImport"
                        class="mt-3 inline-flex items-center px-3 py-1 border border-red-300 rounded text-xs font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                    >
                        Cancel Import
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Import Results --}}
    @if($showResults && $importResults)
        <div class="bg-green-50 border border-green-200 rounded-xl p-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-green-800">Import Completed</h3>
                    
                    <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-white rounded-lg p-3">
                            <p class="text-xs text-gray-500">Total Records</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $importResults['total'] ?? 0 }}</p>
                        </div>
                        <div class="bg-white rounded-lg p-3">
                            <p class="text-xs text-gray-500">Successful</p>
                            <p class="text-lg font-semibold text-green-600">{{ $importResults['success'] ?? 0 }}</p>
                        </div>
                        <div class="bg-white rounded-lg p-3">
                            <p class="text-xs text-gray-500">Errors</p>
                            <p class="text-lg font-semibold text-red-600">{{ $importResults['errors'] ?? 0 }}</p>
                        </div>
                        <div class="bg-white rounded-lg p-3">
                            <p class="text-xs text-gray-500">Skipped</p>
                            <p class="text-lg font-semibold text-yellow-600">{{ $importResults['skipped'] ?? 0 }}</p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center space-x-3">
                        <button 
                            wire:click="resetImport"
                            class="inline-flex items-center px-4 py-2 border border-green-300 rounded-lg text-sm font-medium text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                        >
                            Start New Import
                        </button>
                        
                        <button 
                            wire:click="downloadLogFile"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Download Log
                        </button>
                    </div>
                    
                    <div class="mt-3 text-xs text-gray-500">
                        <p class="font-medium mb-1">Import Notes:</p>
                        <p>• All members created with ACTIVE status (no approval needed for migration)</p>
                        <p>• 3 mandatory accounts created per member (Shares, Savings, Deposits)</p>
                        <p>• All transactions posted to General Ledger</p>
                        <p>• Detailed log file available for download</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Errors Display --}}
    @if(count($errors) > 0)
        <div class="bg-red-50 border border-red-200 rounded-xl p-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-red-800">Import Errors</h3>
                    <ul class="mt-2 text-sm text-red-700 space-y-1">
                        @foreach($errors as $error)
                            <li class="flex items-start">
                                <span class="block w-1.5 h-1.5 bg-red-400 rounded-full mt-1.5 mr-2 flex-shrink-0"></span>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Warnings Display --}}
    @if(count($warnings) > 0)
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-yellow-800">Import Warnings</h3>
                    <ul class="mt-2 text-sm text-yellow-700 space-y-1">
                        @foreach($warnings as $warning)
                            <li class="flex items-start">
                                <span class="block w-1.5 h-1.5 bg-yellow-400 rounded-full mt-1.5 mr-2 flex-shrink-0"></span>
                                {{ $warning }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
</div>