<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Departments Management</h2>
        <button wire:click="create" class="bg-blue-900 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            + New Department
        </button>
    </div>

    <!-- Search -->
    <div class="mb-4">
        <input type="text" wire:model.debounce.300ms="search" 
               placeholder="Search departments..." 
               class="w-full md:w-1/3 px-4 py-2 border rounded-lg">
    </div>

    <!-- Messages -->
    @if (session()->has('message'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($departments as $dept)
                    <tr>
                        <td class="px-6 py-4">{{ $dept->department_name }}</td>
                        <td class="px-6 py-4">{{ $dept->department_code ?? $dept->code ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $dept->branch->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full {{ $dept->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $dept->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <button wire:click="edit({{ $dept->id }})" class="text-blue-600 hover:text-blue-800 mr-3">
                                Edit
                            </button>
                            <button wire:click="delete({{ $dept->id }})" 
                                    onclick="return confirm('Are you sure?')"
                                    class="text-red-600 hover:text-red-800">
                                Delete
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="px-6 py-3 bg-gray-50">
            {{ $departments->links() }}
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-bold mb-4">
                    {{ $editingId ? 'Edit Department' : 'Create Department' }}
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department Name</label>
                        <input type="text" wire:model="department_name" 
                               class="w-full px-3 py-2 border rounded-lg @error('department_name') border-red-500 @enderror">
                        @error('department_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department Code</label>
                        <input type="text" wire:model="department_code" 
                               class="w-full px-3 py-2 border rounded-lg @error('department_code') border-red-500 @enderror">
                        @error('department_code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                        <select wire:model="branch_id" 
                                class="w-full px-3 py-2 border rounded-lg @error('branch_id') border-red-500 @enderror">
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        @error('branch_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea wire:model="description" rows="3"
                                  class="w-full px-3 py-2 border rounded-lg"></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button wire:click="closeModal" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button wire:click="save" class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-700">
                        Save
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>