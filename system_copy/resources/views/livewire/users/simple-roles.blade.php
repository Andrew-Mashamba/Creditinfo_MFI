<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Roles Management</h2>
        <button wire:click="create" class="bg-blue-900 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            + New Role
        </button>
    </div>

    <!-- Search -->
    <div class="mb-4">
        <input type="text" wire:model.debounce.300ms="search" 
               placeholder="Search roles..." 
               class="w-full md:w-1/3 px-4 py-2 border rounded-lg">
    </div>

    <!-- Success/Error Messages -->
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($roles as $role)
                    <tr>
                        <td class="px-6 py-4">{{ $role->name }}</td>
                        <td class="px-6 py-4">{{ $role->department->department_name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $role->description ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <button wire:click="edit({{ $role->id }})" class="text-blue-600 hover:text-blue-800 mr-3">
                                Edit
                            </button>
                            <button wire:click="delete({{ $role->id }})" 
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
            {{ $roles->links() }}
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-bold mb-4">
                    {{ $editingId ? 'Edit Role' : 'Create Role' }}
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role Name</label>
                        <input type="text" wire:model="name" 
                               class="w-full px-3 py-2 border rounded-lg @error('name') border-red-500 @enderror">
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                        <select wire:model="department_id" 
                                class="w-full px-3 py-2 border rounded-lg @error('department_id') border-red-500 @enderror">
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                            @endforeach
                        </select>
                        @error('department_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea wire:model="description" rows="3"
                                  class="w-full px-3 py-2 border rounded-lg @error('description') border-red-500 @enderror"></textarea>
                        @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
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