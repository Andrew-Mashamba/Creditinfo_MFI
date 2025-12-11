<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Permissions Management</h2>
        <div class="flex space-x-2">
            <!-- Quick Templates -->
            <button wire:click="quickAssign('admin')" class="px-3 py-1 bg-purple-600 text-white rounded hover:bg-purple-700 text-sm">
                Admin Template
            </button>
            <button wire:click="quickAssign('manager')" class="px-3 py-1 bg-blue-900 text-white rounded hover:bg-blue-700 text-sm">
                Manager Template
            </button>
            <button wire:click="quickAssign('user')" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                User Template
            </button>
            <button wire:click="quickAssign('none')" class="px-3 py-1 bg-gray-600 text-white rounded hover:bg-gray-700 text-sm">
                Clear All
            </button>
        </div>
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

    <!-- Role Selection -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Select Role to Configure</label>
        <select wire:model="selectedRole" class="w-full md:w-1/3 px-4 py-2 border rounded-lg">
            <option value="">Choose a role...</option>
            @foreach($roles as $role)
                <option value="{{ $role->id }}">
                    {{ $role->name }} 
                    @if($role->department)
                        ({{ $role->department->department_name }})
                    @endif
                </option>
            @endforeach
        </select>
    </div>

    @if($selectedRole)
    <!-- Permissions by Module -->
    <div class="space-y-4">
        @foreach($groupedPermissions as $module => $modulePermissions)
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <!-- Module Header -->
                <div class="bg-gray-50 px-6 py-3 border-b flex justify-between items-center">
                    <h3 class="text-lg font-semibold capitalize">
                        {{ str_replace('_', ' ', $module) }} Module
                        <span class="text-sm text-gray-500 ml-2">({{ $modulePermissions->count() }} permissions)</span>
                    </h3>
                    <button wire:click="toggleModulePermissions('{{ $module }}', {{ json_encode($modulePermissions->pluck('id')->toArray()) }})"
                            class="px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 text-sm">
                        Toggle All
                    </button>
                </div>
                
                <!-- Module Permissions Grid -->
                <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($modulePermissions as $permission)
                        <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                            <input type="checkbox" 
                                   wire:click="togglePermission({{ $permission->id }})"
                                   @if(isset($permissions[$permission->id]) && $permissions[$permission->id]) 
                                       checked 
                                   @endif
                                   class="h-4 w-4 text-blue-600 rounded focus:ring-blue-500">
                            <span class="text-sm">
                                {{ str_replace($module . '.', '', $permission->name) }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
    
    <!-- Save Button -->
    <div class="mt-6 flex justify-end">
        <button wire:click="savePermissions" 
                class="px-6 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-700 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M5 13l4 4L19 7"></path>
            </svg>
            Save Permissions
        </button>
    </div>
    @else
    <!-- No Role Selected Message -->
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 p-4 rounded-lg">
        <div class="flex">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" 
                      d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" 
                      clip-rule="evenodd"></path>
            </svg>
            Please select a role above to configure its permissions.
        </div>
    </div>
    @endif
</div>