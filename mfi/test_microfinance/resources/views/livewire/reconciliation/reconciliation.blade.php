<div class="min-h-screen bg-gradient-to-br from-slate-50 to-red-50">
    <div class="p-6">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-red-600 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Bank Reconciliation Manager</h1>
                        <p class="text-gray-600 mt-1">Upload, process, and reconcile bank statements with internal transactions</p>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="flex items-center space-x-4">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Sessions</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $sessions->count() }}</p>
                            </div>
                        </div>
                    </div>
                    
                    @php
                        $completedCount = $sessions->filter(function($session) {
                            return $this->getSessionStatus($session) === 'COMPLETED';
                        })->count();
                        $pendingCount = $sessions->filter(function($session) {
                            return $this->getSessionStatus($session) === 'PENDING';
                        })->count();
                    @endphp

                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Completed</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $completedCount }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Pending</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $pendingCount }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-6">
            <!-- Enhanced Sidebar -->
            <div class="w-80 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <!-- Modern Upload Section -->
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Upload Bank Statement</h3>
                    
                    <!-- Bank Selection -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Bank</label>
                        <select wire:model="bankStatement" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200">
                            <option value="">Choose Bank</option>
                            <option value="im">I&M Bank</option>
                            <option value="crdb">CRDB Bank</option>
                            <option value="nmb">NMB Bank</option>
                            <option value="abbsa">ABBSA Bank</option>
                        </select>
                        @error('bankStatement') 
                            <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> 
                        @enderror
                    </div>

                    <!-- Modern File Upload -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload PDF Statement</label>
                        
                        <!-- Drag & Drop Zone -->
                        <div 
                            x-data="{ 
                                isDropping: false,
                                isUploading: false
                            }"
                            x-on:dragover.prevent="isDropping = true"
                            x-on:dragleave.prevent="isDropping = false"
                            x-on:drop.prevent="
                                isDropping = false;
                                $wire.upload('pdfFile', $event.dataTransfer.files[0])
                            "
                            class="relative"
                        >
                            <div
                                class="border-2 border-dashed rounded-lg p-6 text-center transition-all duration-200 cursor-pointer hover:border-red-400 hover:bg-red-50"
                                :class="isDropping ? 'border-red-500 bg-red-100' : 'border-gray-300'"
                                @click="$refs.fileInput.click()"
                            >
                                <div wire:loading.remove wire:target="pdfFile">
                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="text-sm text-gray-600">
                                        <span class="font-medium text-red-600 hover:text-red-500">Click to upload</span>
                                        or drag and drop
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">PDF files only, max 10MB</p>
                                </div>
                                
                                <!-- Upload Progress -->
                                <div wire:loading wire:target="pdfFile" class="space-y-2">
                                    <div class="flex items-center justify-center">
                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="text-sm text-gray-600">Uploading...</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hidden File Input -->
                            <input 
                                type="file" 
                                wire:model="pdfFile" 
                                x-ref="fileInput"
                                accept=".pdf" 
                                class="hidden"
                            />
                        </div>
                        
                        @error('pdfFile') 
                            <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> 
                        @enderror
                    </div>

                    <!-- Upload Button -->
                    @if($permissions['canUpload'] ?? false)
                    <button
                        type="button"
                        wire:click="uploadFile()"
                        wire:loading.attr="disabled"
                        wire:target="uploadFile"
                        class="w-full px-4 py-3 bg-red-600 text-white font-medium rounded-lg hover:bg-red-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <!-- Default State -->
                        <div wire:loading.remove wire:target="uploadFile">
                            <div class="flex items-center justify-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <span>Upload & Process</span>
                            </div>
                        </div>
                        
                        <!-- Loading State -->
                        <div wire:loading wire:target="uploadFile">
                            <div class="flex items-center justify-center space-x-2">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Processing...</span>
                            </div>
                        </div>
                    </button>
                    @else
                    <div class="w-full px-4 py-3 bg-gray-200 text-gray-500 font-medium rounded-lg text-center">
                        No permission to upload
                    </div>
                    @endif

                    <!-- Processing Status -->
                    <div wire:loading wire:target="uploadFile">
                        <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex items-center space-x-2">
                                <svg class="animate-spin h-4 w-4 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm text-red-700">Processing bank statement...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Messages -->
                    @if (session()->has('success'))
                        <div class="mt-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="mt-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>{{ session('error') }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sessions List -->
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Analysis Sessions</h3>

                    @if($sessions->count() > 0)
                        <div class="space-y-3">
                            @foreach ($sessions as $session)
                                @php
                                    $sessionStatus = $this->getSessionStatus($session);
                                    $transactionCount = $session->bankTransactions()->count();
                                @endphp
                                <div
                                    @if($permissions['canView'] ?? false)
                                    wire:click="setActiveSession({{ $session->id }})"
                                    @endif
                                    class="p-4 border rounded-xl @if($permissions['canView'] ?? false) cursor-pointer @else cursor-not-allowed opacity-75 @endif transition-all duration-200 hover:shadow-md
                                        {{ $activeSessionId === $session->id ? 'bg-red-50 border-red-300 shadow-md' : 'hover:bg-gray-50 border-gray-200' }}"
                                >
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-gray-900 text-sm mb-1">{{ $this->getSessionDisplayName($session) }}</h4>
                                            <p class="text-xs text-gray-600">Date: {{ $session->created_at->format('d/m/Y') }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                                {{ $sessionStatus === 'COMPLETED' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ $sessionStatus }}
                                            </span>
                                        </div>
                                    </div>
                                    @if($transactionCount > 0)
                                        <div class="flex items-center justify-between text-xs text-gray-500 mt-2 pt-2 border-t border-gray-200">
                                            <span class="flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                {{ $transactionCount }} transactions
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-gray-500 text-sm">No analysis sessions found</p>
                            <p class="text-gray-400 text-xs mt-1">Upload a bank statement to get started</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Enhanced Main Content Area -->
            <div class="flex-1">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <!-- Content Header -->
                    <div class="px-8 py-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">
                                    @if($activeSession && $showData)
                                        {{ $this->getSessionDisplayName($activeSession) }}
                                    @else
                                        Bank Reconciliation
                                    @endif
                                </h2>
                                <p class="text-gray-600 mt-1">
                                    @if($activeSession && $showData)
                                        Date: {{ $activeSession->created_at->format('d/m/Y') }} • {{ $activeSession->bankTransactions()->count() }} transactions
                                    @else
                                        Select a session to view transactions and run reconciliation
                                    @endif
                                </p>
                            </div>
                            
                            @if($activeSession && $showData)
                                @if($permissions['canReconcile'] ?? false)
                                <button
                                    wire:click="runReconciliation()"
                                    wire:loading.attr="disabled"
                                    class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 flex items-center space-x-2"
                                >
                                    <span wire:loading.remove wire:target="runReconciliation" class="flex items-center space-x-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>Run Reconciliation</span>
                                    </span>
                                    <span wire:loading wire:target="runReconciliation">
                                        <svg class="animate-spin h-5 w-5 inline-block mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>Reconciling...</span>
                                    </span>
                                </button>
                                @else
                                <div class="px-6 py-3 bg-gray-200 text-gray-500 font-medium rounded-lg text-center">
                                    No permission to run reconciliation
                                </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="p-8">
                        @if($activeSession && $showData)
                            <!-- Reconciliation Summary -->
                            @if($reconciliationSummary)
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                                    <div class="bg-red-50 p-6 rounded-xl border border-red-200">
                                        <div class="flex items-center justify-between mb-2">
                                            <h3 class="text-sm font-medium text-red-700">Total Transactions</h3>
                                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        <div class="text-2xl font-bold text-red-900">{{ $reconciliationSummary['total'] }}</div>
                                    </div>
                                    
                                    <div class="bg-green-50 p-6 rounded-xl border border-green-200">
                                        <div class="flex items-center justify-between mb-2">
                                            <h3 class="text-sm font-medium text-green-700">Matched</h3>
                                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div class="text-2xl font-bold text-green-900">{{ $reconciliationSummary['matched'] ?? 0 }}</div>
                                    </div>

                                    <div class="bg-yellow-50 p-6 rounded-xl border border-yellow-200">
                                        <div class="flex items-center justify-between mb-2">
                                            <h3 class="text-sm font-medium text-yellow-700">Unmatched</h3>
                                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div class="text-2xl font-bold text-yellow-900">{{ ($reconciliationSummary['unmatched_bank'] ?? 0) + ($reconciliationSummary['unmatched_cashbook'] ?? 0) }}</div>
                                    </div>
                                    
                                    <div class="bg-purple-50 p-6 rounded-xl border border-purple-200">
                                        <div class="flex items-center justify-between mb-2">
                                            <h3 class="text-sm font-medium text-purple-700">Reconciliation Rate</h3>
                                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                            </svg>
                                        </div>
                                        <div class="text-2xl font-bold text-purple-900">{{ $reconciliationSummary['reconciliation_rate'] }}%</div>
                                    </div>
                                </div>
                            @endif

                            <!-- Dynamic Tabs -->
                            <div class="mb-6">
                                <div class="border-b border-gray-200">
                                    <nav class="-mb-px flex space-x-2 overflow-x-auto" aria-label="Tabs">
                                        <!-- Matched Transaction Tab -->
                                        <button
                                            wire:click="switchTab('matched')"
                                            class="whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm transition-colors duration-200
                                                {{ $activeTab === 'matched' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                                        >
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span>Matched Transaction</span>
                                            </div>
                                        </button>

                                        <!-- Cashbook - Non Matching Tab -->
                                        <button
                                            wire:click="switchTab('cashbook')"
                                            class="whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm transition-colors duration-200
                                                {{ $activeTab === 'cashbook' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                                        >
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                </svg>
                                                <span>Cashbook - Non Matching</span>
                                            </div>
                                        </button>

                                        <!-- Dynamic Bank Account Tabs -->
                                        @foreach($bankAccounts as $bankAccount)
                                            <button
                                                wire:click="switchTab('bank_account_{{ $bankAccount->id }}')"
                                                class="whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm transition-colors duration-200
                                                    {{ $activeTab === 'bank_account_' . $bankAccount->id ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                                            >
                                                <div class="flex items-center space-x-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                                    </svg>
                                                    <span>{{ $bankAccount->account_name }} - Non Matching</span>
                                                </div>
                                            </button>
                                        @endforeach
                                    </nav>
                                </div>

                                <!-- Tab Content -->
                                <div class="mt-6">
                                    @if($activeTab === 'matched')
                                        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                            <div class="px-6 py-4 border-b border-gray-200 bg-green-50 flex items-center space-x-3">
                                                <div class="p-2 bg-green-100 rounded-lg">
                                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="text-lg font-semibold text-gray-900">Matched Transactions ({{ $tabData->count() }})</h3>
                                                    <p class="text-sm text-gray-500">Transactions successfully reconciled between bank statement and cashbook</p>
                                                </div>
                                            </div>

                                            @if($tabData->count() > 0)
                                                <div class="overflow-x-auto">
                                                    <table class="w-full text-sm">
                                                        <thead class="bg-gray-50">
                                                            <tr>
                                                                <!-- Cashbook Section -->
                                                                <th colspan="4" class="px-4 py-2 text-center text-xs font-bold text-red-700 uppercase tracking-wider bg-red-50 border-r-2 border-red-200">
                                                                    Cashbook (System)
                                                                </th>
                                                                <!-- Match Info Section -->
                                                                <th colspan="2" class="px-4 py-2 text-center text-xs font-bold text-purple-700 uppercase tracking-wider bg-purple-50 border-r-2 border-purple-200">
                                                                    Match Info
                                                                </th>
                                                                <!-- Bank Section -->
                                                                <th colspan="4" class="px-4 py-2 text-center text-xs font-bold text-green-700 uppercase tracking-wider bg-green-50">
                                                                    Bank Statement
                                                                </th>
                                                            </tr>
                                                            <tr class="bg-gray-100">
                                                                <!-- Cashbook Columns -->
                                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase border-r border-gray-200">Date</th>
                                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase border-r border-gray-200">Reference</th>
                                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase border-r border-gray-200">Description</th>
                                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-600 uppercase border-r-2 border-red-200">Amount</th>
                                                                <!-- Match Info Columns -->
                                                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase border-r border-gray-200">Confidence</th>
                                                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase border-r-2 border-purple-200">Variance</th>
                                                                <!-- Bank Columns -->
                                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-600 uppercase border-r border-gray-200">Debit/Credit</th>
                                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase border-r border-gray-200">Narration</th>
                                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase border-r border-gray-200">Reference</th>
                                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">Date</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="bg-white divide-y divide-gray-200">
                                                            @foreach($tabData as $match)
                                                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                                                    <!-- Cashbook Data -->
                                                                    <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-900 border-r border-gray-200">
                                                                        {{ \Carbon\Carbon::parse($match->cashbook_date)->format('d/m/Y') }}
                                                                    </td>
                                                                    <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-900 border-r border-gray-200">
                                                                        <span class="font-mono text-xs">{{ $match->cashbook_reference ?? '-' }}</span>
                                                                    </td>
                                                                    <td class="px-3 py-3 text-xs text-gray-900 border-r border-gray-200">
                                                                        <div class="max-w-xs truncate" title="{{ $match->cashbook_description }}">
                                                                            {{ $match->cashbook_description ?? '-' }}
                                                                        </div>
                                                                    </td>
                                                                    <td class="px-3 py-3 whitespace-nowrap text-xs text-right border-r-2 border-red-200">
                                                                        <span class="font-semibold text-red-700">{{ number_format($match->cashbook_amount, 2) }}</span>
                                                                    </td>

                                                                    <!-- Match Info -->
                                                                    <td class="px-3 py-3 whitespace-nowrap text-center border-r border-gray-200">
                                                                        <div class="flex items-center justify-center">
                                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold
                                                                                {{ $match->match_confidence >= 100 ? 'bg-green-100 text-green-800' :
                                                                                   ($match->match_confidence >= 85 ? 'bg-yellow-100 text-yellow-800' : 'bg-orange-100 text-orange-800') }}">
                                                                                {{ number_format($match->match_confidence, 0) }}%
                                                                            </span>
                                                                        </div>
                                                                    </td>
                                                                    <td class="px-3 py-3 whitespace-nowrap text-center border-r-2 border-purple-200">
                                                                        @if($match->variance > 0)
                                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                                                {{ number_format($match->variance, 2) }}
                                                                            </span>
                                                                        @else
                                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                                0.00
                                                                            </span>
                                                                        @endif
                                                                    </td>

                                                                    <!-- Bank Data -->
                                                                    <td class="px-3 py-3 whitespace-nowrap text-xs text-right border-r border-gray-200">
                                                                        @if($match->bank_debit > 0)
                                                                            <span class="font-semibold text-red-600">{{ number_format($match->bank_debit, 2) }} DR</span>
                                                                        @elseif($match->bank_credit > 0)
                                                                            <span class="font-semibold text-green-600">{{ number_format($match->bank_credit, 2) }} CR</span>
                                                                        @else
                                                                            <span class="text-gray-400">-</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="px-3 py-3 text-xs text-gray-900 border-r border-gray-200">
                                                                        <div class="max-w-xs truncate" title="{{ $match->bank_narration }}">
                                                                            {{ $match->bank_narration ?? '-' }}
                                                                        </div>
                                                                    </td>
                                                                    <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-900 border-r border-gray-200">
                                                                        <span class="font-mono text-xs">{{ $match->bank_reference ?? '-' }}</span>
                                                                    </td>
                                                                    <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-900">
                                                                        {{ \Carbon\Carbon::parse($match->bank_date)->format('d/m/Y') }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <!-- Pagination Links -->
                                                <div class="px-6 py-4 border-t border-gray-200">
                                                    {{ $tabData->links() }}
                                                </div>
                                            @else
                                                <div class="text-center py-12 text-gray-500 px-6">
                                                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <p>No matched transactions to display</p>
                                                    <p class="text-sm mt-2">Run reconciliation to find matching transactions</p>
                                                </div>
                                            @endif
                                        </div>

                                    @elseif($activeTab === 'cashbook')
                                        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                            <div class="px-6 py-4 border-b border-gray-200 bg-yellow-50 flex items-center space-x-3">
                                                <div class="p-2 bg-yellow-100 rounded-lg">
                                                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="text-lg font-semibold text-gray-900">Cashbook - Non Matching ({{ $tabData->count() }})</h3>
                                                    <p class="text-sm text-gray-500">Cashbook entries not found in bank statement</p>
                                                </div>
                                            </div>

                                            @if($tabData->count() > 0)
                                                <div class="overflow-x-auto">
                                                    <table class="w-full">
                                                        <thead class="bg-gray-50">
                                                            <tr>
                                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="bg-white divide-y divide-gray-200">
                                                            @foreach($tabData as $transaction)
                                                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                        {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y') }}
                                                                    </td>
                                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                        <span class="font-mono">{{ $transaction->reference_number ?? '-' }}</span>
                                                                    </td>
                                                                    <td class="px-6 py-4 text-sm text-gray-900">
                                                                        <div class="max-w-xs truncate" title="{{ $transaction->narration ?? $transaction->description ?? '' }}">
                                                                            {{ $transaction->narration ?? $transaction->description ?? '-' }}
                                                                        </div>
                                                                    </td>
                                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                                                        <span class="font-medium">{{ number_format($transaction->amount ?? 0, 2) }}</span>
                                                                    </td>
                                                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                                                        @if(($transaction->debit_amount ?? 0) > 0)
                                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                                                Debit
                                                                            </span>
                                                                        @elseif(($transaction->credit_amount ?? 0) > 0)
                                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                                Credit
                                                                            </span>
                                                                        @else
                                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                                                Unknown
                                                                            </span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                                            Unmatched
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <!-- Pagination Links -->
                                                <div class="px-6 py-4 border-t border-gray-200">
                                                    {{ $tabData->links() }}
                                                </div>
                                            @else
                                                <div class="text-center py-12 text-gray-500 px-6">
                                                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <p>No non-matching cashbook entries</p>
                                                    <p class="text-sm mt-2">All cashbook entries have been matched with bank transactions</p>
                                                </div>
                                            @endif
                                        </div>

                                    @else
                                        @php
                                            $bankAccountId = str_replace('bank_account_', '', $activeTab);
                                            $currentBankAccount = $bankAccounts->firstWhere('id', $bankAccountId);
                                        @endphp
                                        @if($currentBankAccount)
                                            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                                <div class="px-6 py-4 border-b border-gray-200 bg-orange-50 flex items-center space-x-3">
                                                    <div class="p-2 bg-orange-100 rounded-lg">
                                                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h3 class="text-lg font-semibold text-gray-900">{{ $currentBankAccount->account_name }} - Non Matching ({{ $tabData->count() }})</h3>
                                                        <p class="text-sm text-gray-500">Bank: {{ $currentBankAccount->bank_name }} • Account: {{ $currentBankAccount->account_number }}</p>
                                                    </div>
                                                </div>

                                                @if($tabData->count() > 0)
                                                    <div class="overflow-x-auto">
                                                        <table class="w-full">
                                                            <thead class="bg-gray-50">
                                                                <tr>
                                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Narration</th>
                                                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Debit</th>
                                                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Credit</th>
                                                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="bg-white divide-y divide-gray-200">
                                                                @foreach($tabData as $transaction)
                                                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                            {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y') }}
                                                                        </td>
                                                                        <td class="px-6 py-4 text-sm text-gray-900">
                                                                            <div class="max-w-xs truncate" title="{{ $transaction->narration }}">
                                                                                {{ $transaction->narration }}
                                                                            </div>
                                                                        </td>
                                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                                                            @if($transaction->withdrawal_amount > 0)
                                                                                <span class="font-medium text-red-600">{{ number_format($transaction->withdrawal_amount, 2) }}</span>
                                                                            @else
                                                                                <span class="text-gray-400">-</span>
                                                                            @endif
                                                                        </td>
                                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                                                            @if($transaction->deposit_amount > 0)
                                                                                <span class="font-medium text-green-600">{{ number_format($transaction->deposit_amount, 2) }}</span>
                                                                            @else
                                                                                <span class="text-gray-400">-</span>
                                                                            @endif
                                                                        </td>
                                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                                                            @if($transaction->balance)
                                                                                <span class="font-medium">{{ number_format($transaction->balance, 2) }}</span>
                                                                            @else
                                                                                <span class="text-gray-400">-</span>
                                                                            @endif
                                                                        </td>
                                                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                                                Unreconciled
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <!-- Pagination Links -->
                                                    <div class="px-6 py-4 border-t border-gray-200">
                                                        {{ $tabData->links() }}
                                                    </div>
                                                @else
                                                    <div class="text-center py-12 text-gray-500 px-6">
                                                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <p>No non-matching transactions for this bank account</p>
                                                        <p class="text-sm mt-2">All transactions for this account have been reconciled</p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <!-- Reconciliation Results -->
                            @if($reconciliationResults)
                                <div class="mb-6 p-6 bg-gray-50 rounded-xl border border-gray-200">
                                    <h3 class="font-medium text-gray-800 mb-4 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        Reconciliation Results
                                    </h3>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                        <div class="bg-white p-3 rounded-lg border border-gray-200">
                                            <span class="text-gray-600">Total Processed:</span>
                                            <span class="font-semibold text-gray-900 ml-1">{{ $reconciliationResults['total_processed'] }}</span>
                                        </div>
                                        <div class="bg-white p-3 rounded-lg border border-gray-200">
                                            <span class="text-gray-600">Matched:</span>
                                            <span class="font-semibold text-green-600 ml-1">{{ $reconciliationResults['matched'] }}</span>
                                        </div>
                                        <div class="bg-white p-3 rounded-lg border border-gray-200">
                                            <span class="text-gray-600">Partial Matches:</span>
                                            <span class="font-semibold text-yellow-600 ml-1">{{ $reconciliationResults['partial_matches'] }}</span>
                                        </div>
                                        <div class="bg-white p-3 rounded-lg border border-gray-200">
                                            <span class="text-gray-600">Unmatched:</span>
                                            <span class="font-semibold text-red-600 ml-1">{{ $reconciliationResults['unmatched'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @else
                            <!-- Empty State -->
                            <div class="text-center py-12">
                                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No session selected</h3>
                                <p class="text-gray-600 mb-4">Select an analysis session from the sidebar to view transactions and run reconciliation.</p>
                                <div class="flex items-center justify-center space-x-4 text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Upload a bank statement
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Select a session
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        Run reconciliation
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
