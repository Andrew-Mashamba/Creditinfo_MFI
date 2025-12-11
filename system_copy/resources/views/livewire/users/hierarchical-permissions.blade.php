<div class="p-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-800 rounded-xl p-6 mb-6 text-white">
        <h2 class="text-3xl font-bold mb-4">Permissions Management</h2>
        <p class="opacity-90">Configure permissions for roles and sub-roles with inheritance</p>
        
        @if($selectedEntity)
            <div class="mt-4 grid grid-cols-4 gap-4">
                <div class="bg-white/20 rounded-lg p-2">
                    <div class="text-2xl font-bold">{{ $stats['total_permissions'] }}</div>
                    <div class="text-xs">Total Permissions</div>
                </div>
                <div class="bg-white/20 rounded-lg p-2">
                    <div class="text-2xl font-bold">{{ $stats['inherited'] }}</div>
                    <div class="text-xs">Inherited</div>
                </div>
                <div class="bg-white/20 rounded-lg p-2">
                    <div class="text-2xl font-bold">{{ $stats['overridden'] }}</div>
                    <div class="text-xs">Overridden</div>
                </div>
                <div class="bg-white/20 rounded-lg p-2">
                    <div class="text-2xl font-bold">{{ $stats['custom'] }}</div>
                    <div class="text-xs">Custom</div>
                </div>
            </div>
        @endif
    </div>

    <!-- Messages -->
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Selection Section -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4">Select Role or Sub-Role</h3>
        
        <div class="grid grid-cols-3 gap-4">
            <!-- Department Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">1. Select Department</label>
                <select wire:model="selectedDepartment" class="w-full px-4 py-2 border rounded-lg">
                    <option value="">Choose Department...</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">
                            {{ $dept->department_name }} ({{ $dept->roles_count }} roles)
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Role Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">2. Select Role</label>
                <select wire:model="selectedRole" class="w-full px-4 py-2 border rounded-lg" 
                        @if(!$selectedDepartment) disabled @endif>
                    <option value="">Choose Role...</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">
                            {{ $role->name }} ({{ $role->subRoles->count() }} sub-roles)
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Sub-Role Selection (Optional) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">3. Select Sub-Role (Optional)</label>
                <select wire:model="selectedSubRole" class="w-full px-4 py-2 border rounded-lg"
                        @if(!$selectedRole) disabled @endif>
                    <option value="">Parent Role Permissions</option>
                    @foreach($subRoles as $subRole)
                        <option value="{{ $subRole->id }}">{{ $subRole->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        @if($selectedEntity)
            <div class="mt-4 p-3 bg-purple-50 rounded-lg">
                <p class="text-sm text-purple-700">
                    <strong>Configuring:</strong> 
                    @if(strpos($selectedEntity, 'subrole:') === 0)
                        Sub-Role Permissions (can inherit and override)
                    @else
                        Role Permissions (base permissions)
                    @endif
                </p>
            </div>
        @endif
    </div>

    @if($selectedEntity)
        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-lg p-4 mb-6">
            <div class="flex justify-between items-center">
                <div class="flex space-x-2">
                    <span class="text-sm font-medium text-gray-700">Quick Templates:</span>
                    @foreach($presets as $key => $preset)
                        <button wire:click="applyPreset('{{ $key }}')" 
                                class="px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 text-sm">
                            {{ $preset['name'] }}
                        </button>
                    @endforeach
                </div>
                <div class="flex space-x-2">
                    @if(strpos($selectedEntity, 'subrole:') === 0)
                        <button wire:click="resetToInherited" 
                                class="px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 text-sm">
                            Reset to Inherited
                        </button>
                    @endif
                    <button wire:click="clearPermissions" 
                            class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm">
                        Clear All
                    </button>
                </div>
            </div>
        </div>

        <!-- Permissions by Module -->
        <div class="space-y-4">
            @foreach($groupedPermissions as $module => $modulePermissions)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
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
                            @php
                                $isEnabled = $permissions[$permission->id] ?? false;
                                $isInherited = isset($inheritedPermissions[$permission->id]);
                                $isOverridden = isset($overriddenPermissions[$permission->id]);
                            @endphp
                            <label class="relative flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                <input type="checkbox" 
                                       wire:click="togglePermission({{ $permission->id }})"
                                       @if($isEnabled) checked @endif
                                       class="h-4 w-4 text-blue-600 rounded focus:ring-blue-500 
                                              {{ $isInherited && !$isOverridden ? 'opacity-75' : '' }}">
                                <span class="text-sm">
                                    {{ str_replace($module . '.', '', $permission->name) }}
                                </span>
                                @if($isInherited && $showInherited && !$isOverridden)
                                    <span class="absolute top-0 right-0 w-2 h-2 bg-blue-500 rounded-full" 
                                          title="Inherited from parent role"></span>
                                @endif
                                @if($isOverridden)
                                    <span class="absolute top-0 right-0 w-2 h-2 bg-orange-500 rounded-full" 
                                          title="Overridden from parent"></span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
            
        <!-- Legend and Save -->
        <div class="mt-6 bg-white rounded-lg shadow p-4 flex justify-between items-center">
            <div class="flex space-x-4 text-sm">
                @if(strpos($selectedEntity, 'subrole:') === 0)
                    <div class="flex items-center">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                        <span>Inherited</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-2 h-2 bg-orange-500 rounded-full mr-2"></span>
                        <span>Overridden</span>
                    </div>
                @endif
                <div class="flex items-center">
                    <input type="checkbox" checked disabled class="mr-2">
                    <span>Enabled</span>
                </div>
            </div>
            
            <button wire:click="savePermissions" 
                    class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M5 13l4 4L19 7"></path>
                </svg>
                Save Permissions
            </button>
        </div>
    @else
        <!-- No Selection Message -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <svg class="w-12 h-12 text-yellow-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-yellow-800 mb-2">No Role Selected</h3>
            <p class="text-yellow-700">Please select a department and role above to configure permissions.</p>
        </div>
    @endif
</div>