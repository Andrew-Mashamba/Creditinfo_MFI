<div class="p-4">
    <!-- Search and Add Button -->
    <div class="flex justify-between items-center mb-4">
        <div class="w-1/3">
            <input type="text" wire:model.debounce.300ms="search" placeholder="Search services..." 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <button wire:click="$set('show_service_details', true)" 
            class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            Add New Service
        </button>
    </div>

    <!-- Services Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mandatory</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recurring</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lower Limit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Mode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Debit Account</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credit Account</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($services as $service)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $service->code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $service->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($service->description, 50) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($service->is_mandatory)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Yes</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">No</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($service->isRecurring)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Yes</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">No</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ number_format($service->lower_limit, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @switch($service->paymentMode)
                                @case('1')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Partial</span>
                                    @break
                                @case('2')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Full</span>
                                    @break
                                @case('3')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Exact</span>
                                    @break
                                @case('4')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Limited</span>
                                    @break
                                @case('5')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Infinity</span>
                                    @break
                            @endswitch
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($service->debit_account)
                                <div class="text-sm font-medium text-gray-900">
                                    {{ DB::table('accounts')->where('account_number', $service->debit_account)->value('account_name') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $service->debit_account }}
                                </div>
                            @else
                                <span class="text-gray-400">Not set</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($service->credit_account)
                                <div class="text-sm font-medium text-gray-900">
                                    {{ DB::table('accounts')->where('account_number', $service->credit_account)->value('account_name') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $service->credit_account }}
                                </div>
                            @else
                                <span class="text-gray-400">Not set</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <button wire:click="viewServiceDetails({{ $service->id }})" 
                                class="text-blue-600 hover:text-blue-900">Edit</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                            No services found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $services->links() }}
    </div>

    <!-- Service Details Modal -->
    @if($show_service_details)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" id="modal">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
                        {{ $selected_service ? 'Edit Service' : 'Add New Service' }}
                    </h3>
                    
                    <form wire:submit.prevent="saveService">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                                Name
                            </label>
                            <input type="text" wire:model="name" id="name"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="code">
                                Code (3 characters)
                            </label>
                            <input type="text" wire:model="code" id="code" maxlength="3"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $selected_service ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                {{ $selected_service ? 'disabled' : '' }}>
                            @error('code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                                Description
                            </label>
                            <textarea wire:model="description" id="description" rows="3"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                            @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="paymentMode">
                                Payment Mode
                            </label>
                            <select wire:model="paymentMode" id="paymentMode"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="1">Partial</option>
                                <option value="2">Full</option>
                                <option value="3">Exact</option>
                                <option value="4">Limited</option>
                                <option value="5">Infinity</option>
                            </select>
                            @error('paymentMode') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="debit_category">
                                Debit Account Category
                            </label>
                            <select wire:model="debit_category" id="debit_category"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">Select Category</option>
                                @foreach(DB::table('accounts')->whereNotNull('account_name')->distinct()->get() as $category)
                                    <option value="{{ $category->major_category_code }}">{{ $category->account_name }}</option>
                                @endforeach
                            </select>
                            @error('debit_category') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        @if($debit_category)
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="debit_subcategory">
                                Debit Account Subcategory
                            </label>
                            <select wire:model="debit_subcategory" id="debit_subcategory"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">Select Subcategory</option>
                                @foreach(DB::table('accounts')
                                    ->where('major_category_code', $debit_category)
                                    ->where('account_level', 2)
                                    
                                    ->get() as $subcategory)
                                    <option value="{{ $subcategory->category_code }}">{{ $subcategory->account_name }}</option>
                                @endforeach
                            </select>
                            @error('debit_subcategory') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        @if($debit_subcategory_code)
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="debit_account">
                                Debit Account
                            </label>
                            <select wire:model="debit_account" id="debit_account"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">Select Account</option>
                                @foreach(DB::table('accounts')
                                    ->where('major_category_code', $debit_category_code)
                                    ->where('category_code', $debit_subcategory_code)
                                    ->where('account_level', 3)
                                    ->get() as $account)
                                    <option value="{{ $account->account_number }}">
                                        {{ $account->account_name }} - {{ $account->account_number }}
                                    </option>
                                @endforeach
                            </select>
                            @error('debit_account') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="right_category">
                                Credit Account Category
                            </label>
                            <select wire:model="right_category" id="right_category"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">Select Category</option>
                                @foreach(DB::table('accounts')->whereNotNull('account_name')->distinct()->get() as $category)
                                    <option value="{{ $category->major_category_code }}">{{ $category->account_name }}</option>
                                @endforeach
                            </select>
                            @error('right_category') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        @if($right_category_code)
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="right_account">
                                Credit Account
                            </label>
                            <select wire:model="right_account" id="right_account"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">Select Account</option>
                                @foreach(DB::table('accounts')
                                    ->where('major_category_code', $right_category_code)
                                    ->where('account_level', 3)
                                    ->get() as $account)
                                    <option value="{{ $account->account_number }}">
                                        {{ $account->account_name }} - {{ $account->account_number }}
                                    </option>
                                @endforeach
                            </select>
                            @error('right_account') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="lower_limit">
                                    Lower Limit
                                </label>
                                <input type="number" wire:model="lower_limit" id="lower_limit" step="0.01"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                @error('lower_limit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="upper_limit">
                                    Upper Limit
                                </label>
                                <input type="number" wire:model="upper_limit" id="upper_limit" step="0.01"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                @error('upper_limit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="flex items-center mb-4">
                            <input type="checkbox" wire:model="is_mandatory" id="is_mandatory"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_mandatory" class="ml-2 block text-sm text-gray-900">
                                Mandatory Service
                            </label>
                        </div>

                        <div class="flex items-center mb-4">
                            <input type="checkbox" wire:model="isRecurring" id="isRecurring"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="isRecurring" class="ml-2 block text-sm text-gray-900">
                                Recurring Service
                            </label>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button type="button" wire:click="closeServiceDetails"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Save Service
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="fixed bottom-0 right-0 m-4 p-4 bg-green-500 text-white rounded-lg shadow-lg">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed bottom-0 right-0 m-4 p-4 bg-red-500 text-white rounded-lg shadow-lg">
            {{ session('error') }}
        </div>
    @endif
</div>
