{{-- Modal Backdrop --}}
<div class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50" id="modal-backdrop">
    {{-- Modal Content --}}
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
        {{-- Modal Header --}}
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-900">
                {{ $editingId ? 'Edit' : 'Create' }} Process Code Configuration
            </h3>
            <button type="button" 
                    wire:click="$set('showForm', false)" 
                    class="p-2 text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-full transition-colors"
                    aria-label="Close modal">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Modal Body --}}
        <form wire:submit.prevent="save" class="space-y-6">
            <!-- Basic Information Section -->
            <div class="bg-gray-50/50 p-6 rounded-xl border border-gray-100">
                <h4 class="text-lg font-semibold text-gray-800 mb-4">Basic Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="processCode" class="block text-sm font-medium text-gray-700 mb-1">
                            Process Code <span class="text-red-500">*</span>
                            <span class="ml-1 text-gray-400 cursor-help" title="Unique identifier for the process">ⓘ</span>
                        </label>
                        <input type="text" 
                               id="processCode" 
                               wire:model.defer="processCode" 
                               placeholder="e.g., LOAN_APP, SHARE_WD" 
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 py-2.5 px-3.5 transition-colors text-gray-900 placeholder-gray-400" 
                               required 
                               aria-describedby="processCode-error">
                        @error('processCode') 
                            <span id="processCode-error" class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                        @enderror
                    </div>
                    <div>
                        <label for="processName" class="block text-sm font-medium text-gray-700 mb-1">
                            Process Name <span class="text-red-500">*</span>
                            <span class="ml-1 text-gray-400 cursor-help" title="Display name for the process">ⓘ</span>
                        </label>
                        <input type="text" 
                               id="processName" 
                               wire:model.defer="processName" 
                               placeholder="e.g., Loan Application, Share Withdrawal" 
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 py-2.5 px-3.5 transition-colors text-gray-900 placeholder-gray-400" 
                               required 
                               aria-describedby="processName-error">
                        @error('processName') 
                            <span id="processName-error" class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                        @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            Description <span class="text-red-500">*</span>
                            <span class="ml-1 text-gray-400 cursor-help" title="Detailed description of the process">ⓘ</span>
                        </label>
                        <textarea id="description" 
                                  wire:model.defer="description" 
                                  rows="4" 
                                  placeholder="Enter a detailed description of this process..." 
                                  class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 py-2.5 px-3.5 transition-colors text-gray-900 placeholder-gray-400 resize-y" 
                                  required 
                                  aria-describedby="description-error"></textarea>
                        @error('description') 
                            <span id="description-error" class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                        @enderror
                    </div>
                </div>
            </div>

            

            <!-- Approval Settings Section -->
            <div class="bg-gray-50/50 p-6 rounded-xl border border-gray-100">
                <h4 class="text-lg font-semibold text-gray-800 mb-4">Approval Settings</h4>
                <div class="space-y-6">
                    <div class="flex flex-wrap gap-6">
                        <label class="inline-flex items-center">
                            <input type="checkbox" 
                                   wire:model="requiresFirstChecker" 
                                   class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 shadow-sm transition-colors" 
                                   aria-label="Requires First Checker">
                            <span class="ml-2 text-sm text-gray-700">Requires First Checker</span>
                            <span class="ml-1 text-gray-400 cursor-help" title="Enable if this process needs first level review">ⓘ</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" 
                                   wire:model="requiresSecondChecker" 
                                   class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 shadow-sm transition-colors" 
                                   aria-label="Requires Second Checker">
                            <span class="ml-2 text-sm text-gray-700">Requires Second Checker</span>
                            <span class="ml-1 text-gray-400 cursor-help" title="Enable if this process needs second level review">ⓘ</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" 
                                   wire:model="requiresApprover" 
                                   class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 shadow-sm transition-colors" 
                                   aria-label="Requires Approver">
                            <span class="ml-2 text-sm text-gray-700">Requires Approver</span>
                            <span class="ml-1 text-gray-400 cursor-help" title="Enable if this process needs final approval">ⓘ</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" 
                                   wire:model="isActive" 
                                   class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 shadow-sm transition-colors" 
                                   aria-label="Active">
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                            <span class="ml-1 text-gray-400 cursor-help" title="Enable to make this process available for use">ⓘ</span>
                        </label>
                    </div>
                    
                    @if($requiresFirstChecker)
                        <div>
                            <label for="firstCheckerRoles" class="block text-sm font-medium text-gray-700 mb-1">
                                First Checker Roles <span class="text-red-500">*</span>
                                <span class="ml-1 text-gray-400 cursor-help" title="Select roles that can perform first level review">ⓘ</span>
                            </label>
                            <select id="firstCheckerRoles" 
                                    wire:model.defer="firstCheckerRoles" 
                                    multiple 
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 py-2.5 px-3.5 transition-colors text-gray-900" 
                                    aria-describedby="firstCheckerRoles-error firstCheckerRoles-help">
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <p id="firstCheckerRoles-help" class="mt-1 text-sm text-gray-500">Hold Ctrl/Cmd to select multiple roles</p>
                            @error('firstCheckerRoles') 
                                <span id="firstCheckerRoles-error" class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                            @enderror
                        </div>
                    @endif
                    
                    @if($requiresSecondChecker)
                        <div>
                            <label for="secondCheckerRoles" class="block text-sm font-medium text-gray-700 mb-1">
                                Second Checker Roles <span class="text-red-500">*</span>
                                <span class="ml-1 text-gray-400 cursor-help" title="Select roles that can perform second level review">ⓘ</span>
                            </label>
                            <select id="secondCheckerRoles" 
                                    wire:model.defer="secondCheckerRoles" 
                                    multiple 
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 py-2.5 px-3.5 transition-colors text-gray-900" 
                                    aria-describedby="secondCheckerRoles-error secondCheckerRoles-help">
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <p id="secondCheckerRoles-help" class="mt-1 text-sm text-gray-500">Hold Ctrl/Cmd to select multiple roles</p>
                            @error('secondCheckerRoles') 
                                <span id="secondCheckerRoles-error" class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                            @enderror
                        </div>
                    @endif
                    
                    @if($requiresApprover)
                        <div>
                            <label for="approverRoles" class="block text-sm font-medium text-gray-700 mb-1">
                                Approver Roles <span class="text-red-500">*</span>
                                <span class="ml-1 text-gray-400 cursor-help" title="Select roles that can give final approval">ⓘ</span>
                            </label>
                            <select id="approverRoles" 
                                    wire:model.defer="approverRoles" 
                                    multiple 
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 py-2.5 px-3.5 transition-colors text-gray-900" 
                                    aria-describedby="approverRoles-error approverRoles-help">
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <p id="approverRoles-help" class="mt-1 text-sm text-gray-500">Hold Ctrl/Cmd to select multiple roles</p>
                            @error('approverRoles') 
                                <span id="approverRoles-error" class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                            @enderror
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <button type="button" 
                        wire:click="$set('showForm', false)" 
                        class="px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors font-medium text-sm">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2.5 bg-blue-900 text-white rounded-lg hover:bg-blue-800 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors font-medium text-sm">
                    {{ $editingId ? 'Update Configuration' : 'Create Configuration' }}
                </button>
            </div>
        </form>
    </div>
</div> 