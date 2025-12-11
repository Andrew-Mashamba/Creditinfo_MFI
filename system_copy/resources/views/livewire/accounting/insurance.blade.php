<div>
    <div class="max-w-7xl mx-auto py-10">
        <h2 class="text-2xl font-bold mb-6">Manage Insurances</h2>

        <!-- Success Message -->
        @if (session()->has('message'))
            <div class="bg-green-500 text-white px-4 py-2 mb-4 rounded">
                {{ session('message') }}
            </div>
        @endif

        <!-- Insurance Form -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <form wire:submit.prevent="{{ $insuranceId ? 'updateInsurance' : 'createInsurance' }}">
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Insurance Name</label>
                    <input type="text" id="name" wire:model="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Enter Insurance Name">
                    @error('name') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label for="category" class="block text-gray-700 text-sm font-bold mb-2">Category</label>
                    <select id="category" wire:model="category" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @foreach($categories as $categoryOption)
                            <option value="{{ $categoryOption }}">{{ ucfirst($categoryOption) }}</option>
                        @endforeach
                    </select>
                    @error('category') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- Account Selection - Corrected Flow --}}
                <div class="bg-gray-50 rounded-lg border border-gray-200 p-4 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Account Selection</h3>
                    <p class="text-sm text-gray-600 mb-4">Select where to create the insurance account and the other account for double-entry posting</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="parent_account_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Parent Account (Create Insurance Under) *
                            </label>
                            <select wire:model="parent_account_number" id="parent_account_number" 
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                <option value="">-- Select Parent Account --</option>
                                @foreach($creditAccounts as $account)
                                    <option value="{{ $account->account_number }}">
                                        {{ $account->account_number }} - {{ $account->account_name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">New insurance account will be created under this parent</p>
                        </div>
                        
                        <div>
                            <label for="other_account_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Other Account (Cash/Bank) *
                            </label>
                            <select wire:model="other_account_id" id="other_account_id" 
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                <option value="">-- Select Cash/Bank Account --</option>
                                @foreach($otherAccounts as $account)
                                    <option value="{{ $account->internal_mirror_account_number }}">
                                        {{ $account->bank_name }} - {{ $account->account_number }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Account to be debited (Cash/Bank payment)</p>
                        </div>
                    </div>
                </div>

                <!-- Show for Employees or Members -->
                @if($category != 'loans')
                    <div class="mb-4">
                        <label for="coverage_amount" class="block text-gray-700 text-sm font-bold mb-2">Coverage Amount</label>
                        <input type="number" id="coverage_amount" wire:model="coverage_amount" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Enter Coverage Amount">
                        @error('coverage_amount') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="premium" class="block text-gray-700 text-sm font-bold mb-2">Premium</label>
                        <input type="number" id="premium" wire:model="premium" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Enter Premium">
                        @error('premium') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>
                @endif

                <!-- Show for Loans -->
                @if($category == 'loans')
                    <div class="mb-4">
                        <label for="monthly_rate" class="block text-gray-700 text-sm font-bold mb-2">Monthly Insurance Rate (%)</label>
                        <input type="number" id="monthly_rate" wire:model="monthly_rate" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Enter Monthly Rate">
                        @error('monthly_rate') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>
                @endif

                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-500 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        {{ $insuranceId ? 'Update Insurance' : 'Create Insurance' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Insurance List -->
        <div class="bg-white shadow rounded-lg p-6">
            <table class="min-w-full table-auto">
                <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Category</th>
                    <th class="px-4 py-2">Coverage Amount</th>
                    <th class="px-4 py-2">Premium</th>
                    <th class="px-4 py-2">Monthly Rate</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($insurances as $insurance)
                    <tr>
                        <td class="border px-4 py-2">{{ $insurance->name }}</td>
                        <td class="border px-4 py-2">{{ ucfirst($insurance->category) }}</td>
                        <td class="border px-4 py-2">{{ $insurance->coverage_amount ? number_format($insurance->coverage_amount, 2) : '-' }}</td>
                        <td class="border px-4 py-2">{{ $insurance->premium ? number_format($insurance->premium, 2) : '-' }}</td>
                        <td class="border px-4 py-2">{{ $insurance->monthly_rate ? number_format($insurance->monthly_rate, 2) . '%' : '-' }}</td>
                        <td class="border px-4 py-2">
                            <button wire:click="editInsurance({{ $insurance->id }})" class="bg-yellow-500 text-white px-4 py-1 rounded">Edit</button>
                            <button wire:click="deleteInsurance({{ $insurance->id }})" class="bg-red-500 text-white px-4 py-1 rounded">Delete</button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
