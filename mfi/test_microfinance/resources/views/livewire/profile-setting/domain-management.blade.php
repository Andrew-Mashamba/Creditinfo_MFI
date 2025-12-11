<div class="space-y-6">
    {{-- Header Section --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Domain Management</h2>
                <p class="text-gray-600 mt-1">Manage your domain registrations and renewals</p>
            </div>
            <button wire:click="$set('showRegistrationForm', true); clearRegistrationResult()" 
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Register New Domain
            </button>
        </div>
    </div>

    {{-- Domain Search --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Check Domain Availability</h3>
        <div class="flex gap-4">
            <input type="text" wire:model="searchDomain" 
                   placeholder="Enter domain name (e.g., example.com)"
                   class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-900 focus:border-transparent">
            <button wire:click="checkDomainAvailability" 
                    wire:loading.attr="disabled"
                    class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                <span wire:loading.remove wire:target="checkDomainAvailability">Check Availability</span>
                <span wire:loading wire:target="checkDomainAvailability">Checking...</span>
            </button>
        </div>
        
        @if($availabilityResult)
        <div class="mt-4 p-4 rounded-lg {{ $availabilityResult['available'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    @if($availabilityResult['available'])
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-green-800 font-medium">Domain is available!</span>
                    @else
                        <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span class="text-red-800 font-medium">Domain is not available</span>
                    @endif
                </div>
                @if(isset($availabilityResult['code']))
                <span class="text-xs text-gray-500">Code: {{ $availabilityResult['code'] }}</span>
                @endif
            </div>
            @if(isset($availabilityResult['message']))
            <p class="text-sm text-gray-600 mt-1">{{ $availabilityResult['message'] }}</p>
            @endif
            @if($availabilityResult['available'])
            <div class="mt-2 flex items-center justify-between">
                <p class="text-green-700">Domain is available for registration</p>
                <button wire:click="fillFromAvailabilityCheck" 
                        class="text-green-600 hover:text-green-800 text-sm underline">
                    Use this domain for registration
                </button>
            </div>
            @endif
        </div>
        @endif
    </div>

    {{-- Domain Registration Form --}}
    @if($showRegistrationForm)
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold">Register New Domain</h3>
            <button wire:click="$set('showRegistrationForm', false); clearRegistrationResult()"
                    class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <form wire:submit.prevent="registerDomain">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Domain Name</label>
                    <input type="text" wire:model="domainName" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-900 focus:border-transparent">
                    @error('domainName') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Registrant Name</label>
                    <input type="text" wire:model="registrantName" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-900 focus:border-transparent">
                    @error('registrantName') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Organization (Optional)</label>
                    <input type="text" wire:model="registrantOrganization" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-900 focus:border-transparent">
                    @error('registrantOrganization') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" wire:model="registrantEmail" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-900 focus:border-transparent">
                    @error('registrantEmail') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" wire:model="registrantPhone" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-900 focus:border-transparent">
                    @error('registrantPhone') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea wire:model="registrantAddress" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-900 focus:border-transparent"></textarea>
                    @error('registrantAddress') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <input type="text" wire:model="city" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-900 focus:border-transparent">
                    @error('city') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                    <select wire:model="country" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-900 focus:border-transparent">
                        <option value="TZ">Tanzania</option>
                        <option value="KE">Kenya</option>
                        <option value="UG">Uganda</option>
                        <option value="RW">Rwanda</option>
                    </select>
                    @error('country') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Registration Period (Years)</label>
                    <select wire:model="registrationPeriod" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-900 focus:border-transparent">
                        <option value="1">1 Year</option>                        
                    </select>
                    @error('registrationPeriod') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div class="md:col-span-2">
                    <h4 class="text-lg font-medium text-gray-900 mb-3">Admin Information (Optional)</h4>
                    <p class="text-sm text-gray-600 mb-4">If not provided, registrant information will be used as admin information.</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Admin Name</label>
                    <input type="text" wire:model="adminName" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-900 focus:border-transparent">
                    @error('adminName') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Admin Email</label>
                    <input type="email" wire:model="adminEmail" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-900 focus:border-transparent">
                    @error('adminEmail') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>
                
<div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Admin Phone</label>
                    <input type="text" wire:model="adminPhone" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-900 focus:border-transparent">
                    @error('adminPhone') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nameservers</label>
                    @foreach($nameservers as $index => $nameserver)
                    <div class="flex gap-2 mb-2">
                        <input type="text" wire:model="nameservers.{{ $index }}" 
                               placeholder="ns{{ $index + 1 }}.example.com"
                               class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-900 focus:border-transparent">
                        @if(count($nameservers) > 2)
                        <button type="button" wire:click="removeNameserver({{ $index }})"
                                class="text-red-500 hover:text-red-700 px-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                        @endif
                    </div>
                    @error('nameservers.'.$index) <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    @endforeach
                    <button type="button" wire:click="addNameserver"
                            class="text-red-900 hover:text-red-800 text-sm">
                        + Add Nameserver
                    </button>
                </div>
            </div>
            
            <div class="flex justify-end gap-4 mt-6">
                <button type="button" wire:click="$set('showRegistrationForm', false); clearRegistrationResult()"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                    Cancel
                </button>
                <button type="submit" 
                        wire:loading.attr="disabled"
                        class="bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                    <span wire:loading.remove wire:target="registerDomain">Register Domain</span>
                    <span wire:loading wire:target="registerDomain">Registering...</span>
                </button>
            </div>
        </form>
        
        {{-- Registration Result Display --}}
        @if($registrationResult)
        <div class="mt-6 p-4 rounded-lg {{ $registrationResult['success'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    @if($registrationResult['success'])
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-green-800 font-medium">Domain registered successfully!</span>
                    @else
                        <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span class="text-red-800 font-medium">Registration failed</span>
                    @endif
                </div>
                @if(isset($registrationResult['code']))
                <span class="text-xs text-gray-500">Code: {{ $registrationResult['code'] }}</span>
                @endif
            </div>
            @if(isset($registrationResult['message']))
            <p class="text-sm text-gray-600 mt-1">{{ $registrationResult['message'] }}</p>
            @endif
            @if($registrationResult['success'])
            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-600">Domain:</span>
                    <span class="font-medium text-gray-900">{{ $registrationResult['domain'] }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Registration Date:</span>
                    <span class="font-medium text-gray-900">{{ $registrationResult['registrationDate'] }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Expiry Date:</span>
                    <span class="font-medium text-gray-900">{{ $registrationResult['expiryDate'] }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Amount:</span>
                    <span class="font-medium text-gray-900">{{ $registrationResult['amount'] }} {{ $registrationResult['currency'] }}</span>
                </div>
                @if(isset($registrationResult['transactionId']))
                <div class="md:col-span-2">
                    <span class="text-gray-600">Transaction ID:</span>
                    <span class="font-medium text-gray-900">{{ $registrationResult['transactionId'] }}</span>
                </div>
                @endif
            </div>
            @endif
        </div>
        @endif
    </div>
    @endif

    {{-- Domains List --}}
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold">Registered Domains ({{ $domains->count() }})</h3>
        </div>
        
        @if($domains->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Domain</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registrant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registration Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($domains as $domain)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $domain->domain_name }}</div>
                            <div class="text-sm text-gray-500">{{ $domain->nameservers_string }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $domain->registrant_name }}</div>
                            @if($domain->registrant_organization)
                                <div class="text-xs text-gray-500">{{ $domain->registrant_organization }}</div>
                            @endif
                            <div class="text-sm text-gray-500">{{ $domain->registrant_email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $domain->admin_name ?: $domain->registrant_name }}</div>
                            <div class="text-sm text-gray-500">{{ $domain->admin_email ?: $domain->registrant_email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                {{ $domain->registration_period }} {{ $domain->registration_period == 1 ? 'Year' : 'Years' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $domain->formatted_registration_date }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $domain->formatted_expiry_date }}</div>
                            @if($domain->isExpiringSoon())
                                <div class="text-xs text-orange-600">Expires in {{ $domain->days_until_expiry }} days</div>
                            @elseif($domain->isExpired())
                                <div class="text-xs text-red-600">Expired</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $domain->status_badge_class }}">
                                {{ ucfirst($domain->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <button wire:click="showRenewalModal({{ $domain->id }})"
                                        class="text-red-900 hover:text-red-900">
                                    Renew
                                </button>
                                <button wire:click="showDomainDetails({{ $domain->id }})"
                                        class="text-gray-600 hover:text-gray-900">
                                    View Details
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No domains registered</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by registering your first domain.</p>
       </div>
        @endif
    </div>

    {{-- Renewal Modal --}}
    @if($showRenewalForm && $selectedDomain)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="cancelRenewal">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" wire:click.stop>
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Renew Domain</h3>
                <div class="mb-4">
                    <p class="text-sm text-gray-600">Domain: <span class="font-medium">{{ $selectedDomain->domain_name }}</span></p>
                    <p class="text-sm text-gray-600">Current expiry: <span class="font-medium">{{ $selectedDomain->formatted_expiry_date }}</span></p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Renewal Period (years)</label>
                    <select wire:model="renewalPeriod" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-900 focus:border-transparent">
                        <option value="1">1 Year</option>
                        <option value="2">2 Years</option>
                        <option value="3">3 Years</option>
                        <option value="5">5 Years</option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button wire:click="cancelRenewal"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                        Cancel
                    </button>
                    <button wire:click="renewDomain({{ $selectedDomain->id }})"
                            wire:loading.attr="disabled"
                            class="bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                        <span wire:loading.remove wire:target="renewDomain">Renew Domain</span>
                        <span wire:loading wire:target="renewDomain">Renewing...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Renewal Result Display --}}
    @if($renewalResult)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="$set('renewalResult', null)">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" wire:click.stop>
            <div class="mt-3">
                <div class="p-4 rounded-lg {{ $renewalResult['success'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            @if($renewalResult['success'])
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-green-800 font-medium">Domain renewed successfully!</span>
                            @else
                                <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span class="text-red-800 font-medium">Renewal failed</span>
                            @endif
                        </div>
                        @if(isset($renewalResult['code']))
                        <span class="text-xs text-gray-500">Code: {{ $renewalResult['code'] }}</span>
                        @endif
                    </div>
                    @if(isset($renewalResult['message']))
                    <p class="text-sm text-gray-600 mt-1">{{ $renewalResult['message'] }}</p>
                    @endif
                    @if($renewalResult['success'])
                    <div class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Domain:</span>
                            <span class="font-medium text-gray-900">{{ $renewalResult['domain'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">New Expiry Date:</span>
                            <span class="font-medium text-gray-900">{{ $renewalResult['expiryDate'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Renewal Period:</span>
                            <span class="font-medium text-gray-900">{{ $renewalResult['renewalPeriod'] }} {{ $renewalResult['renewalPeriod'] == 1 ? 'Year' : 'Years' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Amount:</span>
                            <span class="font-medium text-gray-900">{{ $renewalResult['amount'] }} {{ $renewalResult['currency'] }}</span>
                        </div>
                        @if(isset($renewalResult['transactionId']))
                        <div class="flex justify-between">
                            <span class="text-gray-600">Transaction ID:</span>
                            <span class="font-medium text-gray-900">{{ $renewalResult['transactionId'] }}</span>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
                
                <div class="flex justify-end mt-4">
                    <button wire:click="$set('renewalResult', null)"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Domain Details Modal --}}
    @if($showDomainDetails && $selectedDomain)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeDomainDetails">
        <div class="relative top-10 mx-auto p-5 border w-4/5 max-w-4xl shadow-lg rounded-md bg-white" wire:click.stop>
            <div class="mt-3">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Domain Details</h3>
                    <button wire:click="closeDomainDetails" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Domain Information --}}
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-md font-semibold text-gray-900 mb-3">Domain Information</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Domain Name:</span>
                                <div class="flex items-center">
                                    <span class="text-sm font-medium text-gray-900 mr-2">{{ $selectedDomain->domain_name }}</span>
                                    <button onclick="copyToClipboard('{{ $selectedDomain->domain_name }}')" 
                                            class="text-gray-400 hover:text-gray-600" title="Copy domain name">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Status:</span>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $selectedDomain->status_badge_class }}">
                                    {{ ucfirst($selectedDomain->status) }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Registration Period:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $selectedDomain->registration_period }} {{ $selectedDomain->registration_period == 1 ? 'Year' : 'Years' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Registration Date:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $selectedDomain->formatted_registration_date }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Expiry Date:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $selectedDomain->formatted_expiry_date }}</span>
                            </div>
                            @if($selectedDomain->isExpiringSoon())
                                <div class="flex justify-between">
                                    <span class="text-sm text-orange-600">Days Until Expiry:</span>
                                    <span class="text-sm font-medium text-orange-600">{{ $selectedDomain->days_until_expiry }} days</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Registrant Information --}}
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-md font-semibold text-gray-900 mb-3">Registrant Information</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Name:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $selectedDomain->registrant_name }}</span>
                            </div>
                            @if($selectedDomain->registrant_organization)
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Organization:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $selectedDomain->registrant_organization }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Email:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $selectedDomain->registrant_email }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Phone:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $selectedDomain->registrant_phone }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Address:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $selectedDomain->registrant_address }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">City:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $selectedDomain->city }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Country:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $selectedDomain->country }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Admin Information --}}
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-md font-semibold text-gray-900 mb-3">Admin Information</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Name:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $selectedDomain->admin_name ?: $selectedDomain->registrant_name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Email:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $selectedDomain->admin_email ?: $selectedDomain->registrant_email }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Phone:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $selectedDomain->admin_phone ?: $selectedDomain->registrant_phone }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Technical Information --}}
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-md font-semibold text-gray-900 mb-3">Technical Information</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Nameservers:</span>
                                <div class="text-sm font-medium text-gray-900 text-right">
                                    @if(is_array($selectedDomain->nameservers))
                                        @foreach($selectedDomain->nameservers as $nameserver)
                                            <div>{{ $nameserver }}</div>
                                        @endforeach
                                    @else
                                        <div>{{ $selectedDomain->nameservers }}</div>
                                    @endif
                                </div>
                            </div>
                            @if($selectedDomain->transaction_id)
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Transaction ID:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $selectedDomain->transaction_id }}</span>
                            </div>
                            @endif
                            @if($selectedDomain->amount)
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Amount:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $selectedDomain->formatted_amount }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button wire:click="closeDomainDetails"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                        Close
                    </button>
                    <button wire:click="showRenewalModal({{ $selectedDomain->id }})"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                        Renew Domain
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- JavaScript for notifications and utilities --}}
<script nonce="{{ csp_nonce() }}">
    // Wait for both DOM and Livewire to be ready
    document.addEventListener('DOMContentLoaded', function () {
        // Listen for Livewire events
        document.addEventListener('livewire:load', function () {
            console.log('Livewire loaded, setting up event listeners');
            
            Livewire.on('domain-checked', event => {
                console.log('Domain checked event received:', event);
                // This will be handled by the availability result display above
            });
            
            Livewire.on('domain-registered', event => {
                console.log('Domain registered event received:', event);
                // This will be handled by the registration result display above
            });
            
            Livewire.on('domain-renewed', event => {
                console.log('Domain renewed event received:', event);
                // This will be handled by the renewal result display above
            });
            
            Livewire.on('success', event => {
                console.log('Success event received:', event);
                // Show success notification
                if (typeof toastr !== 'undefined') {
                    toastr.success(event.message);
                } else {
                    alert(event.message);
                }
            });
            
            Livewire.on('error', event => {
                console.log('Error event received:', event);
                // Show error notification
                if (typeof toastr !== 'undefined') {
                    toastr.error(event.message);
                } else {
                    alert('Error: ' + event.message);
                }
            });
        });
        
        // Also listen for Livewire updates (for newer versions)
        document.addEventListener('livewire:update', function () {
            console.log('Livewire updated');
        });
    });

    // Copy to clipboard functionality
    function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            // Use the modern clipboard API
            navigator.clipboard.writeText(text).then(function() {
                showCopyNotification('Copied to clipboard!');
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
                fallbackCopyTextToClipboard(text);
            });
        } else {
            // Fallback for older browsers
            fallbackCopyTextToClipboard(text);
        }
    }

    function fallbackCopyTextToClipboard(text) {
        var textArea = document.createElement("textarea");
        textArea.value = text;
        
        // Avoid scrolling to bottom
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";
        
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            var successful = document.execCommand('copy');
            if (successful) {
                showCopyNotification('Copied to clipboard!');
            } else {
                showCopyNotification('Failed to copy');
            }
        } catch (err) {
            console.error('Fallback: Oops, unable to copy', err);
            showCopyNotification('Failed to copy');
        }
        
        document.body.removeChild(textArea);
    }

    function showCopyNotification(message) {
        // Create a temporary notification
        var notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-opacity duration-300';
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Remove after 2 seconds
        setTimeout(function() {
            notification.style.opacity = '0';
            setTimeout(function() {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 2000);
    }
</script>
