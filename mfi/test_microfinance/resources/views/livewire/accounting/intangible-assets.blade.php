<div class="bg-gray-50 p-6">
    {{-- Header Section --}}
    <div class="bg-gradient-to-r from-indigo-600 to-purple-700 rounded-t-xl p-6 text-white">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">Assets Management</h1>
                <p class="text-indigo-100 mt-1">Manage tangible and intangible assets</p>
            </div>
            <div class="flex space-x-2">
                <button class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Export Report</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Main Content --}}
    <div class="bg-white border-x border-b border-gray-200 rounded-b-xl">
        <div class="flex gap-6 p-6">
            {{-- Assets Table --}}
            <div class="flex-1">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Registered Assets</h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Asset Name</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Type</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700">Value (TZS)</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Acquisition Date</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($intangibleAssets as $asset)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-gray-900 font-medium">{{ $asset->name }}</td>
                                    <td class="px-4 py-3 text-gray-700">
                                        <span class="px-2 py-1 text-xs rounded-full
                                            @if($asset->type == 'shortterm_assets') bg-red-100 text-red-700
                                            @elseif($asset->type == 'longterm_assets') bg-green-100 text-green-700
                                            @elseif($asset->type == 'intangible_assets') bg-purple-100 text-purple-700
                                            @else bg-gray-100 text-gray-700
                                            @endif">
                                            @if($asset->type == 'shortterm_assets')
                                                Short Term
                                            @elseif($asset->type == 'longterm_assets')
                                                Long Term
                                            @elseif($asset->type == 'intangible_assets')
                                                Intangible
                                            @else
                                                {{ ucfirst($asset->type) }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-900">{{ number_format($asset->value, 2) }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ \Carbon\Carbon::parse($asset->acquisition_date)->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-center space-x-2">
                                            <button wire:click="edit({{ $asset->id }})" 
                                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-xs transition-colors">
                                                Edit
                                            </button>
                                            <button wire:click="liquidate({{ $asset->id }})" 
                                                class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-md text-xs transition-colors">
                                                Liquidate
                                            </button>
                                            <button wire:click="delete({{ $asset->id }})" 
                                                onclick="return confirm('Are you sure you want to delete this asset?')"
                                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-xs transition-colors">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                        No assets registered yet. Use the form to add your first asset.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Registration Form --}}
            <div class="w-96 bg-gray-50 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">
                    {{ $isEdit ? 'Edit Asset' : 'Register New Asset' }}
                </h2>

                <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}" class="space-y-4">
                    {{-- Asset Type --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Asset Type</label>
                        <select wire:model="type" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                            <option value="">Select Asset Type</option>
                            <option value="shortterm_assets">Short Term Asset</option>
                            <option value="longterm_assets">Long Term Asset</option>
                            <option value="intangible_assets">Intangible Asset</option>
                        </select>
                        @error('type') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Asset Name --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Asset Name</label>
                        <input type="text" wire:model="name" 
                            placeholder="Enter asset name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                        @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Asset Value --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Asset Value (TZS)</label>
                        <input type="number" wire:model="value" step="0.01" 
                            placeholder="0.00"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                        @error('value') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Account Selection - Corrected Flow --}}
                    <div class="col-span-2 bg-gray-50 rounded-lg border border-gray-200 p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Account Selection</h3>
                        <p class="text-sm text-gray-600 mb-4">Select where to create the intangible asset account and the other account for double-entry posting</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="parent_account_number" class="block text-sm font-medium text-gray-700 mb-2">
                                    Parent Account (Create Asset Under) *
                                </label>
                                <select wire:model="parent_account_number" id="parent_account_number" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm" required>
                                    <option value="">-- Select Parent Account --</option>
                                    @foreach($parentAccounts as $account)
                                        <option value="{{ $account->account_number }}">
                                            {{ $account->account_number }} - {{ $account->account_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">New intangible asset account will be created under this parent</p>
                                @error('parent_account_number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label for="other_account_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Other Account (Cash/Bank) *
                                </label>
                                <select wire:model="other_account_id" id="other_account_id" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm" required>
                                    <option value="">-- Select Cash/Bank Account --</option>
                                    @foreach($otherAccounts as $account)
                                        <option value="{{ $account->internal_mirror_account_number }}">
                                            {{ $account->bank_name }} - {{ $account->account_number }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Account to be credited (Cash/Bank payment)</p>
                                @error('other_account_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Acquisition Date --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Acquisition Date</label>
                        <input type="date" wire:model="acquisition_date" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                        @error('acquisition_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-4">
                        <button type="submit" 
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                            {{ $isEdit ? 'Update Asset' : 'Register Asset' }}
                        </button>
                        
                        @if($isEdit)
                            <button type="button" wire:click="resetInputFields" 
                                class="w-full mt-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium py-2 px-4 rounded-lg transition-colors">
                                Cancel Edit
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Summary Statistics --}}
    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Total Assets</h4>
            <p class="text-2xl font-bold text-gray-900">{{ $intangibleAssets->count() }}</p>
            <p class="text-xs text-gray-600">Registered assets</p>
        </div>
        
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Total Value</h4>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($intangibleAssets->sum('value'), 2) }}</p>
            <p class="text-xs text-gray-600">TZS</p>
        </div>
        
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Short Term</h4>
            <p class="text-2xl font-bold text-gray-900">{{ $intangibleAssets->where('type', 'shortterm_assets')->count() }}</p>
            <p class="text-xs text-gray-600">Assets</p>
        </div>
        
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Long Term</h4>
            <p class="text-2xl font-bold text-gray-900">{{ $intangibleAssets->where('type', 'longterm_assets')->count() }}</p>
            <p class="text-xs text-gray-600">Assets</p>
        </div>
    </div>
</div>