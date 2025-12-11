<div>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800">Approval Matrix Configuration</h2>
                        <button wire:click="create" class="bg-blue-500 hover:bg-blue-900 text-white font-bold py-2 px-4 rounded">
                            Add New Configuration
                        </button>
                    </div>

                    @if($showForm)
                        <div class="bg-gray-50 p-6 rounded-lg mb-6">
                            <form wire:submit.prevent="save">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Process Type</label>
                                        <select wire:model="processType" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">Select Process Type</option>
                                            <option value="loan">Loan</option>
                                            <option value="expense">Expense</option>
                                            <option value="budget">Budget</option>
                                            <option value="hire">Hire</option>
                                        </select>
                                        @error('processType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Process Name</label>
                                        <input type="text" wire:model="processName" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('processName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Process Code</label>
                                        <input type="text" wire:model="processCode" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('processCode') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Level</label>
                                        <input type="number" wire:model="level" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('level') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Approver Role</label>
                                        <select wire:model="approverRole" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">Select Role</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('approverRole') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Approver Sub Role</label>
                                        <select wire:model="approverSubRole" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">Select Sub Role</option>
                                            @foreach($subRoles as $subRole)
                                                <option value="{{ $subRole->name }}">{{ $subRole->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('approverSubRole') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Minimum Amount</label>
                                        <input type="number" step="0.01" wire:model="minAmount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('minAmount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Maximum Amount</label>
                                        <input type="number" step="0.01" wire:model="maxAmount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('maxAmount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-span-2">
                                        <label class="flex items-center">
                                            <input type="checkbox" wire:model="isActive" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-600">Active</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="mt-6 flex justify-end space-x-3">
                                    <button type="button" wire:click="$set('showForm', false)" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                        Cancel
                                    </button>
                                    <button type="submit" class="bg-blue-500 hover:bg-blue-900 text-white font-bold py-2 px-4 rounded">
                                        {{ $editingId ? 'Update' : 'Save' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Process</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approver</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount Range</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($configs as $config)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $config->process_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $config->process_code }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $config->level }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $config->approver_role }}</div>
                                            @if($config->approver_sub_role)
                                                <div class="text-sm text-gray-500">{{ $config->approver_sub_role }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($config->min_amount || $config->max_amount)
                                                {{ number_format($config->min_amount, 2) }} - {{ number_format($config->max_amount, 2) }}
                                            @else
                                                No limit
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $config->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $config->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button wire:click="edit({{ $config->id }})" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                            <button wire:click="toggleStatus({{ $config->id }})" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                                {{ $config->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                            <button wire:click="delete({{ $config->id }})" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this configuration?')">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 