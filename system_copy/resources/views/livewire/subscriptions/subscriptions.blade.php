<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Flash Message -->
    @if (session()->has('message'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    @if(!($permissions['canView'] ?? false) && !($permissions['canManage'] ?? false) && !($permissions['canExport'] ?? false))
    {{-- No Access Message for users with no permissions --}}
    <div class="bg-white shadow rounded-lg p-8 text-center">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Access</h3>
        <p class="text-gray-500">You don't have permission to access the service subscriptions module.</p>
    </div>
    @else
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Service Subscriptions</h1>
                <p class="text-gray-600 mt-1">Manage your SACCOS service subscriptions and billing</p>
            </div>
      
        </div>
    </div>

    <!-- Navigation Tabs -->
    @if(($permissions['canView'] ?? false) || ($permissions['canManage'] ?? false) || ($permissions['canExport'] ?? false))
    <div class="mb-6">
        <nav class="flex space-x-8 border-b border-gray-200">
            @if($permissions['canView'] ?? false)
            <button wire:click="setActiveTab('overview')" 
                    class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200
                    {{ $activeTab === 'overview' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Services
            </button>
            @endif
            @if($permissions['canView'] ?? false)
            <button wire:click="setActiveTab('usage')" 
                    class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200
                    {{ $activeTab === 'usage' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Usage
            </button>
            @endif
        </nav>
    </div>
    @endif

    <!-- Content Area -->
    <div>
        @if($activeTab === 'overview' && ($permissions['canView'] ?? false))
            <!-- Services Overview -->
            <div class="space-y-4">
                @foreach($services as $service)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $service['name'] }}</h3>
                                @if($service['type'] === 'mandatory')
                                    <span class="ml-2 px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Mandatory</span>
                                @else
                                    <span class="ml-2 px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">Optional</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600 mt-1">{{ $service['description'] }}</p>
                            
                            <!-- Features -->
                            <div class="mt-3">
                                <p class="text-sm font-medium text-gray-700 mb-2">Features:</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($service['features'] as $feature)
                                    <span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded">{{ $feature }}</span>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Usage (if active) -->
                            @if($service['status'] === 'active' && $service['usage'])
                                <div class="mt-4">
                                    @if(isset($service['usage']['percentage']))
                                    <div class="flex items-center justify-between text-sm text-gray-600 mb-1">
                                        <span>Usage this month</span>
                                        <span>{{ $service['usage']['sent'] ?? $service['usage']['generated'] ?? $service['usage']['created'] ?? 0 }} / {{ $service['usage']['limit'] }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-900 h-2 rounded-full" style="width: {{ $service['usage']['percentage'] }}%"></div>
                                    </div>
                                    
                                    <!-- Enhanced SMS Service Statistics -->
                                    @if($service['id'] == 1 && isset($service['usage']['total']))
                                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                                        <div class="bg-green-50 p-2 rounded">
                                            <p class="text-green-700 font-medium">Delivered</p>
                                            <p class="text-green-900 font-bold">{{ number_format($service['usage']['sent']) }}</p>
                                        </div>
                                        <div class="bg-red-50 p-2 rounded">
                                            <p class="text-red-700 font-medium">Failed</p>
                                            <p class="text-red-900 font-bold">{{ number_format($service['usage']['failed']) }}</p>
                                        </div>
                                        <div class="bg-blue-50 p-2 rounded">
                                            <p class="text-blue-700 font-medium">Total</p>
                                            <p class="text-blue-900 font-bold">{{ number_format($service['usage']['total']) }}</p>
                                        </div>
                                        <div class="bg-purple-50 p-2 rounded">
                                            <p class="text-purple-700 font-medium">Success Rate</p>
                                            <p class="text-purple-900 font-bold">{{ $service['usage']['success_rate'] }}%</p>
                                        </div>
                                    </div>
                                    @endif
                                    
                                    @elseif(isset($service['usage']['downloads']))
                                    <div class="text-sm text-gray-600">
                                        <p>Downloads: {{ $service['usage']['downloads'] }}</p>
                                        <p>Active Users: {{ $service['usage']['active_users'] }}</p>
                                    </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="ml-6 text-right">
                            <p class="text-2xl font-bold text-gray-900">TSH {{ number_format($service['price']) }}</p>
                            <p class="text-sm text-gray-600">{{ $service['billing_cycle'] }}</p>
                            
                            @if($service['type'] === 'optional' && ($permissions['canManage'] ?? false))
                                <div class="mt-3 space-y-2">
                                    @if($service['status'] === 'active')
                                        <button wire:click="pauseSubscription({{ $service['id'] }})" 
                                                class="w-full px-3 py-2 text-sm font-medium rounded-lg bg-yellow-100 text-yellow-700 hover:bg-yellow-200 transition-colors">
                                            Pause
                                        </button>
                                        <button wire:click="cancelSubscription({{ $service['id'] }})" 
                                                class="w-full px-3 py-2 text-sm font-medium rounded-lg bg-red-100 text-red-700 hover:bg-red-200 transition-colors">
                                            Cancel
                                        </button>
                                    @elseif($service['status'] === 'paused')
                                        <button wire:click="resumeSubscription({{ $service['id'] }})" 
                                                class="w-full px-3 py-2 text-sm font-medium rounded-lg bg-green-100 text-green-700 hover:bg-green-200 transition-colors">
                                            Resume
                                        </button>
                                        <button wire:click="cancelSubscription({{ $service['id'] }})" 
                                                class="w-full px-3 py-2 text-sm font-medium rounded-lg bg-red-100 text-red-700 hover:bg-red-200 transition-colors">
                                            Cancel
                                        </button>
                                    @elseif($service['status'] === 'cancelled')
                                        <button wire:click="restartSubscription({{ $service['id'] }})" 
                                                class="w-full px-3 py-2 text-sm font-medium rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors">
                                            Restart
                                        </button>
                                    @endif
                                </div>
                            @elseif($service['type'] === 'optional')
                                <div class="mt-3">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium 
                                        {{ $service['status'] === 'active' ? 'bg-green-100 text-green-800' : 
                                           ($service['status'] === 'paused' ? 'bg-yellow-100 text-yellow-800' : 
                                           ($service['status'] === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                        <span class="w-2 h-2 rounded-full mr-2
                                            {{ $service['status'] === 'active' ? 'bg-green-400' : 
                                               ($service['status'] === 'paused' ? 'bg-yellow-400' : 
                                               ($service['status'] === 'cancelled' ? 'bg-red-400' : 'bg-gray-400')) }}"></span>
                                        {{ ucfirst($service['status']) }}
                                    </span>
                                </div>
                            @else
                                <div class="mt-3">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                                        Active
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @elseif($activeTab === 'overview')
            <!-- No Access Message for Services Overview -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Access</h3>
                <p class="text-gray-500">You don't have permission to view service subscriptions.</p>
            </div>
        @elseif($activeTab === 'usage' && ($permissions['canView'] ?? false))
            <!-- Usage Statistics -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Today's Usage Statistics</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-blue-600 font-medium">SMS Delivered</p>
                                <p class="text-2xl font-bold text-blue-900">{{ number_format($usageStats['sms_sent_today']) }}</p>
                                @if(isset($usageStats['sms_total_today']) && $usageStats['sms_total_today'] > 0)
                                <p class="text-xs text-blue-600 mt-1">
                                    {{ $usageStats['sms_success_rate'] }}% success rate
                                </p>
                                @endif
                            </div>
                            <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                        </div>
                    </div>

                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-green-600 font-medium">Emails Delivered</p>
                                <p class="text-2xl font-bold text-green-900">{{ number_format($usageStats['emails_sent_today']) }}</p>
                                @if(isset($usageStats['emails_total_today']) && $usageStats['emails_total_today'] > 0)
                                <p class="text-xs text-green-600 mt-1">
                                    {{ $usageStats['emails_success_rate'] }}% success rate
                                </p>
                                @endif
                            </div>
                            <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>

                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-purple-600 font-medium">Payment Links</p>
                                <p class="text-2xl font-bold text-purple-900">{{ number_format($usageStats['payment_links_today']) }}</p>
                                @if(isset($usageStats['payment_links_used_today']) && $usageStats['payment_links_today'] > 0)
                                <p class="text-xs text-purple-600 mt-1">
                                    {{ $usageStats['payment_links_conversion_rate'] }}% conversion
                                </p>
                                @endif
                            </div>
                            <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                        </div>
                    </div>

                    <div class="bg-orange-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-orange-600 font-medium">Control Numbers</p>
                                <p class="text-2xl font-bold text-orange-900">{{ number_format($usageStats['control_numbers_today']) }}</p>
                                @if(isset($usageStats['control_numbers_paid_today']) && $usageStats['control_numbers_today'] > 0)
                                <p class="text-xs text-orange-600 mt-1">
                                    {{ $usageStats['control_numbers_payment_rate'] }}% paid
                                </p>
                                @endif
                            </div>
                            <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Monthly Trends Chart would go here -->
                <div class="mt-8 bg-gray-50 rounded-lg p-6">
                    <h4 class="text-md font-semibold text-gray-800 mb-4">Monthly Usage Trends</h4>
                    <div class="h-64 flex items-center justify-center text-gray-500">
                        <p>Usage chart visualization would be displayed here</p>
                    </div>
                </div>
            </div>
        @elseif($activeTab === 'usage')
            <!-- No Access Message for Usage Statistics -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Access</h3>
                <p class="text-gray-500">You don't have permission to view usage statistics.</p>
            </div>
        @else
            <!-- No Access Message for any other tab -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Access</h3>
                <p class="text-gray-500">You don't have permission to access this subscriptions section.</p>
            </div>
        @endif
    </div>

    <!-- Service Toggle Modal -->
    @if($showUpgradeModal && $selectedService)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                {{ $selectedService['status'] === 'active' ? 'Deactivate' : 'Activate' }} {{ $selectedService['name'] }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    @if($selectedService['status'] === 'active')
                                        Are you sure you want to deactivate this service? You will lose access to all features and your data will be preserved for 30 days.
                                    @else
                                        Activating this service will add TSH {{ number_format($selectedService['price']) }} to your monthly bill.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="confirmServiceToggle" type="button" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-900 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Confirm
                    </button>
                    <button wire:click="cancelServiceToggle" type="button" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif
</div>
