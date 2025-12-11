<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Email Rules</h2>
        <div class="flex space-x-2">
            <button wire:click="processAllEmails" class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-700 transition">
                Process New Emails
            </button>
            <button wire:click="openCreateModal" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                Create New Rule
            </button>
        </div>
    </div>

    <!-- Rules List -->
    <div class="space-y-4">
        @forelse($emailRules as $rule)
            <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2">
                            <h3 class="text-lg font-semibold @if(!$rule->is_active) text-gray-400 @endif">
                                {{ $rule->name }}
                            </h3>
                            @if($rule->priority > 0)
                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                    Priority: {{ $rule->priority }}
                                </span>
                            @endif
                            @if(!$rule->is_active)
                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                                    Inactive
                                </span>
                            @endif
                        </div>
                        
                        @if($rule->description)
                            <p class="text-sm text-gray-600 mt-1">{{ $rule->description }}</p>
                        @endif
                        
                        <div class="mt-2 flex items-center space-x-4 text-sm">
                            <span class="text-gray-500">
                                {{ count($rule->conditions) }} condition{{ count($rule->conditions) !== 1 ? 's' : '' }}
                                ({{ $rule->condition_logic === 'all' ? 'All' : 'Any' }})
                            </span>
                            <span class="text-gray-500">
                                {{ count($rule->actions) }} action{{ count($rule->actions) !== 1 ? 's' : '' }}
                            </span>
                            @if($rule->times_applied > 0)
                                <span class="text-green-600">
                                    Applied {{ $rule->times_applied }} time{{ $rule->times_applied !== 1 ? 's' : '' }}
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-2 ml-4">
                        <button 
                            wire:click="toggleRule({{ $rule->id }})"
                            class="p-2 text-gray-600 hover:text-gray-900 transition"
                            title="{{ $rule->is_active ? 'Disable' : 'Enable' }} rule"
                        >
                            @if($rule->is_active)
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                            @endif
                        </button>
                        
                        <button 
                            wire:click="openEditModal({{ $rule->id }})"
                            class="p-2 text-blue-600 hover:text-blue-800 transition"
                            title="Edit rule"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        
                        <button 
                            wire:click="deleteRule({{ $rule->id }})"
                            onclick="return confirm('Are you sure you want to delete this rule?')"
                            class="p-2 text-red-600 hover:text-red-800 transition"
                            title="Delete rule"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-gray-50 rounded-lg p-8 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No email rules yet</h3>
                <p class="text-gray-600 mb-4">Create rules to automatically organize your emails</p>
                <button wire:click="openCreateModal" class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-700 transition">
                    Create Your First Rule
                </button>
            </div>
        @endforelse
    </div>

    <!-- Create/Edit Modal -->
    @if($showCreateModal || $showEditModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-10 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-xl bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">
                            {{ $showEditModal ? 'Edit' : 'Create' }} Email Rule
                        </h3>
                        <button 
                            wire:click="{{ $showEditModal ? 'closeEditModal' : 'closeCreateModal' }}"
                            class="text-gray-400 hover:text-gray-600"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Rule Templates (for create only) -->
                    @if($showCreateModal && count($ruleTemplates) > 0)
                        <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                            <p class="text-sm text-blue-800 mb-2">Quick start with a template:</p>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($ruleTemplates as $index => $template)
                                    <button 
                                        wire:click="applyTemplate({{ $index }})"
                                        class="text-left p-2 bg-white rounded border border-blue-200 hover:border-blue-400 transition text-sm"
                                    >
                                        <div class="font-medium">{{ $template['name'] }}</div>
                                        <div class="text-xs text-gray-600">{{ $template['description'] }}</div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <form wire:submit.prevent="{{ $showEditModal ? 'updateRule' : 'createRule' }}">
                        <!-- Basic Info -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Rule Name</label>
                                <input 
                                    type="text" 
                                    wire:model="ruleName" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="e.g., Move newsletters to folder"
                                    required
                                >
                                @error('ruleName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Priority (0-100)</label>
                                <input 
                                    type="number" 
                                    wire:model="priority" 
                                    min="0" 
                                    max="100"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                @error('priority') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description (optional)</label>
                            <textarea 
                                wire:model="ruleDescription" 
                                rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="What does this rule do?"
                            ></textarea>
                        </div>

                        <!-- Conditions -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-sm font-medium text-gray-700">
                                    When 
                                    <select wire:model="conditionLogic" class="mx-1 px-2 py-1 border border-gray-300 rounded">
                                        <option value="all">all</option>
                                        <option value="any">any</option>
                                    </select>
                                    of these conditions are met:
                                </label>
                                <button 
                                    type="button"
                                    wire:click="addCondition"
                                    class="text-sm text-blue-600 hover:text-blue-800"
                                >
                                    + Add Condition
                                </button>
                            </div>
                            
                            <div class="space-y-2">
                                @foreach($conditions as $index => $condition)
                                    <div class="flex items-center space-x-2">
                                        <select 
                                            wire:model="conditions.{{ $index }}.type"
                                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        >
                                            @foreach($availableConditions as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        
                                        @if(!in_array($condition['type'], ['has_attachment', 'is_unread']))
                                            <input 
                                                type="text"
                                                wire:model="conditions.{{ $index }}.value"
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                placeholder="Value"
                                            >
                                        @else
                                            <select 
                                                wire:model="conditions.{{ $index }}.value"
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            >
                                                <option value="true">Yes</option>
                                                <option value="false">No</option>
                                            </select>
                                        @endif
                                        
                                        <button 
                                            type="button"
                                            wire:click="removeCondition({{ $index }})"
                                            class="p-2 text-red-600 hover:text-red-800"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            @error('conditions') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Actions -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-sm font-medium text-gray-700">Then perform these actions:</label>
                                <button 
                                    type="button"
                                    wire:click="addAction"
                                    class="text-sm text-blue-600 hover:text-blue-800"
                                >
                                    + Add Action
                                </button>
                            </div>
                            
                            <div class="space-y-2">
                                @foreach($actions as $index => $action)
                                    <div class="flex items-center space-x-2">
                                        <select 
                                            wire:model="actions.{{ $index }}.type"
                                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        >
                                            @foreach($availableActions as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        
                                        @if(in_array($action['type'], ['move_to_folder', 'forward_to', 'add_label']))
                                            <input 
                                                type="text"
                                                wire:model="actions.{{ $index }}.value"
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                placeholder="{{ $action['type'] === 'forward_to' ? 'Email address' : 'Value' }}"
                                            >
                                        @endif
                                        
                                        <button 
                                            type="button"
                                            wire:click="removeAction({{ $index }})"
                                            class="p-2 text-red-600 hover:text-red-800"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            @error('actions') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Active Status -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    wire:model="isActive"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                >
                                <span class="ml-2 text-sm text-gray-700">Rule is active</span>
                            </label>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-2">
                            <button 
                                type="button"
                                wire:click="{{ $showEditModal ? 'closeEditModal' : 'closeCreateModal' }}"
                                class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit"
                                class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-700 transition"
                            >
                                {{ $showEditModal ? 'Update' : 'Create' }} Rule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>