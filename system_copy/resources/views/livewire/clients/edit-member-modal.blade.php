<div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm p-4" x-data="{ activeTab: 'personal' }">
    <div class="bg-white rounded-2xl w-[95vw] max-w-[1600px] max-h-[95vh] shadow-2xl overflow-hidden flex flex-col">
        <!-- Modal Header -->
        <div class="px-8 py-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 flex-shrink-0">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-blue-900 rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">Edit Member</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ $editingMember['first_name'] ?? '' }} {{ $editingMember['last_name'] ?? '' }} - {{ $editingMember['client_number'] ?? '' }}</p>
                    </div>
                </div>
                <button wire:click="closeEditModal" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="px-8 pt-6 border-b border-gray-200 bg-gray-50 flex-shrink-0 overflow-x-auto">
            <nav class="flex gap-2 min-w-max" role="tablist">
                <button @click="activeTab = 'personal'" :class="activeTab === 'personal' ? 'bg-white text-blue-600 border-blue-600 shadow-sm' : 'bg-transparent text-gray-600 hover:bg-gray-100 border-transparent'"
                    class="px-4 py-3 rounded-t-lg font-medium text-sm border-b-2 transition-all whitespace-nowrap flex items-center gap-2">
                    <i class="fas fa-user"></i> Personal Info
                </button>
                <button @click="activeTab = 'contact'" :class="activeTab === 'contact' ? 'bg-white text-blue-600 border-blue-600 shadow-sm' : 'bg-transparent text-gray-600 hover:bg-gray-100 border-transparent'"
                    class="px-4 py-3 rounded-t-lg font-medium text-sm border-b-2 transition-all whitespace-nowrap flex items-center gap-2">
                    <i class="fas fa-phone"></i> Contact
                </button>
                <button @click="activeTab = 'identification'" :class="activeTab === 'identification' ? 'bg-white text-blue-600 border-blue-600 shadow-sm' : 'bg-transparent text-gray-600 hover:bg-gray-100 border-transparent'"
                    class="px-4 py-3 rounded-t-lg font-medium text-sm border-b-2 transition-all whitespace-nowrap flex items-center gap-2">
                    <i class="fas fa-id-card"></i> Identification
                </button>
                <button @click="activeTab = 'employment'" :class="activeTab === 'employment' ? 'bg-white text-blue-600 border-blue-600 shadow-sm' : 'bg-transparent text-gray-600 hover:bg-gray-100 border-transparent'"
                    class="px-4 py-3 rounded-t-lg font-medium text-sm border-b-2 transition-all whitespace-nowrap flex items-center gap-2">
                    <i class="fas fa-briefcase"></i> Employment & Finance
                </button>
                <button @click="activeTab = 'business'" :class="activeTab === 'business' ? 'bg-white text-blue-600 border-blue-600 shadow-sm' : 'bg-transparent text-gray-600 hover:bg-gray-100 border-transparent'"
                    class="px-4 py-3 rounded-t-lg font-medium text-sm border-b-2 transition-all whitespace-nowrap flex items-center gap-2">
                    <i class="fas fa-building"></i> Business Info
                </button>
                <button @click="activeTab = 'address'" :class="activeTab === 'address' ? 'bg-white text-blue-600 border-blue-600 shadow-sm' : 'bg-transparent text-gray-600 hover:bg-gray-100 border-transparent'"
                    class="px-4 py-3 rounded-t-lg font-medium text-sm border-b-2 transition-all whitespace-nowrap flex items-center gap-2">
                    <i class="fas fa-map-marker-alt"></i> Address
                </button>
                <button @click="activeTab = 'guarantor'" :class="activeTab === 'guarantor' ? 'bg-white text-blue-600 border-blue-600 shadow-sm' : 'bg-transparent text-gray-600 hover:bg-gray-100 border-transparent'"
                    class="px-4 py-3 rounded-t-lg font-medium text-sm border-b-2 transition-all whitespace-nowrap flex items-center gap-2">
                    <i class="fas fa-user-shield"></i> Guarantor
                </button>
                <button @click="activeTab = 'membership'" :class="activeTab === 'membership' ? 'bg-white text-blue-600 border-blue-600 shadow-sm' : 'bg-transparent text-gray-600 hover:bg-gray-100 border-transparent'"
                    class="px-4 py-3 rounded-t-lg font-medium text-sm border-b-2 transition-all whitespace-nowrap flex items-center gap-2">
                    <i class="fas fa-users"></i> Membership
                </button>
                <button @click="activeTab = 'other'" :class="activeTab === 'other' ? 'bg-white text-blue-600 border-blue-600 shadow-sm' : 'bg-transparent text-gray-600 hover:bg-gray-100 border-transparent'"
                    class="px-4 py-3 rounded-t-lg font-medium text-sm border-b-2 transition-all whitespace-nowrap flex items-center gap-2">
                    <i class="fas fa-ellipsis-h"></i> Other
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="flex-1 overflow-y-auto p-8 bg-white">
            <form wire:submit.prevent="saveMember" id="editMemberForm">
                @php
                    $fieldGroups = [
                        'personal' => ['first_name', 'middle_name', 'last_name', 'middle_names', 'present_surname', 'birth_surname', 'full_name', 'gender', 'date_of_birth', 'place_of_birth', 'country_of_birth', 'marital_status', 'number_of_spouse', 'number_of_children', 'dependent_count', 'citizenship', 'nationality', 'nationarity', 'residency', 'religion', 'fate_status', 'social_status', 'classification_of_individual', 'negative_status_of_individual'],
                        'contact' => ['phone_number', 'fixed_line', 'email'],
                        'identification' => ['national_id', 'nida_number', 'tin_number', 'tax_identification_number', 'passport_number', 'passport_issuer_country', 'driving_license_number', 'voters_id', 'custom_id_number_1', 'custom_id_number_2', 'client_id', 'customer_code', 'ref_number', 'employee_id'],
                        'employment' => ['employment', 'employer_name', 'occupation', 'education', 'education_level', 'income_available', 'income_source', 'monthly_expenses', 'annual_income', 'basic_salary', 'gross_salary', 'tax_paid', 'pension', 'nhif', 'workers_union_etc'],
                        'business' => ['membership_type', 'business_name', 'trade_name', 'incorporation_number', 'legal_form', 'establishment_date', 'registration_country', 'registration_number', 'industry_sector'],
                        'address' => ['address', 'street', 'building_number', 'number_of_building', 'postal_code', 'city', 'region', 'district', 'ward', 'country'],
                        'guarantor' => ['guarantor_first_name', 'guarantor_middle_name', 'guarantor_last_name', 'guarantor_full_name', 'guarantor_email', 'guarantor_phone', 'guarantor_relationship', 'guarantor_membership_number', 'guarantor_region', 'guarantor_ward', 'guarantor_district'],
                        'membership' => ['account_number', 'client_number', 'branch_id', 'next_of_kin_name', 'next_of_kin_phone'],
                        'other' => ['notes', 'profile_photo_path']
                    ];
                @endphp

                @foreach($fieldGroups as $tab => $fields)
                    <div x-show="activeTab === '{{ $tab }}'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            @foreach($fields as $field)
                                <div class="{{ in_array($field, ['address', 'notes']) ? 'md:col-span-2 lg:col-span-3 xl:col-span-4' : '' }}">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        @if($field === 'client_number')
                                            Member Number
                                        @elseif($field === 'account_number')
                                            Bank Account Number
                                        @else
                                            {{ ucfirst(str_replace('_', ' ', $field)) }}
                                        @endif
                                    </label>
                                    @if(in_array($field, ['address', 'main_address', 'notes', 'exit_notes']))
                                        <textarea wire:model="editingMember.{{ $field }}" rows="3"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            placeholder="Enter {{ str_replace('_', ' ', $field) }}"></textarea>
                                    @elseif($field === 'gender')
                                        <select wire:model="editingMember.{{ $field }}"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="">Select Gender</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                    @elseif($field === 'marital_status')
                                        <select wire:model="editingMember.{{ $field }}"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="">Select Status</option>
                                            <option value="single">Single</option>
                                            <option value="married">Married</option>
                                            <option value="divorced">Divorced</option>
                                            <option value="widowed">Widowed</option>
                                        </select>
                                    @elseif($field === 'membership_type')
                                        <select wire:model="editingMember.{{ $field }}"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="">Select Type</option>
                                            <option value="Individual">Individual</option>
                                            <option value="Business">Business</option>
                                        </select>
                                    @elseif($field === 'branch_id')
                                        <select wire:model="editingMember.{{ $field }}"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="">Select Branch</option>
                                            @foreach(\App\Models\Branch::all() as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    @elseif($field === 'portal_access_enabled' || $field === 'accept_terms')
                                        <div class="flex items-center h-10">
                                            <input type="checkbox" wire:model="editingMember.{{ $field }}"
                                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                                            <span class="text-sm text-gray-600">{{ $field === 'portal_access_enabled' ? 'Enable portal access' : 'Accept terms and conditions' }}</span>
                                        </div>
                                    @elseif(in_array($field, ['date_of_birth', 'registration_date', 'establishment_date', 'exit_date']))
                                        <input type="date" wire:model="editingMember.{{ $field }}"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    @elseif(in_array($field, ['amount', 'income_available', 'monthly_expenses', 'annual_income', 'basic_salary', 'gross_salary', 'tax_paid', 'pension', 'nhif', 'hisa', 'akiba', 'amana']))
                                        <input type="number" step="0.01" wire:model="editingMember.{{ $field }}"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            placeholder="0.00">
                                    @elseif(in_array($field, ['number_of_spouse', 'number_of_children', 'dependent_count']))
                                        <input type="number" wire:model="editingMember.{{ $field }}"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            placeholder="0">
                                    @elseif($field === 'email')
                                        <input type="email" wire:model="editingMember.{{ $field }}"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50"
                                            readonly
                                            placeholder="email@example.com">
                                    @else
                                        <input type="text" wire:model="editingMember.{{ $field }}"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent {{ in_array($field, ['client_number', 'account_number', 'phone_number']) ? 'bg-gray-50' : '' }}"
                                            {{ in_array($field, ['client_number', 'account_number', 'phone_number']) ? 'readonly' : '' }}
                                            placeholder="{{ in_array($field, ['client_number', 'account_number', 'phone_number']) ? 'Protected field' : 'Enter ' . str_replace('_', ' ', $field) }}">
                                    @endif
                                    @error("editingMember.$field")
                                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="px-8 py-6 border-t border-gray-200 bg-gray-50 flex justify-between items-center flex-shrink-0">
            <div class="text-sm text-gray-600">
                <span class="font-medium">Tip:</span> Use tabs above to navigate different sections
            </div>
            <div class="flex gap-3">
                <button type="button" wire:click="closeEditModal"
                    class="px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" form="editMemberForm"
                    class="px-6 py-3 text-sm font-medium text-white bg-blue-900 rounded-xl hover:bg-blue-700 transition-colors shadow-lg hover:shadow-xl">
                    <i class="fas fa-save mr-2"></i>
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
