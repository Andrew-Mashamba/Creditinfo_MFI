<div class="bg-gray-50 p-6">
    {{-- Header Section --}}
    <div class="rounded-t-xl p-6 ">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">Property, Plant & Equipment (PPE) Management</h1>
                <p class="text-gray-500 mt-1">Track, manage, and depreciate fixed assets</p>
            </div>
            <div class="flex space-x-4">
                {{-- Summary Statistics --}}
                <div class="bg-white/20 rounded-lg px-4 py-2">
                    <p class="text-xs text-gray-500">Total PPE Value</p>
                    <p class="text-lg font-bold">{{ number_format($this->totalPpeValue, 2) }} {{ config('app.currency', 'TZS') }}</p>
                </div>
                <div class="bg-white/20 rounded-lg px-4 py-2">
                    <p class="text-xs text-gray-500">Accumulated Depreciation</p>
                    <p class="text-lg font-bold">{{ number_format($this->totalAccumulatedDepreciation, 2) }} {{ config('app.currency', 'TZS') }}</p>
                </div>
                <div class="bg-white/20 rounded-lg px-4 py-2">
                    <p class="text-xs text-gray-500">Net Book Value</p>
                    <p class="text-lg font-bold">{{ number_format($this->netBookValue, 2) }} {{ config('app.currency', 'TZS') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Navigation Tabs --}}
    <div class="bg-white border-x border-gray-200">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button wire:click="selectMenu(1)" 
                    class="px-6 py-3 text-sm font-medium {{ $selectedMenuItem == 1 ? 'text-red-600 border-b-2 border-red-600 bg-red-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }} transition-colors">
                    Dashboard
                </button>
                <button wire:click="selectMenu(2)" 
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed"
                    class="px-6 py-3 text-sm font-medium {{ $selectedMenuItem == 2 ? 'text-red-600 border-b-2 border-red-600 bg-red-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }} transition-colors">
                    <span wire:loading.remove wire:target="selectMenu">Add PPE</span>
                    <span wire:loading wire:target="selectMenu">Loading...</span>
                </button>
                <button wire:click="selectMenu(3)" 
                    class="px-6 py-3 text-sm font-medium {{ $selectedMenuItem == 3 ? 'text-red-600 border-b-2 border-red-600 bg-red-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }} transition-colors">
                    PPE Register
                </button>
               
                <button wire:click="selectMenu(5)" 
                    class="px-6 py-3 text-sm font-medium {{ $selectedMenuItem == 5 ? 'text-red-600 border-b-2 border-red-600 bg-red-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }} transition-colors">
                    Disposal
                </button>
                <button wire:click="selectMenu(6)" 
                    class="px-6 py-3 text-sm font-medium {{ $selectedMenuItem == 6 ? 'text-red-600 border-b-2 border-red-600 bg-red-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }} transition-colors">
                    Reports
                </button>
                <button wire:click="selectMenu(7)" 
                    class="px-6 py-3 text-sm font-medium {{ $selectedMenuItem == 7 ? 'text-red-600 border-b-2 border-red-600 bg-red-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }} transition-colors">
                    Maintenance
                </button>
                <button wire:click="selectMenu(8)" 
                    class="px-6 py-3 text-sm font-medium {{ $selectedMenuItem == 8 ? 'text-red-600 border-b-2 border-red-600 bg-red-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }} transition-colors">
                    Transfers
                </button>
                <button wire:click="selectMenu(9)" 
                    class="px-6 py-3 text-sm font-medium {{ $selectedMenuItem == 9 ? 'text-red-600 border-b-2 border-red-600 bg-red-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }} transition-colors">
                    Insurance
                </button>
                <button wire:click="selectMenu(10)" 
                    class="px-6 py-3 text-sm font-medium {{ $selectedMenuItem == 10 ? 'text-red-600 border-b-2 border-red-600 bg-red-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }} transition-colors">
                    Revaluation
                </button>
            </nav>
        </div>
    </div>

    {{-- Content Area --}}
    <div class="bg-white border-x border-b border-gray-200 rounded-b-xl">
        @if (session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg m-6" role="alert">
                <span class="block sm:inline">{{ session('message') }}</span>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg m-6" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="p-6">
            @switch($selectedMenuItem)
                @case(1)
                    {{-- Dashboard --}}
                    <div class="space-y-6">
                        <h2 class="text-xl font-semibold text-gray-900">PPE Dashboard</h2>
                        
                        {{-- Summary Cards --}}
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-6 border border-red-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-red-600">Total Assets</p>
                                        <p class="text-2xl font-bold text-gray-900">{{ $this->totalAssetCount }}</p>
                                    </div>
                                    <div class="p-3 bg-red-200 rounded-lg">
                                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-green-600">Active Assets</p>
                                        <p class="text-2xl font-bold text-gray-900">{{ $this->activeAssetsCount }}</p>
                                    </div>
                                    <div class="p-3 bg-green-200 rounded-lg">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-6 border border-yellow-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-yellow-600">Monthly Depreciation</p>
                                        <p class="text-2xl font-bold text-gray-900">{{ number_format($this->monthlyDepreciation, 2) }}</p>
                                    </div>
                                    <div class="p-3 bg-yellow-200 rounded-lg">
                                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-6 border border-red-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-red-600">Pending Disposal</p>
                                        <p class="text-2xl font-bold text-gray-900">{{ $this->pendingDisposalCount }}</p>
                                    </div>
                                    <div class="p-3 bg-red-200 rounded-lg">
                                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Recent PPE Additions --}}
                        <div class="bg-white rounded-lg border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Recent PPE Additions</h3>
                            </div>
                            <div class="p-6">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left font-medium text-gray-700">Asset Name</th>
                                                <th class="px-4 py-2 text-left font-medium text-gray-700">Category</th>
                                                <th class="px-4 py-2 text-right font-medium text-gray-700">Purchase Price</th>
                                                <th class="px-4 py-2 text-left font-medium text-gray-700">Purchase Date</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @forelse($this->recentPpes as $ppe)
                                                <tr>
                                                    <td class="px-4 py-2 text-gray-900">{{ $ppe->name }}</td>
                                                    <td class="px-4 py-2 text-gray-700">{{ $ppe->category }}</td>
                                                    <td class="px-4 py-2 text-right text-gray-900">{{ number_format($ppe->purchase_price, 2) }}</td>
                                                    <td class="px-4 py-2 text-gray-700">{{ \Carbon\Carbon::parse($ppe->purchase_date)->format('d/m/Y') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="px-4 py-8 text-center text-gray-500">No recent PPE additions</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @break

                @case(2)
                    {{-- Add/Edit PPE Form --}}
                    <div class="max-w-4xl mx-auto">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-semibold text-gray-900">
                                @if($importMode)
                                    Import Pre-existing PPE Asset
                                @elseif($isEditMode)
                                    Edit PPE Asset
                                @else
                                    Add New PPE Asset
                                @endif
                            </h2>
                            @if(!$isEditMode && !$importMode)
                                <button type="button" wire:click="importPreExistingAsset" 
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                                    Import Pre-existing Asset
                                </button>
                            @endif
                        </div>

                        @if($importMode)
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                                <p class="text-sm text-red-800">
                                    <strong>Import Mode:</strong> You're adding a pre-existing asset. The system will calculate accumulated depreciation based on the purchase date and current condition.
                                </p>
                            </div>
                        @endif

                        {{-- Validation Errors Display --}}
                        @if ($errors->any())
                            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
                                        <div class="mt-2 text-sm text-red-700">
                                            <ul class="list-disc pl-5 space-y-1">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <form wire:submit.prevent="{{ $importMode ? 'processImport' : ($isEditMode ? 'update' : 'store') }}" class="space-y-6">
                            {{-- Basic Information --}}
                            <div class="bg-white rounded-lg border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Asset Name *</label>
                                        <input wire:model="name" type="text" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"
                                            placeholder="Enter asset name" required>
                                        @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">PPE Category * 
                                            <span class="text-xs text-gray-500">(Select the type of asset)</span>
                                        </label>
                                        <select wire:model="categoryx" 
                                            class="w-full px-3 py-2 border {{ $errors->has('categoryx') ? 'border-red-500' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" required>
                                            <option value="">Select Category</option>
                                            @php
                                                // Get the institution's PPE account configuration
                                                $institution = \Illuminate\Support\Facades\DB::table('institutions')
                                                    ->where('id', 1)
                                                    ->first();
                                                
                                                // Method 1: Get all accounts in category 1600 (Property and Equipment)
                                                $ppeCategories = \Illuminate\Support\Facades\DB::table('accounts')
                                                    ->where('category_code', '1600')
                                                    ->where('status', 'ACTIVE')
                                                    ->whereNull('deleted_at')
                                                    ->orderBy('account_level')
                                                    ->orderBy('account_name')
                                                    ->get();
                                                
                                                // If no category 1600 accounts, fallback to name-based search
                                                if ($ppeCategories->isEmpty()) {
                                                    $ppeCategories = \Illuminate\Support\Facades\DB::table('accounts')
                                                        ->where('major_category_code', '1000') // Assets
                                                        ->where(function($query) {
                                                            $query->where('account_name', 'LIKE', '%PROPERTY%')
                                                                  ->orWhere('account_name', 'LIKE', '%EQUIPMENT%')
                                                                  ->orWhere('account_name', 'LIKE', '%BUILDING%')
                                                                  ->orWhere('account_name', 'LIKE', '%VEHICLE%')
                                                                  ->orWhere('account_name', 'LIKE', '%FURNITURE%')
                                                                  ->orWhere('account_name', 'LIKE', '%MACHINERY%')
                                                                  ->orWhere('account_name', 'LIKE', '%COMPUTER%')
                                                                  ->orWhere('account_name', 'LIKE', '%OFFICE%');
                                                        })
                                                        ->where('status', 'ACTIVE')
                                                        ->whereNull('deleted_at')
                                                        ->orderBy('account_level')
                                                        ->orderBy('account_name')
                                                        ->get();
                                                }
                                            @endphp
                                            @if($ppeCategories->isEmpty())
                                                <option value="" disabled>No PPE categories found. Please create PPE accounts first.</option>
                                            @else
                                                @foreach($ppeCategories as $category)
                                                    <option value="{{ $category->account_number }}">
                                                        {{ str_repeat('—', max(0, $category->account_level - 2)) }} 
                                                        {{ $category->account_name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('categoryx') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                        {{-- Debug: Show current value --}}
                                        <p class="text-xs text-gray-400 mt-1">Current value: {{ $categoryx ?? 'none' }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Purchase Price *</label>
                                        <input wire:model="purchase_price" type="number" step="0.01" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"
                                            placeholder="0.00" required>
                                        @error('purchase_price') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Purchase Date *</label>
                                        <input wire:model="purchase_date" type="date" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" required>
                                        @error('purchase_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Salvage Value *</label>
                                        <input wire:model="salvage_value" type="number" step="0.01" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"
                                            placeholder="0.00" required>
                                        @error('salvage_value') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Useful Life (Years) *</label>
                                        <input wire:model="useful_life" type="number" min="1" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"
                                            placeholder="5" required>
                                        @error('useful_life') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
                                        <input wire:model="quantity" type="number" min="1" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"
                                            placeholder="1" required>
                                        @error('quantity') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Location *</label>
                                        <input wire:model="location" type="text" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"
                                            placeholder="Enter location" required>
                                        @error('location') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                                        <select wire:model="status" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" required>
                                            <option value="active">Active</option>
                                            <option value="under_repair">Under Repair</option>
                                            <option value="pending_disposal">Pending Disposal</option>
                                            <option value="disposed">Disposed</option>
                                        </select>
                                        @error('status') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Asset Details & Tracking --}}
                            <div class="bg-white rounded-lg border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Asset Details & Tracking</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Asset Code</label>
                                        <input wire:model="asset_code" type="text" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"
                                            placeholder="Auto-generated if empty">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Serial Number</label>
                                        <input wire:model="serial_number" type="text" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"
                                            placeholder="Enter serial number">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Barcode</label>
                                        <input wire:model="barcode" type="text" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"
                                            placeholder="Enter barcode">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Manufacturer</label>
                                        <input wire:model="manufacturer" type="text" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"
                                            placeholder="Enter manufacturer">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Model</label>
                                        <input wire:model="model" type="text" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"
                                            placeholder="Enter model">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Condition *</label>
                                        <select wire:model="condition" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" required>
                                            <option value="excellent">Excellent</option>
                                            <option value="good">Good</option>
                                            <option value="fair">Fair</option>
                                            <option value="poor">Poor</option>
                                            <option value="needs_repair">Needs Repair</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Depreciation Settings --}}
                            <div class="bg-white rounded-lg border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Depreciation Settings</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Depreciation Method *</label>
                                        <select wire:model="depreciation_method" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" required>
                                            <option value="straight_line">Straight Line</option>
                                            <option value="declining_balance">Declining Balance</option>
                                            <option value="sum_of_years">Sum of Years' Digits</option>
                                            <option value="units_of_production">Units of Production</option>
                                        </select>
                                    </div>
                                    
                                    @if($importMode)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Accumulated Depreciation</label>
                                        <input wire:model="accumulated_depreciation" type="number" step="0.01" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"
                                            placeholder="Enter if known, or leave for auto-calculation">
                                    </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Warranty Information --}}
                            <div class="bg-white rounded-lg border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Warranty Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Warranty Start Date</label>
                                        <input wire:model="warranty_start_date" type="date" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Warranty End Date</label>
                                        <input wire:model="warranty_end_date" type="date" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Warranty Provider</label>
                                        <input wire:model="warranty_provider" type="text" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"
                                            placeholder="Enter warranty provider">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Warranty Terms</label>
                                        <input wire:model="warranty_terms" type="text" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"
                                            placeholder="Enter warranty terms">
                                    </div>
                                </div>
                            </div>

                            {{-- Assignment & Deployment --}}
                            <div class="bg-white rounded-lg border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Assignment & Deployment</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                                        <select wire:model="department_id" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm">
                                            <option value="">Select Department</option>
                                            @foreach($departments ?? [] as $dept)
                                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Custodian</label>
                                        <select wire:model="custodian_id" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm">
                                            <option value="">Select Custodian</option>
                                            @foreach($users ?? [] as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Assigned To</label>
                                        <select wire:model="assigned_to" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm">
                                            <option value="">Select User</option>
                                            @foreach($users ?? [] as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Insurance (Optional at Creation) --}}
                            <div class="bg-white rounded-lg border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Insurance Information (Optional)</h3>
                                <p class="text-sm text-gray-600 mb-4">You can add insurance now or later from the Insurance tab</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Policy Number</label>
                                        <input wire:model="policy_number" type="text" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"
                                            placeholder="Enter policy number">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Insurance Company</label>
                                        <input wire:model="insurance_company" type="text" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"
                                            placeholder="Enter insurance company">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Premium Amount</label>
                                        <input wire:model="premium_amount" type="number" step="0.01" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"
                                            placeholder="0.00">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Coverage End Date</label>
                                        <input wire:model="insurance_end_date" type="date" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm">
                                    </div>
                                </div>
                            </div>

                            {{-- Additional Costs Section --}}
                            <div class="bg-white rounded-lg border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Cost Capitalization</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Legal Fees</label>
                                        <input wire:model="legal_fees" type="number" step="0.01" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"
                                            placeholder="0.00">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Registration Fees</label>
                                        <input wire:model="registration_fees" type="number" step="0.01" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"
                                            placeholder="0.00">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Renovation Costs</label>
                                        <input wire:model="renovation_costs" type="number" step="0.01" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"
                                            placeholder="0.00">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Transportation Costs</label>
                                        <input wire:model="transportation_costs" type="number" step="0.01" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"
                                            placeholder="0.00">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Installation Costs</label>
                                        <input wire:model="installation_costs" type="number" step="0.01" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"
                                            placeholder="0.00">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Other Costs</label>
                                        <input wire:model="other_costs" type="number" step="0.01" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"
                                            placeholder="0.00">
                                    </div>
                                </div>

                                {{-- Total Cost Summary --}}
                                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                                    <div class="flex justify-between items-center">
                                        <span class="text-lg font-semibold text-gray-900">Total Capitalized Cost:</span>
                                        <span class="text-xl font-bold text-red-600">
                                            {{ number_format(
                                                (float)($purchase_price ?? 0) + 
                                                (float)($legal_fees ?? 0) + 
                                                (float)($registration_fees ?? 0) + 
                                                (float)($renovation_costs ?? 0) + 
                                                (float)($transportation_costs ?? 0) + 
                                                (float)($installation_costs ?? 0) + 
                                                (float)($other_costs ?? 0), 2) }} {{ config('app.currency', 'TZS') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Payment Information --}}
                            <div class="bg-white rounded-lg border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method *</label>
                                        <select wire:model="payment_method" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" required>
                                            <option value="cash">Cash Payment</option>
                                            <option value="credit">Credit Purchase</option>
                                            <option value="loan">Loan Financing</option>
                                            <option value="lease">Lease Agreement</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Account</label>
                                        <select wire:model="cash_account" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm">
                                            <option value="">Select Payment Account</option>
                                            @php
                                                // Get cash and bank accounts
                                                $paymentAccounts = \Illuminate\Support\Facades\DB::table('accounts')
                                                    ->where('major_category_code', '1000') // Assets
                                                    ->where(function($query) {
                                                        $query->where('account_name', 'LIKE', '%CASH%')
                                                              ->orWhere('account_name', 'LIKE', '%BANK%');
                                                    })
                                                    ->where('status', 'ACTIVE')
                                                    ->whereNull('deleted_at')
                                                    ->orderBy('account_name')
                                                    ->get();
                                            @endphp
                                            @foreach($paymentAccounts as $account)
                                                <option value="{{ $account->account_number }}">{{ $account->account_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Supplier Name</label>
                                        <input wire:model="supplier_name" type="text" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"
                                            placeholder="Enter supplier name">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Invoice Number</label>
                                        <input wire:model="invoice_number" type="text" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"
                                            placeholder="Enter invoice number">
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                    <textarea wire:model="notes" rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm"
                                        placeholder="Enter any additional notes"></textarea>
                                </div>
                            </div>

                            {{-- Account Selection - Corrected Flow --}}
                            <div class="bg-white rounded-lg border border-gray-200 p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Account Selection</h3>
                                <p class="text-sm text-gray-600 mb-4">Select where to create the PPE account and the other account for double-entry posting</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">PPE Account (Create Asset Under) *</label>
                                        <select wire:model="parent_account_number" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" required>
                                            <option value="">Select PPE Account</option>
                                            @php
                                                // Get the institution's configured PPE parent account
                                                $institution = \Illuminate\Support\Facades\DB::table('institutions')
                                                    ->where('id', 1) // Or use session institution ID
                                                    ->first();
                                                
                                                $ppeAccounts = collect();
                                                
                                                if ($institution && $institution->property_and_equipment_account) {
                                                    // Get the main PPE account and all its descendant accounts (recursive)
                                                    $ppeParentAccount = $institution->property_and_equipment_account;
                                                    
                                                    // Using recursive query to get all descendants
                                                    $ppeAccounts = \Illuminate\Support\Facades\DB::table('accounts as a1')
                                                        ->where('a1.status', 'ACTIVE')
                                                        ->where(function($query) use ($ppeParentAccount) {
                                                            // Include the parent account itself
                                                            $query->where('a1.account_number', $ppeParentAccount)
                                                                  // Include direct children
                                                                  ->orWhere('a1.parent_account_number', $ppeParentAccount)
                                                                  // Include grandchildren (accounts whose parent's parent is the PPE account)
                                                                  ->orWhereIn('a1.parent_account_number', function($subQuery) use ($ppeParentAccount) {
                                                                      $subQuery->select('account_number')
                                                                               ->from('accounts')
                                                                               ->where('parent_account_number', $ppeParentAccount);
                                                                  })
                                                                  // Include great-grandchildren
                                                                  ->orWhereIn('a1.parent_account_number', function($subQuery) use ($ppeParentAccount) {
                                                                      $subQuery->select('a2.account_number')
                                                                               ->from('accounts as a2')
                                                                               ->join('accounts as a3', 'a2.parent_account_number', '=', 'a3.account_number')
                                                                               ->where('a3.parent_account_number', $ppeParentAccount);
                                                                  });
                                                        })
                                                        ->orderBy('a1.account_level')
                                                        ->orderBy('a1.account_name')
                                                        ->get();
                                                } else {
                                                    // Fallback: Get default PPE accounts from Assets category
                                                    $ppeAccounts = \Illuminate\Support\Facades\DB::table('accounts')
                                                        ->where('major_category_code', '1000') // Assets
                                                        ->where(function($query) {
                                                            $query->where('category_code', '1600') // Property and Equipment category
                                                                  ->orWhere(function($q) {
                                                                      $q->where('account_name', 'LIKE', '%PROPERTY%')
                                                                        ->where('account_name', 'LIKE', '%EQUIPMENT%');
                                                                  })
                                                                  ->orWhere('account_name', 'LIKE', '%FIXED ASSET%');
                                                        })
                                                        ->where('status', 'ACTIVE')
                                                        ->orderBy('account_level')
                                                        ->orderBy('account_name')
                                                        ->get();
                                                }
                                            @endphp
                                            @if($ppeAccounts->isEmpty())
                                                <option value="" disabled>No PPE accounts configured. Please configure in Institution Settings.</option>
                                            @else
                                                @foreach($ppeAccounts as $account)
                                                    <option value="{{ $account->account_number }}">
                                                        {{ str_repeat('—', max(0, $account->account_level - 1)) }} 
                                                        {{ $account->account_number }} - {{ $account->account_name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">New PPE asset account will be created under this parent</p>
                                        @if(!$institution || !$institution->property_and_equipment_account)
                                            <p class="text-xs text-yellow-600 mt-1">⚠️ No PPE account configured in Institution Settings. Using default accounts.</p>
                                        @endif
                                        @error('parent_account_number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Other Account (Payment Source/Payable) *</label>
                                        <select wire:model="other_account_id" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" required>
                                            <option value="">Select Payment Account</option>
                                            
                                            @php
                                                // Get various payment source accounts
                                                $bankAccounts = collect($otherAccounts); // Bank accounts from component
                                                
                                                // Get cash accounts from institution settings
                                                $cashAccounts = collect();
                                                if ($institution) {
                                                    $cashAccountNumbers = array_filter([
                                                        $institution->main_vaults_account,
                                                        $institution->main_till_account,
                                                        $institution->main_petty_cash_account
                                                    ]);
                                                    
                                                    if (!empty($cashAccountNumbers)) {
                                                        $cashAccounts = \Illuminate\Support\Facades\DB::table('accounts')
                                                            ->whereIn('account_number', $cashAccountNumbers)
                                                            ->where('status', 'ACTIVE')
                                                            ->get();
                                                    }
                                                }
                                                
                                                // Get accounts payable
                                                $payableAccounts = \Illuminate\Support\Facades\DB::table('accounts')
                                                    ->where('major_category_code', '2000') // Liabilities
                                                    ->where(function($query) {
                                                        $query->where('category_code', '2400') // Accounts Payable
                                                              ->orWhere('account_name', 'LIKE', '%PAYABLE%')
                                                              ->orWhere('account_name', 'LIKE', '%CREDITOR%');
                                                    })
                                                    ->where('status', 'ACTIVE')
                                                    ->orderBy('account_name')
                                                    ->limit(20)
                                                    ->get();
                                            @endphp
                                            
                                            @if($bankAccounts->isNotEmpty())
                                                <optgroup label="Bank Accounts">
                                                    @foreach($bankAccounts as $account)
                                                        <option value="{{ $account->internal_mirror_account_number }}">
                                                            {{ $account->bank_name }} - {{ $account->account_number }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                            
                                            @if($cashAccounts->isNotEmpty())
                                                <optgroup label="Cash Accounts">
                                                    @foreach($cashAccounts as $account)
                                                        <option value="{{ $account->account_number }}">
                                                            {{ $account->account_number }} - {{ $account->account_name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                            
                                            @if($payableAccounts->isNotEmpty())
                                                <optgroup label="Accounts Payable">
                                                    @foreach($payableAccounts as $account)
                                                        <option value="{{ $account->account_number }}">
                                                            {{ $account->account_number }} - {{ $account->account_name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">Account to be credited (Cash paid or Payable created)</p>
                                        @error('other_account_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Form Actions --}}
                            <div class="flex justify-end space-x-3">
                                <button type="button" wire:click="resetForm" 
                                    class="px-6 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                                    Cancel
                                </button>
                                <button type="submit" 
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-not-allowed"
                                    class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span wire:loading.remove wire:target="store,update">{{ $isEditMode ? 'Update PPE' : 'Add PPE' }}</span>
                                    <span wire:loading wire:target="store,update">Processing...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                    @break

                @case(3)
                    {{-- Enhanced PPE Register with Comprehensive Information --}}
                    <div class="space-y-6">
                        {{-- Header with Statistics --}}
                        <div class="bg-white rounded-lg border border-gray-200 p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-900">PPE Register</h2>
                                    <p class="text-sm text-gray-600 mt-1">Comprehensive view of all property, plant and equipment</p>
                                </div>
                                <div class="flex gap-3">
                                    <button wire:click="exportExcel" 
                                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Export
                                    </button>
                                    <button wire:click="runDepreciation" 
                                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                        Run Depreciation
                                    </button>
                                    <button wire:click="selectMenu(2)" 
                                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                        Add New PPE
                                    </button>
                                </div>
                            </div>
                            
                            {{-- Quick Statistics --}}
                            <div class="grid grid-cols-5 gap-4">
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-gray-900">{{ $this->totalAssetCount ?? 0 }}</p>
                                    <p class="text-xs text-gray-600">Total Assets</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-green-600">{{ $this->activeAssetsCount ?? 0 }}</p>
                                    <p class="text-xs text-gray-600">Active</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-yellow-600">{{ \App\Models\PPE::needsMaintenance()->count() ?? 0 }}</p>
                                    <p class="text-xs text-gray-600">Need Maintenance</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-red-600">{{ number_format($this->totalPpeValue ?? 0, 0) }}</p>
                                    <p class="text-xs text-gray-600">Total Value</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-red-600">{{ number_format($this->totalAccumulatedDepreciation ?? 0, 0) }}</p>
                                    <p class="text-xs text-gray-600">Total Depreciation</p>
                                </div>
                            </div>
                        </div>

                        {{-- Filters --}}
                        <div class="bg-white rounded-lg border border-gray-200 p-4">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <input wire:model.debounce.300ms="search" type="text" 
                                    class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 text-sm"
                                    placeholder="Search by name, code, serial...">
                                <select wire:model="statusFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 text-sm">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="under_repair">Under Repair</option>
                                    <option value="pending_disposal">Pending Disposal</option>
                                    <option value="disposed">Disposed</option>
                                </select>
                                <select wire:model="categoryFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 text-sm">
                                    <option value="">All Categories</option>
                                    @foreach(\App\Models\PPE::distinct('category')->whereNotNull('category')->pluck('category') as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </select>
                                <select wire:model="conditionFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 text-sm">
                                    <option value="">All Conditions</option>
                                    <option value="excellent">Excellent</option>
                                    <option value="good">Good</option>
                                    <option value="fair">Fair</option>
                                    <option value="poor">Poor</option>
                                    <option value="needs_repair">Needs Repair</option>
                                </select>
                            </div>
                        </div>

                        {{-- Enhanced Table --}}
                        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="w-full text-xs">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-3 text-left font-medium text-gray-700">Asset Details</th>
                                            <th class="px-3 py-3 text-left font-medium text-gray-700">Location</th>
                                            <th class="px-3 py-3 text-center font-medium text-gray-700">Status</th>
                                            <th class="px-3 py-3 text-right font-medium text-gray-700">Financial</th>
                                            <th class="px-3 py-3 text-center font-medium text-gray-700">Depreciation</th>
                                            <th class="px-3 py-3 text-center font-medium text-gray-700">Maintenance</th>
                                            <th class="px-3 py-3 text-center font-medium text-gray-700">Insurance</th>
                                            <th class="px-3 py-3 text-center font-medium text-gray-700">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @forelse($ppes as $ppe)
                                            <tr class="hover:bg-gray-50 @if($ppe->condition == 'needs_repair' || ($ppe->next_maintenance_date && $ppe->next_maintenance_date < now())) bg-red-50 @endif">
                                                {{-- Asset Details --}}
                                                <td class="px-3 py-3">
                                                    <div>
                                                        <p class="font-medium text-gray-900">{{ $ppe->name }}</p>
                                                        <p class="text-gray-600">Code: {{ $ppe->asset_code ?? 'N/A' }}</p>
                                                        <p class="text-gray-500">{{ $ppe->category }}</p>
                                                        @if($ppe->serial_number)
                                                            <p class="text-gray-500">SN: {{ $ppe->serial_number }}</p>
                                                        @endif
                                                    </div>
                                                </td>
                                                
                                                {{-- Location --}}
                                                <td class="px-3 py-3">
                                                    <div>
                                                        <p class="font-medium">{{ $ppe->location ?? 'N/A' }}</p>
                                                        @if($ppe->department_id)
                                                            <p class="text-gray-600">Dept: {{ $departments->where('id', $ppe->department_id)->first()->name ?? 'N/A' }}</p>
                                                        @endif
                                                        @if($ppe->custodian_id)
                                                            <p class="text-gray-600">{{ $users->where('id', $ppe->custodian_id)->first()->name ?? 'N/A' }}</p>
                                                        @endif
                                                    </div>
                                                </td>
                                                
                                                {{-- Status --}}
                                                <td class="px-3 py-3 text-center">
                                                    <div class="space-y-1">
                                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full 
                                                            @if($ppe->condition == 'excellent') bg-green-100 text-green-800
                                                            @elseif($ppe->condition == 'good') bg-red-100 text-red-800
                                                            @elseif($ppe->condition == 'fair') bg-yellow-100 text-yellow-800
                                                            @elseif($ppe->condition == 'poor') bg-orange-100 text-orange-800
                                                            @else bg-red-100 text-red-800 @endif">
                                                            {{ ucfirst($ppe->condition ?? 'Unknown') }}
                                                        </span>
                                                        <br>
                                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full 
                                                            @if($ppe->status == 'active') bg-green-100 text-green-800
                                                            @elseif($ppe->status == 'disposed') bg-gray-100 text-gray-800
                                                            @else bg-yellow-100 text-yellow-800 @endif">
                                                            {{ ucfirst($ppe->status) }}
                                                        </span>
                                                    </div>
                                                </td>
                                                
                                                {{-- Financial --}}
                                                <td class="px-3 py-3 text-right">
                                                    <div>
                                                        <p class="font-medium">{{ number_format($ppe->purchase_price, 0) }}</p>
                                                        <p class="text-green-600">NBV: {{ number_format($ppe->closing_value, 0) }}</p>
                                                        <p class="text-gray-500 text-xs">{{ \Carbon\Carbon::parse($ppe->purchase_date)->format('d/m/Y') }}</p>
                                                        <p class="text-gray-500 text-xs">Age: {{ \Carbon\Carbon::parse($ppe->purchase_date)->diffInYears(now()) }} yrs</p>
                                                    </div>
                                                </td>
                                                
                                                {{-- Depreciation --}}
                                                <td class="px-3 py-3 text-center">
                                                    <div>
                                                        <p class="font-medium">{{ ucfirst(str_replace('_', ' ', $ppe->depreciation_method ?? 'straight_line')) }}</p>
                                                        <p class="text-red-600">{{ number_format($ppe->accumulated_depreciation, 0) }}</p>
                                                        <p class="text-gray-500 text-xs">Monthly: {{ number_format($ppe->depreciation_for_month ?? 0, 0) }}</p>
                                                    </div>
                                                </td>
                                                
                                                {{-- Maintenance --}}
                                                <td class="px-3 py-3 text-center">
                                                    @if($ppe->next_maintenance_date)
                                                        @if($ppe->next_maintenance_date < now())
                                                            <span class="inline-flex px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                                                Overdue
                                                            </span>
                                                        @else
                                                            <p class="text-xs">{{ \Carbon\Carbon::parse($ppe->next_maintenance_date)->format('d/m/Y') }}</p>
                                                        @endif
                                                    @else
                                                        <span class="text-gray-400 text-xs">Not scheduled</span>
                                                    @endif
                                                    @if($ppe->maintenance_cost_to_date > 0)
                                                        <p class="text-gray-600 text-xs">Cost: {{ number_format($ppe->maintenance_cost_to_date, 0) }}</p>
                                                    @endif
                                                </td>
                                                
                                                {{-- Insurance --}}
                                                <td class="px-3 py-3 text-center">
                                                    <div class="space-y-1">
                                                        @if($ppe->warranty_end_date && $ppe->warranty_end_date >= now())
                                                            <span class="inline-flex px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                                                Warranty
                                                            </span>
                                                        @endif
                                                        
                                                        @php
                                                            $hasInsurance = false;
                                                            // Check if asset has active insurance (simplified check)
                                                            if(method_exists($ppe, 'isInsured')) {
                                                                $hasInsurance = $ppe->isInsured();
                                                            }
                                                        @endphp
                                                        
                                                        @if($hasInsurance)
                                                            <span class="inline-flex px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                                                Insured
                                                            </span>
                                                        @else
                                                            <span class="inline-flex px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                                                Not Insured
                                                            </span>
                                                        @endif
                                                    </div>
                                                </td>
                                                
                                                {{-- Actions --}}
                                                <td class="px-3 py-3">
                                                    <div class="flex flex-col gap-1">
                                                        <button wire:click="viewAsset({{ $ppe->id }})" 
                                                            class="text-red-600 hover:text-red-800 text-xs font-medium">View</button>
                                                        
                                                        @if($ppe->status == 'active')
                                                            <button wire:click="scheduleMaintenance({{ $ppe->id }})" 
                                                                class="text-green-600 hover:text-green-800 text-xs font-medium">Maintenance</button>
                                                            <button wire:click="initiateTransfer({{ $ppe->id }})" 
                                                                class="text-purple-600 hover:text-purple-800 text-xs font-medium">Transfer</button>
                                                        @endif
                                                        
                                                        <button wire:click="editAsset({{ $ppe->id }})" 
                                                            class="text-gray-600 hover:text-gray-800 text-xs font-medium">Edit</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                                    No PPE assets found. <button wire:click="selectMenu(2)" class="text-red-600 hover:underline">Add your first asset</button>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            {{-- Pagination --}}
                            @if($ppes && method_exists($ppes, 'hasPages') && $ppes->hasPages())
                                <div class="px-6 py-3 border-t border-gray-200">
                                    {{ $ppes->links() }}
                                </div>
                            @endif
                        </div>
                        
                        {{-- Legend --}}
                        <div class="bg-white rounded-lg border border-gray-200 p-4">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Legend:</h3>
                            <div class="flex flex-wrap gap-4 text-xs">
                                <div class="flex items-center gap-1">
                                    <span class="inline-block w-3 h-3 bg-red-50 rounded"></span>
                                    <span>Needs Attention</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <span class="inline-flex px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Active</span>
                                    <span>Operational</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <span class="inline-flex px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Warning</span>
                                    <span>Attention Required</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @break

                @case(4)
                    {{-- Depreciation Management --}}
                    <div class="space-y-6">
                        <h2 class="text-xl font-semibold text-gray-900">Depreciation Management</h2>

                        <div class="bg-white rounded-lg border border-gray-200 p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Run Depreciation</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                                    <select wire:model="depreciationMonth" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm">
                                        @for($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                                    <select wire:model="depreciationYear" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm">
                                        @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                                            <option value="{{ $year }}">{{ $year }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="flex items-end">
                                    <button wire:click="runDepreciation" 
                                        class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                        Run Depreciation
                                    </button>
                                </div>
                            </div>

                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <p class="text-sm text-yellow-800">
                                    <strong>Note:</strong> Depreciation will be calculated using the straight-line method for all active assets. 
                                    Monthly depreciation = (Cost - Salvage Value) / (Useful Life in Years × 12)
                                </p>
                            </div>
                        </div>

                        {{-- Depreciation History --}}
                        <div class="bg-white rounded-lg border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">Depreciation History</h3>
                            </div>
                            <div class="p-6">
                                <p class="text-gray-500">Depreciation history will be displayed here</p>
                            </div>
                        </div>
                    </div>
                    @break

                @case(5)
                    {{-- Disposal Management --}}
                    <div class="space-y-6">
                        <h2 class="text-xl font-semibold text-gray-900">Asset Disposal Management</h2>

                        {{-- Disposal Statistics --}}
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-white rounded-lg border border-gray-200 p-4">
                                <div class="flex items-center">
                                    <div class="p-2 bg-orange-100 rounded-lg">
                                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-600">Pending Approval</p>
                                        <p class="text-xl font-semibold text-gray-900">{{ $this->pendingApprovalCount ?? 0 }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white rounded-lg border border-gray-200 p-4">
                                <div class="flex items-center">
                                    <div class="p-2 bg-green-100 rounded-lg">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-600">Approved</p>
                                        <p class="text-xl font-semibold text-gray-900">{{ $this->approvedDisposalCount ?? 0 }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white rounded-lg border border-gray-200 p-4">
                                <div class="flex items-center">
                                    <div class="p-2 bg-red-100 rounded-lg">
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-600">Rejected</p>
                                        <p class="text-xl font-semibold text-gray-900">{{ $this->rejectedDisposalCount ?? 0 }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white rounded-lg border border-gray-200 p-4">
                                <div class="flex items-center">
                                    <div class="p-2 bg-gray-100 rounded-lg">
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-600">Completed</p>
                                        <p class="text-xl font-semibold text-gray-900">{{ $this->completedDisposalCount ?? 0 }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Assets Available for Disposal --}}
                        <div class="bg-white rounded-lg border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">Assets Available for Disposal</h3>
                            </div>
                            <div class="p-6">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left font-medium text-gray-700">Asset Name</th>
                                                <th class="px-4 py-2 text-left font-medium text-gray-700">Category</th>
                                                <th class="px-4 py-2 text-right font-medium text-gray-700">Net Book Value</th>
                                                <th class="px-4 py-2 text-center font-medium text-gray-700">Status</th>
                                                <th class="px-4 py-2 text-center font-medium text-gray-700">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @forelse($this->assetsForDisposal ?? [] as $asset)
                                                <tr>
                                                    <td class="px-4 py-2 text-gray-900">{{ $asset->name }}</td>
                                                    <td class="px-4 py-2 text-gray-700">{{ $asset->category }}</td>
                                                    <td class="px-4 py-2 text-right text-gray-900">{{ number_format($asset->closing_value, 2) }}</td>
                                                    <td class="px-4 py-2 text-center">
                                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                                            {{ ucfirst($asset->status) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-2 text-center">
                                                        <button wire:click="initiateDisposal({{ $asset->id }})" 
                                                            class="text-red-600 hover:text-red-800 text-xs font-medium">
                                                            Initiate Disposal
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                                        No assets available for disposal
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @break

                @case(6)
                    {{-- Reports --}}
                    <div class="space-y-6">
                        <h2 class="text-xl font-semibold text-gray-900">PPE Reports</h2>

                        <div class="bg-white rounded-lg border border-gray-200 p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Generate Reports</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                                    <select wire:model="reportType" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm">
                                        <option value="summary">PPE Summary</option>
                                        <option value="depreciation">Depreciation Schedule</option>
                                        <option value="disposal">Disposal Report</option>
                                        <option value="register">Asset Register</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                                    <select wire:model="reportDateRange" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm">
                                        <option value="30">Last 30 Days</option>
                                        <option value="90">Last 3 Months</option>
                                        <option value="180">Last 6 Months</option>
                                        <option value="365">This Year</option>
                                        <option value="all">All Time</option>
                                    </select>
                                </div>
                                <div class="flex items-end space-x-2">
                                    <button wire:click="generateReport" 
                                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                        Generate
                                    </button>
                                    <button wire:click="exportExcel" 
                                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                        Export Excel
                                    </button>
                                    <button wire:click="exportPdf" 
                                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                        Export PDF
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Report Preview --}}
                        <div class="bg-white rounded-lg border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">Report Preview</h3>
                            </div>
                            <div class="p-6">
                                <p class="text-gray-500 text-center py-8">Select report parameters and click Generate to view report</p>
                            </div>
                        </div>
                    </div>
                    @break

                @case(7)
                    {{-- Maintenance Management --}}
                    <div class="space-y-6">
                        <div class="flex justify-between items-center">
                            <h2 class="text-xl font-semibold text-gray-900">Maintenance Management</h2>
                            <button wire:click="openMaintenanceForm" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                Schedule Maintenance
                            </button>
                        </div>

                        {{-- Maintenance Overview Cards --}}
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-white rounded-lg border border-gray-200 p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-600">Due This Month</p>
                                        <p class="text-2xl font-bold text-gray-900">{{ $this->maintenanceDueCount ?? 0 }}</p>
                                    </div>
                                    <div class="p-3 bg-yellow-100 rounded-lg">
                                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white rounded-lg border border-gray-200 p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-600">Overdue</p>
                                        <p class="text-2xl font-bold text-red-600">{{ $this->maintenanceOverdueCount ?? 0 }}</p>
                                    </div>
                                    <div class="p-3 bg-red-100 rounded-lg">
                                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white rounded-lg border border-gray-200 p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-600">Completed MTD</p>
                                        <p class="text-2xl font-bold text-green-600">{{ $this->maintenanceCompletedCount ?? 0 }}</p>
                                    </div>
                                    <div class="p-3 bg-green-100 rounded-lg">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white rounded-lg border border-gray-200 p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-600">Total Cost MTD</p>
                                        <p class="text-xl font-bold text-gray-900">{{ number_format($this->maintenanceCostMTD ?? 0, 2) }}</p>
                                    </div>
                                    <div class="p-3 bg-red-100 rounded-lg">
                                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Maintenance Schedule Table --}}
                        <div class="bg-white rounded-lg border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">Maintenance Schedule</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left font-medium text-gray-700">Asset</th>
                                            <th class="px-4 py-2 text-left font-medium text-gray-700">Type</th>
                                            <th class="px-4 py-2 text-left font-medium text-gray-700">Due Date</th>
                                            <th class="px-4 py-2 text-left font-medium text-gray-700">Status</th>
                                            <th class="px-4 py-2 text-left font-medium text-gray-700">Assigned To</th>
                                            <th class="px-4 py-2 text-center font-medium text-gray-700">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @forelse($this->maintenanceSchedule ?? [] as $maintenance)
                                            <tr>
                                                <td class="px-4 py-2">{{ $maintenance->ppe->name }}</td>
                                                <td class="px-4 py-2">{{ ucfirst($maintenance->maintenance_type) }}</td>
                                                <td class="px-4 py-2">{{ \Carbon\Carbon::parse($maintenance->maintenance_date)->format('d/m/Y') }}</td>
                                                <td class="px-4 py-2">
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                                        @if($maintenance->status == 'completed') bg-green-100 text-green-800
                                                        @elseif($maintenance->isOverdue()) bg-red-100 text-red-800
                                                        @else bg-yellow-100 text-yellow-800 @endif">
                                                        {{ $maintenance->isOverdue() ? 'Overdue' : ucfirst($maintenance->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-2">{{ $maintenance->performed_by }}</td>
                                                <td class="px-4 py-2 text-center">
                                                    @if($maintenance->status !== 'completed')
                                                        <button wire:click="completeMaintenance({{ $maintenance->id }})" 
                                                            class="text-red-600 hover:text-red-800 text-xs font-medium">Complete</button>
                                                    @else
                                                        <button wire:click="viewMaintenance({{ $maintenance->id }})" 
                                                            class="text-gray-600 hover:text-gray-800 text-xs font-medium">View</button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">No maintenance records found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @break

                @case(8)
                    {{-- Asset Transfers --}}
                    <div class="space-y-6">
                        <div class="flex justify-between items-center">
                            <h2 class="text-xl font-semibold text-gray-900">Asset Transfer Management</h2>
                            <button wire:click="openTransferForm" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                Initiate Transfer
                            </button>
                        </div>

                        {{-- Recent Transfers --}}
                        <div class="bg-white rounded-lg border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">Transfer History</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left font-medium text-gray-700">Asset</th>
                                            <th class="px-4 py-2 text-left font-medium text-gray-700">From</th>
                                            <th class="px-4 py-2 text-left font-medium text-gray-700">To</th>
                                            <th class="px-4 py-2 text-left font-medium text-gray-700">Date</th>
                                            <th class="px-4 py-2 text-left font-medium text-gray-700">Status</th>
                                            <th class="px-4 py-2 text-center font-medium text-gray-700">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @forelse($this->transfers ?? [] as $transfer)
                                            <tr>
                                                <td class="px-4 py-2">{{ $transfer->ppe->name }}</td>
                                                <td class="px-4 py-2">{{ $transfer->from_location }}</td>
                                                <td class="px-4 py-2">{{ $transfer->to_location }}</td>
                                                <td class="px-4 py-2">{{ \Carbon\Carbon::parse($transfer->transfer_date)->format('d/m/Y') }}</td>
                                                <td class="px-4 py-2">
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                                        @if($transfer->status == 'completed') bg-green-100 text-green-800
                                                        @elseif($transfer->status == 'approved') bg-red-100 text-red-800
                                                        @elseif($transfer->status == 'rejected') bg-red-100 text-red-800
                                                        @else bg-yellow-100 text-yellow-800 @endif">
                                                        {{ ucfirst($transfer->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-2 text-center">
                                                    @if($transfer->status === 'pending')
                                                        <button wire:click="approveTransfer({{ $transfer->id }})" 
                                                            class="text-green-600 hover:text-green-800 text-xs font-medium mr-2">Approve</button>
                                                        <button wire:click="rejectTransfer({{ $transfer->id }})" 
                                                            class="text-red-600 hover:text-red-800 text-xs font-medium">Reject</button>
                                                    @else
                                                        <button wire:click="viewTransfer({{ $transfer->id }})" 
                                                            class="text-red-600 hover:text-red-800 text-xs font-medium">View</button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">No transfer records found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @break

                @case(9)
                    {{-- Insurance Management --}}
                    <div class="space-y-6">
                        <div class="flex justify-between items-center">
                            <h2 class="text-xl font-semibold text-gray-900">Insurance Management</h2>
                            <button wire:click="openInsuranceForm" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                Add Insurance Policy
                            </button>
                        </div>

                        {{-- Insurance Overview --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-white rounded-lg border border-gray-200 p-4">
                                <h4 class="text-sm font-medium text-gray-600 mb-2">Active Policies</h4>
                                <p class="text-2xl font-bold text-green-600">{{ $this->activePoliciesCount ?? 0 }}</p>
                            </div>
                            <div class="bg-white rounded-lg border border-gray-200 p-4">
                                <h4 class="text-sm font-medium text-gray-600 mb-2">Expiring Soon</h4>
                                <p class="text-2xl font-bold text-yellow-600">{{ $this->expiringPoliciesCount ?? 0 }}</p>
                            </div>
                            <div class="bg-white rounded-lg border border-gray-200 p-4">
                                <h4 class="text-sm font-medium text-gray-600 mb-2">Total Premium/Year</h4>
                                <p class="text-2xl font-bold text-red-600">{{ number_format($this->totalAnnualPremium ?? 0, 2) }}</p>
                            </div>
                        </div>

                        {{-- Insurance Policies Table --}}
                        <div class="bg-white rounded-lg border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">Insurance Policies</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left font-medium text-gray-700">Asset</th>
                                            <th class="px-4 py-2 text-left font-medium text-gray-700">Policy Number</th>
                                            <th class="px-4 py-2 text-left font-medium text-gray-700">Insurance Company</th>
                                            <th class="px-4 py-2 text-left font-medium text-gray-700">Coverage</th>
                                            <th class="px-4 py-2 text-left font-medium text-gray-700">Expiry Date</th>
                                            <th class="px-4 py-2 text-left font-medium text-gray-700">Status</th>
                                            <th class="px-4 py-2 text-center font-medium text-gray-700">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @forelse($this->insurancePolicies ?? [] as $policy)
                                            <tr>
                                                <td class="px-4 py-2">{{ $policy->ppe->name }}</td>
                                                <td class="px-4 py-2">{{ $policy->policy_number }}</td>
                                                <td class="px-4 py-2">{{ $policy->insurance_company }}</td>
                                                <td class="px-4 py-2">{{ ucfirst($policy->coverage_type) }}</td>
                                                <td class="px-4 py-2">{{ \Carbon\Carbon::parse($policy->end_date)->format('d/m/Y') }}</td>
                                                <td class="px-4 py-2">
                                                    @if($policy->isActive())
                                                        <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Active</span>
                                                    @elseif($policy->isExpiring())
                                                        <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Expiring Soon</span>
                                                    @else
                                                        <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Expired</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2 text-center">
                                                    <button wire:click="renewInsurance({{ $policy->id }})" 
                                                        class="text-red-600 hover:text-red-800 text-xs font-medium">Renew</button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">No insurance policies found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @break

                @case(10)
                    {{-- Revaluation Management --}}
                    <div class="space-y-6">
                        <div class="flex justify-between items-center">
                            <h2 class="text-xl font-semibold text-gray-900">Asset Revaluation</h2>
                            <button wire:click="openRevaluationForm" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                New Revaluation
                            </button>
                        </div>

                        {{-- Revaluation History --}}
                        <div class="bg-white rounded-lg border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">Revaluation History</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left font-medium text-gray-700">Asset</th>
                                            <th class="px-4 py-2 text-left font-medium text-gray-700">Date</th>
                                            <th class="px-4 py-2 text-right font-medium text-gray-700">Old Value</th>
                                            <th class="px-4 py-2 text-right font-medium text-gray-700">New Value</th>
                                            <th class="px-4 py-2 text-right font-medium text-gray-700">Change</th>
                                            <th class="px-4 py-2 text-left font-medium text-gray-700">Type</th>
                                            <th class="px-4 py-2 text-left font-medium text-gray-700">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @forelse($this->revaluations ?? [] as $revaluation)
                                            <tr>
                                                <td class="px-4 py-2">{{ $revaluation->ppe->name }}</td>
                                                <td class="px-4 py-2">{{ \Carbon\Carbon::parse($revaluation->revaluation_date)->format('d/m/Y') }}</td>
                                                <td class="px-4 py-2 text-right">{{ number_format($revaluation->old_value, 2) }}</td>
                                                <td class="px-4 py-2 text-right">{{ number_format($revaluation->new_value, 2) }}</td>
                                                <td class="px-4 py-2 text-right">
                                                    <span class="{{ $revaluation->revaluation_amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                        {{ $revaluation->revaluation_amount > 0 ? '+' : '' }}{{ number_format($revaluation->revaluation_amount, 2) }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-2">{{ ucfirst($revaluation->revaluation_type) }}</td>
                                                <td class="px-4 py-2">
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                                        @if($revaluation->status == 'posted') bg-green-100 text-green-800
                                                        @elseif($revaluation->status == 'approved') bg-red-100 text-red-800
                                                        @else bg-yellow-100 text-yellow-800 @endif">
                                                        {{ ucfirst($revaluation->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">No revaluation records found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @break

                @default
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Welcome to PPE Management</h3>
                        <p class="text-gray-600">Select an option from the tabs above to get started</p>
                    </div>
            @endswitch
        </div>
    </div>

    {{-- Disposal Form Modal --}}
    @if($showDisposalForm)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Complete Asset Disposal</h3>
                    <button wire:click="resetDisposalForm" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form wire:submit.prevent="completeDisposal" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Disposal Date</label>
                            <input type="date" wire:model="disposal_date" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                            @error('disposal_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Disposal Method</label>
                            <select wire:model="disposal_method" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                                <option value="sold">Sold</option>
                                <option value="scrapped">Scrapped</option>
                                <option value="donated">Donated</option>
                                <option value="lost">Lost</option>
                                <option value="stolen">Stolen</option>
                                <option value="other">Other</option>
                            </select>
                            @error('disposal_method') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Disposal Proceeds</label>
                            <input type="number" step="0.01" wire:model="disposal_proceeds" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                            @error('disposal_proceeds') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Disposal Notes</label>
                        <textarea wire:model="disposal_notes" rows="3" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"></textarea>
                        @error('disposal_notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" wire:click="resetDisposalForm" 
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                            Complete Disposal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteConfirmation ?? false)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-2">Delete PPE Asset</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Are you sure you want to delete this asset? This action cannot be undone.
                    </p>
                </div>
                <div class="items-center px-4 py-3">
                    <button wire:click="cancelDelete" 
                        class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancel
                    </button>
                    <button wire:click="deleteAsset" 
                        class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-24 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Maintenance Form Modal --}}
    @if($showMaintenanceForm)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Schedule Maintenance</h3>
                    <button wire:click="resetMaintenanceForm" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form wire:submit.prevent="saveMaintenance">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">PPE Asset</label>
                            <select wire:model="ppeId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Select Asset</option>
                                @foreach(App\Models\PPE::where('status', 'active')->get() as $ppe)
                                <option value="{{ $ppe->id }}">{{ $ppe->name }} ({{ $ppe->asset_code }})</option>
                                @endforeach
                            </select>
                            @error('ppeId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Maintenance Type</label>
                            <select wire:model="maintenance_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="preventive">Preventive</option>
                                <option value="corrective">Corrective</option>
                                <option value="emergency">Emergency</option>
                            </select>
                            @error('maintenance_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Maintenance Date</label>
                            <input type="date" wire:model="maintenance_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('maintenance_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea wire:model="maintenance_description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                            @error('maintenance_description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cost</label>
                            <input type="number" step="0.01" wire:model="maintenance_cost" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('maintenance_cost') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Vendor</label>
                            <input type="text" wire:model="maintenance_vendor" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('maintenance_vendor') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" wire:click="resetMaintenanceForm" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Save Maintenance
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Transfer Form Modal --}}
    @if($showTransferForm)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Asset Transfer</h3>
                    <button wire:click="resetTransferForm" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form wire:submit.prevent="saveTransfer">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">PPE Asset</label>
                            <select wire:model="ppeId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Select Asset</option>
                                @foreach(App\Models\PPE::where('status', 'active')->get() as $ppe)
                                <option value="{{ $ppe->id }}">{{ $ppe->name }} ({{ $ppe->asset_code }})</option>
                                @endforeach
                            </select>
                            @error('ppeId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Transfer To Location</label>
                            <input type="text" wire:model="transfer_to_location" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('transfer_to_location') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Transfer To Department</label>
                            <input type="text" wire:model="transfer_to_department" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('transfer_to_department') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">New Custodian</label>
                            <input type="text" wire:model="transfer_to_custodian" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('transfer_to_custodian') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Transfer Date</label>
                            <input type="date" wire:model="transfer_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('transfer_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Reason for Transfer</label>
                            <textarea wire:model="transfer_reason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                            @error('transfer_reason') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" wire:click="resetTransferForm" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Process Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Insurance Form Modal --}}
    @if($showInsuranceForm)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Add Insurance Policy</h3>
                    <button wire:click="resetInsuranceForm" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form wire:submit.prevent="saveInsurance">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">PPE Asset</label>
                            <select wire:model="ppeId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Select Asset</option>
                                @foreach(App\Models\PPE::where('status', 'active')->get() as $ppe)
                                <option value="{{ $ppe->id }}">{{ $ppe->name }} ({{ $ppe->asset_code }})</option>
                                @endforeach
                            </select>
                            @error('ppeId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Policy Number</label>
                            <input type="text" wire:model="policy_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('policy_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Insurance Company</label>
                            <input type="text" wire:model="insurance_company" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('insurance_company') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Coverage Type</label>
                            <select wire:model="coverage_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="comprehensive">Comprehensive</option>
                                <option value="fire">Fire</option>
                                <option value="theft">Theft</option>
                                <option value="damage">Damage</option>
                            </select>
                            @error('coverage_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Start Date</label>
                                <input type="date" wire:model="insurance_start_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @error('insurance_start_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">End Date</label>
                                <input type="date" wire:model="insurance_end_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @error('insurance_end_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Premium Amount</label>
                            <input type="number" step="0.01" wire:model="premium_amount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('premium_amount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" wire:click="resetInsuranceForm" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit" 
                            onclick="console.log('Save Insurance button clicked at ' + new Date().toISOString())"
                            wire:loading.attr="disabled"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="saveInsurance">Save Insurance</span>
                            <span wire:loading wire:target="saveInsurance">Processing...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Revaluation Form Modal --}}
    @if($showRevaluationForm)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Asset Revaluation</h3>
                    <button wire:click="resetRevaluationForm" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form wire:submit.prevent="saveRevaluation">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">PPE Asset</label>
                            <select wire:model="ppeId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Select Asset</option>
                                @foreach(App\Models\PPE::where('status', 'active')->get() as $ppe)
                                <option value="{{ $ppe->id }}">{{ $ppe->name }} - Current Value: {{ number_format($ppe->closing_value ?: $ppe->purchase_price, 2) }}</option>
                                @endforeach
                            </select>
                            @error('ppeId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Revaluation Date</label>
                            <input type="date" wire:model="revaluation_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('revaluation_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">New Value</label>
                            <input type="number" step="0.01" wire:model="new_value" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('new_value') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Valuation Method</label>
                            <select wire:model="valuation_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="market_value">Market Value</option>
                                <option value="professional_valuation">Professional Valuation</option>
                                <option value="cost_approach">Cost Approach</option>
                                <option value="income_approach">Income Approach</option>
                            </select>
                            @error('valuation_method') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Reason for Revaluation</label>
                            <textarea wire:model="revaluation_reason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                            @error('revaluation_reason') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" wire:click="resetRevaluationForm" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Save Revaluation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Loading Overlay --}}
    <div wire:loading.flex wire:target="store, update, deleteAsset, runDepreciation, generateReport, exportExcel, exportPdf" 
        class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 items-center justify-center">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-4">
            <svg class="animate-spin h-8 w-8 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-700 font-medium">Processing...</span>
        </div>
    </div>
</div>