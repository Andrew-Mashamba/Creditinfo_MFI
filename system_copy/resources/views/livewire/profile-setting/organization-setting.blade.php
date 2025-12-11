<div class="py-2 px-4 bg-gradient-to-br from-slate-50 to-blue-50">
    {{-- Quick Stats Row --}}
    <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="bg-white rounded-lg shadow-sm p-3 flex items-center space-x-3 border border-gray-100">
            <div class="p-1.5 bg-blue-100 rounded">
                <svg class="w-4 h-4 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div class="text-xs text-gray-500">Total Share Types</div>
                <div class="text-sm font-bold text-gray-900">{{ is_array($shares) ? count($shares) : 0 }}</div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-3 flex items-center space-x-3 border border-gray-100">
            <div class="p-1.5 bg-yellow-100 rounded">
                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div class="text-xs text-gray-500">Pending Documents</div>
                <div class="text-sm font-bold text-gray-900">
                    {{
                        collect(['budget_approval_letter','summary_of_general_meeting','chairperson_report','audit_financial_report','revenue_and_expense'])
                        ->filter(fn($f) => !DB::table('institution_files')->where('file_id', array_search($f, array_keys($fileFields)))->exists())->count()
                    }}
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-3 flex items-center space-x-3 border border-gray-100">
            <div class="p-1.5 bg-green-100 rounded">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </div>
            <div>
                <div class="text-xs text-gray-500">Current Financial Year</div>
                <div class="text-sm font-bold text-gray-900">
                    {{ $institution && $institution->startDate ? \Carbon\Carbon::parse($institution->startDate)->format('Y') : 'N/A' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    <div class="mb-3">
        @if (session()->has('message'))
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-2 rounded flex items-center text-sm" role="alert">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('message') }}</span>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-2 rounded flex items-center text-sm" role="alert">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- General Info & Shares --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- General Info Card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                <h2 class="text-sm font-bold text-gray-900 mb-3 flex items-center border-b border-blue-100 pb-2">
                    <svg class="w-4 h-4 mr-2 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    General Institution Info
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    {{-- Left --}}
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Currency</label>
                            <input disabled value="TZS" class="w-full px-2 py-1.5 bg-gray-50 border border-gray-300 rounded text-gray-500 text-sm focus:outline-none focus:ring-1 focus:ring-blue-900 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Member Registration Fees</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-2 text-gray-500 text-xs">TZS</span>
                                <input type="number" wire:model.defer="institution.registration_fees" min="0" step="0.01" class="w-full pl-10 pr-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-900 focus:border-transparent">
                            </div>
                            @error('institution.registration_fees')
                                <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Minimum Shares</label>
                            <input type="number" wire:model.defer="institution.min_shares" min="0" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-900 focus:border-transparent">
                            @error('institution.min_shares')
                                <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Initial Shares</label>
                            <input type="number" wire:model.defer="institution.initial_shares" min="0" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-900 focus:border-transparent">
                            @error('institution.initial_shares')
                                <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Value per Share</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-2 text-gray-500 text-xs">TZS</span>
                                <input type="number" wire:model.defer="institution.value_per_share" min="0" step="0.01" class="w-full pl-10 pr-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-900 focus:border-transparent">
                            </div>
                            @error('institution.value_per_share')
                                <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                      
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Petty Amount Limit</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-2 text-gray-500 text-xs">TZS</span>
                                <input type="number" wire:model.defer="institution.petty_amount_limit" min="0" step="0.01" class="w-full pl-10 pr-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-900 focus:border-transparent">
                            </div>
                            @error('institution.petty_amount_limit')
                                <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    {{-- Right --}}
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Institution Name</label>
                            <input type="text" wire:model.defer="institution.institution_name" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-900 focus:border-transparent">
                            @error('institution.institution_name')
                                <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" wire:model.defer="institution.manager_email" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-900 focus:border-transparent">
                            @error('institution.manager_email')
                                <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Phone Number</label>
                            <input type="tel" wire:model.defer="institution.phone_number" pattern="[0-9]{10}" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-900 focus:border-transparent">
                            @error('institution.phone_number')
                                <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Region</label>
                            <input type="text" wire:model.defer="institution.region" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-900 focus:border-transparent">
                            @error('institution.region')
                                <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Wilaya</label>
                            <input type="text" wire:model.defer="institution.wilaya" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-900 focus:border-transparent">
                            @error('institution.wilaya')
                                <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Set Member Inactive After</label>
                            <div class="grid grid-cols-2 gap-1">
                                @foreach([6, 12, 18, 24] as $months)
                                    <label class="inline-flex items-center">
                                        <input type="radio" wire:model.defer="institution.inactivity" value="{{ $months }}" class="form-radio h-3 w-3 text-blue-900 focus:ring-blue-900">
                                        <span class="ml-1 text-xs text-gray-700">{{ $months }} months</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('institution.inactivity')
                                <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
          
        </div>
        {{-- Financial Year & Documents --}}
        <div class="space-y-4">
            {{-- Financial Year Card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                <h2 class="text-sm font-bold text-gray-900 mb-3 flex items-center border-b border-blue-100 pb-2">
                    <svg class="w-4 h-4 mr-2 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Financial Year
                </h2>
                <label for="startDate" class="block text-xs font-medium text-gray-700 mb-1">Financial Year Date</label>
                <input type="date" wire:model.defer="institution.startDate" id="startDate" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-900 focus:border-transparent">
                @error('institution.startDate')
                    <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            {{-- Documents Management Card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                <div class="flex items-center justify-between mb-3 border-b border-blue-100 pb-2">
                    <h2 class="text-sm font-bold text-gray-900 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Required Documents
                    </h2>
                    @php
                        $docs = [
                            'budget_approval_letter' => ['label' => 'Budget Approval Letter', 'required' => true],
                            'summary_of_general_meeting' => ['label' => 'Summary of General Meeting', 'required' => true],
                            'chairperson_report' => ['label' => "Chairperson's Report", 'required' => true],
                            'audit_financial_report' => ['label' => "External Auditor's Financial Report", 'required' => true],
                            'revenue_and_expense' => ['label' => 'Revenue and Expense Analysis', 'required' => false]
                        ];
                        $uploadedCount = 0;
                        foreach($docs as $field => $doc) {
                            $fileId = array_search($field, array_keys($fileFields));
                            if(DB::table('institution_files')->where('file_id', $fileId)->exists()) {
                                $uploadedCount++;
                            }
                        }
                    @endphp
                    <div class="flex items-center space-x-2">
                        <span class="text-xs text-gray-500">Progress:</span>
                        <div class="flex items-center">
                            <div class="w-20 bg-gray-200 rounded-full h-1.5">
                                <div class="bg-blue-900 h-1.5 rounded-full" style="width: {{ ($uploadedCount / count($docs)) * 100 }}%"></div>
                            </div>
                            <span class="ml-2 text-xs font-medium text-gray-700">{{ $uploadedCount }}/{{ count($docs) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Documents Table --}}
                <div class="space-y-2">
                    @foreach($docs as $field => $doc)
                        @php
                            $fileId = array_search($field, array_keys($fileFields));
                            $files = DB::table('institution_files')
                                ->where('file_id', $fileId)
                                ->orderBy('created_at', 'desc')
                                ->get();
                            $hasFile = $files->isNotEmpty();
                            $latestFile = $files->first();
                        @endphp
                        <div class="border {{ $hasFile ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }} rounded-lg p-3 transition-all hover:shadow-sm">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        @if($hasFile)
                                            <svg class="w-4 h-4 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                        @endif
                                        <div>
                                            <h3 class="text-xs font-semibold text-gray-900">
                                                {{ $doc['label'] }}
                                                @if($doc['required'])
                                                    <span class="ml-1 text-red-500">*</span>
                                                @endif
                                            </h3>
                                            @if($hasFile && $latestFile)
                                                <p class="text-xs text-gray-600 mt-0.5">
                                                    Latest version: {{ \Carbon\Carbon::parse($latestFile->created_at)->format('M d, Y H:i') }}
                                                    @if($files->count() > 1)
                                                        <span class="ml-1 text-blue-900 font-medium">({{ $files->count() }} versions)</span>
                                                    @endif
                                                </p>
                                            @else
                                                <p class="text-xs text-red-600 mt-0.5 font-medium">
                                                    ⚠️ Document missing - Please upload
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Actions --}}
                                <div class="flex items-center space-x-1">
                                    @if($hasFile)
                                        {{-- Download Button --}}
                                        <button wire:click="download({{ $latestFile->id }})" 
                                                class="inline-flex items-center px-2 py-1 bg-white border border-gray-300 rounded text-xs font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-900"
                                                title="Download document">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                                            </svg>
                                            Download
                                        </button>
                                        
                                        {{-- Add New Version Button --}}
                                        <label class="inline-flex items-center px-2 py-1 bg-blue-900 border border-blue-900 rounded text-xs font-medium text-white hover:bg-blue-800 cursor-pointer focus:outline-none focus:ring-1 focus:ring-blue-900">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Add Version
                                            <input type="file" wire:model="{{ $field }}" class="hidden" accept=".pdf" 
                                                   wire:change="store">
                                        </label>

                                        {{-- View Versions Button --}}
                                        @if($files->count() > 1)
                                            <button type="button" 
                                                    onclick="document.getElementById('versions-{{ $field }}').classList.toggle('hidden')"
                                                    class="inline-flex items-center px-2 py-1 bg-gray-100 border border-gray-300 rounded text-xs font-medium text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-1 focus:ring-blue-900"
                                                    title="View all versions">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                                </svg>
                                                Versions
                                            </button>
                                        @endif
                                    @else
                                        {{-- Upload Button for Missing Files --}}
                                        <label class="inline-flex items-center px-3 py-1 bg-blue-900 border border-blue-900 rounded text-xs font-medium text-white hover:bg-blue-800 cursor-pointer focus:outline-none focus:ring-1 focus:ring-blue-900">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                            </svg>
                                            Upload Document
                                            <input type="file" wire:model="{{ $field }}" class="hidden" accept=".pdf" 
                                                   wire:change="store">
                                        </label>
                                    @endif
                                </div>
                            </div>

                            {{-- Upload Progress --}}
                            <div wire:loading wire:target="{{ $field }}" class="mt-2">
                                <div class="flex items-center space-x-2">
                                    <svg class="animate-spin h-3 w-3 text-blue-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-xs text-gray-600">Uploading document...</span>
                                </div>
                            </div>

                            {{-- Error Message --}}
                            @error($field)
                                <p class="mt-1 text-xs text-red-600 flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror

                            {{-- Version History Panel --}}
                            @if($hasFile)
                                <div id="versions-{{ $field }}" class="hidden mt-3 p-2 bg-white rounded-lg border border-gray-200">
                                    <h4 class="text-xs font-semibold text-gray-700 mb-2 flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                        </svg>
                                        Document Version History ({{ $files->count() }} {{ $files->count() == 1 ? 'version' : 'versions' }})
                                    </h4>
                                    <div class="space-y-1">
                                        @foreach($files as $index => $file)
                                            <div class="flex items-center justify-between p-2 {{ $index == 0 ? 'bg-blue-50 border border-blue-200' : 'bg-gray-50' }} rounded">
                                                <div class="flex items-center space-x-2">
                                                    @if($index == 0)
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold bg-blue-900 text-white">Latest</span>
                                                    @else
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-300 text-gray-700">v{{ $files->count() - $index }}</span>
                                                    @endif
                                                    <span class="text-xs text-gray-700">
                                                        {{ \Carbon\Carbon::parse($file->created_at)->format('M d, Y') }}
                                                        <span class="text-gray-500">at {{ \Carbon\Carbon::parse($file->created_at)->format('H:i') }}</span>
                                                    </span>
                                                </div>
                                                <div class="flex items-center space-x-1">
                                                    <button wire:click="download({{ $file->id }})" 
                                                            class="inline-flex items-center px-2 py-1 bg-white border border-gray-300 rounded text-xs text-gray-700 hover:bg-gray-50"
                                                            title="Download this version">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                        </svg>
                                                        Download
                                                    </button>
                                                    @if($index != 0)
                                                        <button wire:click="deleteFile({{ $file->id }})" 
                                                                wire:confirm="Are you sure you want to delete this version?"
                                                                class="p-1 text-red-600 hover:bg-red-50 rounded"
                                                                title="Delete this version">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Help Text --}}
                <div class="mt-3 p-2 bg-blue-50 rounded-lg">
                    <p class="text-xs text-blue-900">
                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <strong>Note:</strong> All documents must be in PDF format (max 5MB). Documents marked with <span class="text-red-500">*</span> are required. 
                        The system keeps all versions of uploaded documents for audit purposes.
                    </p>
                </div>
            </div>
        </div>
    </div>
    {{-- Save Button --}}
    <div class="mt-4 flex justify-end">
        <button type="button" wire:click="institutionSetting" wire:loading.attr="disabled" wire:target="institutionSetting" aria-label="Save institution settings" aria-busy="false" aria-live="polite" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded shadow-sm text-white bg-blue-900 hover:bg-blue-800 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-blue-900 disabled:opacity-70 disabled:cursor-not-allowed transition-colors duration-200 ease-in-out">
            <span wire:loading.remove wire:target="institutionSetting" class="inline-flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Save Changes
            </span>
            <span wire:loading wire:target="institutionSetting" >
                <div class="inline-flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span class="text-sm">Saving...</span>
                </div>
            </span>
        </button>
    </div>
</div>

