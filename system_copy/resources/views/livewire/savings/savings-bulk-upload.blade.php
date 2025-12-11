<div class="container mx-auto p-6 space-y-8">

    {{-- Stepper Navigation --}}
    @php
        $steps = ['Upload', 'Preview', 'Process', 'Results'];
    @endphp
    <div class="mb-2">
        <div class="flex items-center justify-between">
            @foreach($steps as $index => $label)
                <div class="flex-1 flex items-center group">
                    <div class="w-10 h-10 flex items-center justify-center rounded-full text-sm font-semibold 
                        {{ $currentStep >= ($index + 1) ? 'bg-blue-900 text-white' : 'bg-gray-300 text-gray-600' }}">
                        {{ $index + 1 }}
                    </div>
                    @if($index < count($steps) - 1)
                        <div class="flex-1 h-1 mx-2 {{ $currentStep > ($index + 1) ? 'bg-blue-900' : 'bg-gray-300' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="flex justify-between mt-2 text-sm text-gray-500 font-medium">
            @foreach($steps as $index => $label)
                <div class="flex-1 text-center {{ $currentStep === ($index + 1) ? 'text-blue-900 font-semibold' : '' }}">
                    {{ $label }}
                </div>
            @endforeach
        </div>
    </div>

    {{-- Step 1: Upload --}}
    @if($currentStep === 1)
        <div class="bg-white p-6 rounded-xl shadow ring-1 ring-gray-100">
            <h2 class="text-lg font-bold text-gray-800 mb-6">Bulk Savings Deposit Upload</h2>

            <div class="bg-gray-50 rounded-lg p-4 mb-6 ring-1 ring-gray-100">
                <h3 class="text-lg font-medium text-gray-700 mb-4">Bank Account Configuration</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="selectedBankAccountId" class="block text-sm font-medium text-gray-700 mb-2">Bank Account *</label>
                        <select wire:model="selectedBankAccountId" id="selectedBankAccountId"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-900 focus:border-blue-900">
                            <option value="">Select Bank Account</option>
                            @foreach($bankAccounts as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->bank_name }} - {{ $bank->account_name }} ({{ $bank->account_number }})</option>
                            @endforeach
                        </select>
                        @error('selectedBankAccountId') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="selectedInternalMirrorAccountId" class="block text-sm font-medium text-gray-700 mb-2">Internal Mirror Account *</label>
                        <select wire:model="selectedInternalMirrorAccountId" id="selectedInternalMirrorAccountId"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-900 focus:border-blue-900">
                            <option value="">Select Internal Mirror Account</option>
                            @foreach($internalMirrorAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->account_name }} ({{ $account->account_number }})</option>
                            @endforeach
                        </select>
                        @error('selectedInternalMirrorAccountId') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <a href="#" wire:click.prevent="downloadTemplate" class="text-blue-900 hover:underline text-sm">Download Template</a>
            </div>

            <div>
                <input type="file" wire:model="uploadFile"
                    class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-900 focus:border-blue-900 mb-2" accept=".csv,.xlsx,.xls">
                @error('uploadFile') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                <div wire:loading wire:target="uploadFile" class="text-blue-900 text-sm">Uploading...</div>

                @if($uploadFile)
                    <div class="mt-4">
                        <button wire:click="processFilePreview"
                            class="bg-blue-900 text-white px-4 py-2 rounded-md hover:bg-blue-900 shadow focus:ring-2 focus:ring-blue-900"
                            @if($isUploading) disabled @endif>
                            Preview File
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Step 2: Preview --}}
    @if($currentStep === 2)
        <div class="bg-white p-6 rounded-xl shadow ring-1 ring-gray-100">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Preview & Validate</h2>
            <div class="mb-4 text-gray-600 text-sm">
                Showing first {{ count($previewData) }} rows. Please review and fix any errors before proceeding.
                <div class="mt-2">
                    <strong>Auto Values:</strong> Payment Method: Bank, Date: Today, Narration: Bulk savings deposit.
                </div>
            </div>

            <div class="overflow-x-auto rounded border border-gray-200">
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 border">#</th>
                            <th class="p-2 border">Member ID</th>
                            <th class="p-2 border">Account Number</th>
                            <th class="p-2 border">Amount</th>
                            <th class="p-2 border">Errors</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($previewData as $i => $row)
                            <tr class="{{ isset($validationErrors[$i + 2]) ? 'bg-red-50' : '' }}">
                                <td class="p-2 border">{{ $i + 2 }}</td>
                                <td class="p-2 border">{{ $row[0] ?? '' }}</td>
                                <td class="p-2 border">{{ $row[1] ?? '' }}</td>
                                <td class="p-2 border">{{ $row[2] ?? '' }}</td>
                                <td class="p-2 border">
                                    @if(isset($validationErrors[$i + 2]))
                                        <ul class="text-red-600 list-disc pl-5 text-sm">
                                            @foreach($validationErrors[$i + 2] as $err)
                                                <li>{{ $err }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-green-600 font-medium">OK</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between mt-6">
                <button wire:click="goBack" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Back</button>
                <button wire:click="processBulkUpload"
                    class="bg-blue-900 text-white px-4 py-2 rounded hover:bg-blue-900"
                    @if($errorCount > 0) disabled @endif>
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
        <div class="bg-white p-6 rounded-xl shadow flex flex-col items-center ring-1 ring-gray-100">
            <h2 class="text-lg font-semibold mb-4">Processing Bulk Upload</h2>
            <div class="w-full bg-gray-200 rounded-full h-4 mb-4 overflow-hidden">
                <div class="bg-blue-900 h-full transition-all duration-300 ease-in-out" style="width: {{ $uploadProgress }}%"></div>
            </div>
            <div class="text-gray-700 mb-2">{{ $processingStatus }}</div>
            <div class="text-sm text-gray-500">Please wait, do not close this window.</div>
        </div>
    @endif

    {{-- Step 4: Results --}}
    @if($currentStep === 4)
        <div class="bg-white p-6 rounded-xl shadow ring-1 ring-gray-100">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Bulk Upload Results</h2>
            <div class="mb-4">
                <span class="text-green-600 font-bold">{{ $successCount }}</span> successful,
                <span class="text-red-600 font-bold">{{ $errorCount }}</span> failed,
                <span class="text-gray-600 font-bold">{{ $skippedCount }}</span> skipped.
            </div>

            <div class="overflow-x-auto max-h-64 border border-gray-200 rounded">
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 border">Row</th>
                            <th class="p-2 border">Status</th>
                            <th class="p-2 border">Message</th>
                            <th class="p-2 border">Reference</th>
                            <th class="p-2 border">Member ID</th>
                            <th class="p-2 border">Account</th>
                            <th class="p-2 border">Amount</th>
                            <th class="p-2 border">Payment Method</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($processingResults as $result)
                            <tr>
                                <td class="p-2 border">{{ $result['row'] ?? '' }}</td>
                                <td class="p-2 border">
                                    @if($result['status'] === 'success')
                                        <span class="text-green-600 font-bold">Success</span>
                                    @else
                                        <span class="text-red-600 font-bold">Error</span>
                                    @endif
                                </td>
                                <td class="p-2 border">{{ $result['message'] ?? '' }}</td>
                                <td class="p-2 border">{{ $result['reference'] ?? '' }}</td>
                                <td class="p-2 border">{{ $result['member'] ?? '' }}</td>
                                <td class="p-2 border">{{ $result['account'] ?? '' }}</td>
                                <td class="p-2 border">{{ $result['amount'] ?? '' }}</td>
                                <td class="p-2 border">{{ $result['bank'] ?? $result['payment_method'] ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                <button wire:click="resetUploadState" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                    New Upload
                </button>
            </div>
        </div>
    @endif

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="fixed bottom-4 right-4 bg-green-600 text-white px-4 py-2 rounded-md shadow-lg flex items-center space-x-2">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed bottom-4 right-4 bg-red-600 text-white px-4 py-2 rounded-md shadow-lg flex items-center space-x-2">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif
</div>
