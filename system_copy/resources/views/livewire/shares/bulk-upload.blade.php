<div class="container mx-auto p-6">
    {{-- Stepper Navigation --}}
    <div class="mb-6 flex items-center space-x-4">
        <div class="flex items-center">
            <div class="rounded-full h-8 w-8 flex items-center justify-center {{ $currentStep >= 1 ? 'bg-indigo-600 text-white' : 'bg-gray-300 text-gray-600' }}">1</div>
            <span class="ml-2 {{ $currentStep >= 1 ? 'font-bold text-blue-900' : 'text-gray-500' }}">Upload</span>
        </div>
        <div class="h-1 w-8 bg-gray-300"></div>
        <div class="flex items-center">
            <div class="rounded-full h-8 w-8 flex items-center justify-center {{ $currentStep >= 2 ? 'bg-indigo-600 text-white' : 'bg-gray-300 text-gray-600' }}">2</div>
            <span class="ml-2 {{ $currentStep >= 2 ? 'font-bold text-blue-900' : 'text-gray-500' }}">Preview</span>
        </div>
        <div class="h-1 w-8 bg-gray-300"></div>
        <div class="flex items-center">
            <div class="rounded-full h-8 w-8 flex items-center justify-center {{ $currentStep >= 3 ? 'bg-indigo-600 text-white' : 'bg-gray-300 text-gray-600' }}">3</div>
            <span class="ml-2 {{ $currentStep >= 3 ? 'font-bold text-blue-900' : 'text-gray-500' }}">Process</span>
        </div>
        <div class="h-1 w-8 bg-gray-300"></div>
        <div class="flex items-center">
            <div class="rounded-full h-8 w-8 flex items-center justify-center {{ $currentStep == 4 ? 'bg-indigo-600 text-white' : 'bg-gray-300 text-gray-600' }}">4</div>
            <span class="ml-2 {{ $currentStep == 4 ? 'font-bold text-blue-900' : 'text-gray-500' }}">Results</span>
        </div>
    </div>

    {{-- Step 1: Upload --}}
    @if($currentStep === 1)
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-xl font-semibold mb-4">Bulk Share Issuance Upload</h2>
            <div class="mb-4">
                <a href="#" wire:click.prevent="downloadTemplate" class="text-indigo-600 hover:underline">Download Template</a>
            </div>
            <div>
                <input type="file" wire:model="uploadFile" class="mb-2" accept=".csv,.xlsx,.xls">
                @error('uploadFile') <div class="text-red-600 text-sm mb-2">{{ $message }}</div> @enderror
                <div wire:loading wire:target="uploadFile" class="text-indigo-600">Uploading...</div>
                
                @if($uploadFile)
                    <div class="mt-2">
                        <button wire:click="processFilePreview" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700" @if($isUploading) disabled @endif>
                            Preview File
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Step 2: Preview --}}
    @if($currentStep === 2)
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-xl font-semibold mb-4">Preview & Validate</h2>
            <div class="mb-4 text-gray-600">Showing first {{ count($previewData) }} rows. Please review and fix any errors before proceeding.</div>
            <div class="overflow-x-auto">
                <table class="min-w-full border text-sm">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2 border">#</th>
                            <th class="p-2 border">Member ID</th>
                            <th class="p-2 border">Linked Savings Account</th>
                            <th class="p-2 border">Linked Share Account</th>
                            <th class="p-2 border">Shares</th>
                            <th class="p-2 border">Errors</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($previewData as $i => $row)
                            <tr>
                                <td class="border p-2">{{ $i + 2 }}</td>
                                @foreach($row as $cell)
                                    <td class="border p-2">{{ $cell }}</td>
                                @endforeach
                                <td class="border p-2">
                                    @if(isset($validationErrors[$i + 2]))
                                        <ul class="text-red-600 list-disc pl-4">
                                            @foreach($validationErrors[$i + 2] as $err)
                                                <li>{{ $err }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-green-600">OK</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="flex justify-between mt-4">
                <button wire:click="goBack" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Back</button>
                <button wire:click="processBulkUpload" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700" @if($errorCount > 0) disabled @endif>
                    Process Bulk Upload
                </button>
            </div>
            @if($errorCount > 0)
                <div class="mt-2 text-red-600">Please fix errors before proceeding.</div>
            @endif
        </div>
    @endif

    {{-- Step 3: Processing --}}
    @if($currentStep === 3)
        <div class="bg-white p-6 rounded shadow flex flex-col items-center">
            <h2 class="text-xl font-semibold mb-4">Processing Bulk Upload</h2>
            <div class="w-full bg-gray-200 rounded-full h-4 mb-4">
                <div class="bg-indigo-600 h-4 rounded-full transition-all duration-300" style="width: {{ $uploadProgress }}%"></div>
            </div>
            <div class="text-gray-700 mb-2">{{ $processingStatus }}</div>
            <div class="text-sm text-gray-500">Please wait, do not close this window.</div>
        </div>
    @endif

    {{-- Step 4: Results --}}
    @if($currentStep === 4)
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-xl font-semibold mb-4">Bulk Upload Results</h2>
            <div class="mb-4">
                <span class="text-green-600 font-bold">{{ $successCount }}</span> successful,
                <span class="text-red-600 font-bold">{{ $errorCount }}</span> failed,
                <span class="text-gray-600 font-bold">{{ $skippedCount }}</span> skipped.
            </div>
            <div class="overflow-x-auto max-h-64">
                <table class="min-w-full border text-sm">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2 border">Row</th>
                            <th class="p-2 border">Status</th>
                            <th class="p-2 border">Message</th>
                            <th class="p-2 border">Reference</th>
                            <th class="p-2 border">Member</th>
                            <th class="p-2 border">Shares</th>
                            <th class="p-2 border">Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($processingResults as $result)
                            <tr>
                                <td class="border p-2">{{ $result['row'] ?? '' }}</td>
                                <td class="border p-2">
                                    @if($result['status'] === 'success')
                                        <span class="text-green-600 font-bold">Success</span>
                                    @else
                                        <span class="text-red-600 font-bold">Error</span>
                                    @endif
                                </td>
                                <td class="border p-2">{{ $result['message'] ?? '' }}</td>
                                <td class="border p-2">{{ $result['reference'] ?? '' }}</td>
                                <td class="border p-2">{{ $result['member'] ?? '' }}</td>
                                <td class="border p-2">{{ $result['shares'] ?? '' }}</td>
                                <td class="border p-2">{{ $result['value'] ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="flex justify-between mt-4">
                <button wire:click="resetUploadState" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">New Upload</button>
            </div>
        </div>
    @endif

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="fixed bottom-4 right-4 bg-red-500 text-white px-4 py-2 rounded shadow">
            {{ session('error') }}
        </div>
    @endif
</div>
