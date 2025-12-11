@if($viewBranchDetails)
<div class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-6 w-3/4 md:w-3/4 lg:w-2/3 bg-white rounded-lg shadow-2xl">
        
        <!-- Header -->
        <div class="flex justify-between items-center pb-4 border-b border-gray-200">
            <div>
                <h3 class="text-2xl font-bold text-gray-900">Branch Details</h3>
                <p class="text-sm text-gray-500">Branch #{{ $branch_number }}</p>
            </div>
            <button wire:click="$set('viewBranchDetails', false)"
                class="text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 rounded-full p-1">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">

            <!-- Left Column -->
            <div class="space-y-6">

                <!-- Basic Info -->
                <div class="bg-gray-50 p-5 rounded-lg shadow-sm">
                    <h4 class="text-lg font-semibold text-blue-900 flex items-center mb-4">
                        <i class="fas fa-info-circle mr-2"></i> Basic Information
                    </h4>
                    <dl class="grid grid-cols-1 gap-y-3 text-sm text-gray-700">
                        <div>
                            <dt class="font-medium text-gray-500">Branch Name</dt>
                            <dd class="text-base text-gray-900">{{ $name }}</dd>
                        </div>

                        <div>
                            <dt class="font-medium text-gray-500">Branch Type</dt>
                            <dd>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($branch_type == 'MAIN') bg-blue-100 text-blue-800
                                    @elseif($branch_type == 'SUB') bg-green-100 text-green-800
                                    @else bg-purple-100 text-purple-800
                                    @endif">
                                    {{ $branch_type }}
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt class="font-medium text-gray-500">Region</dt>
                            <dd class="text-base text-gray-900">{{ $region }}</dd>
                        </div>

                        <div>
                            <dt class="font-medium text-gray-500">Wilaya</dt>
                            <dd class="text-base text-gray-900">{{ $wilaya }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Contact Info -->
                <div class="bg-gray-50 p-5 rounded-lg shadow-sm">
                    <h4 class="text-lg font-semibold text-blue-900 flex items-center mb-4">
                        <i class="fas fa-envelope mr-2"></i> Contact Information
                    </h4>
                    <dl class="grid grid-cols-1 gap-y-3 text-sm text-gray-700">
                        <div>
                            <dt class="font-medium text-gray-500">Email Address</dt>
                            <dd class="text-base text-gray-900">{{ $email }}</dd>
                        </div>

                        <div>
                            <dt class="font-medium text-gray-500">Phone Number</dt>
                            <dd class="text-base text-gray-900">{{ $phone_number }}</dd>
                        </div>

                        <div>
                            <dt class="font-medium text-gray-500">Physical Address</dt>
                            <dd class="text-base text-gray-900">{{ $address }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">

                <!-- Branch Management -->
                <div class="bg-gray-50 p-5 rounded-lg shadow-sm">
                    <h4 class="text-lg font-semibold text-blue-900 flex items-center mb-4">
                        <i class="fas fa-user-tie mr-2"></i> Branch Management
                    </h4>
                    <dl class="grid grid-cols-1 gap-y-3 text-sm text-gray-700">
                        <div>
                            <dt class="font-medium text-gray-500">Branch Manager</dt>
                            <dd class="text-base text-gray-900">{{ $branch_manager }}</dd>
                        </div>

                        <div>
                            <dt class="font-medium text-gray-500">Opening Date</dt>
                            <dd class="text-base text-gray-900">{{ \Carbon\Carbon::parse($opening_date)->format('F d, Y') }}</dd>
                        </div>

                        <div>
                            <dt class="font-medium text-gray-500">Operating Hours</dt>
                            <dd class="text-base text-gray-900">
                                @if($opening_time && $closing_time)
                                    {{ $opening_time }} <span class="text-gray-400 mx-1">to</span> {{ $closing_time }}
                                @else
                                    <span class="text-gray-500 italic">Not specified</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Services -->
                <div class="bg-gray-50 p-5 rounded-lg shadow-sm">
                    <h4 class="text-lg font-semibold text-blue-900 flex items-center mb-4">
                        <i class="fas fa-concierge-bell mr-2"></i> Services Offered
                    </h4>
                    <div class="flex flex-wrap gap-2 text-sm">
                        @if($services_offered && is_array($services_offered) && count($services_offered) > 0)
                            @foreach($services_offered as $service)
                                <span class="inline-flex items-center px-3 py-1 rounded-full font-medium 
                                    @if($service == 'SAVINGS') bg-green-100 text-green-800
                                    @elseif($service == 'LOANS') bg-blue-100 text-blue-800
                                    @elseif($service == 'INSURANCE') bg-purple-100 text-purple-800
                                    @else bg-indigo-100 text-indigo-800
                                    @endif">
                                    <i class="fas 
                                        @if($service == 'SAVINGS') fa-piggy-bank
                                        @elseif($service == 'LOANS') fa-hand-holding-usd
                                        @elseif($service == 'INSURANCE') fa-shield-alt
                                        @else fa-chart-line
                                        @endif mr-2"></i>
                                    {{ ucfirst(strtolower($service)) }}
                                </span>
                            @endforeach
                        @else
                            <p class="text-gray-500 italic">No services recorded.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 flex justify-end space-x-3 border-t pt-4">
            <button wire:click="$set('viewBranchDetails', false)"
                class="py-2 px-4 border border-gray-300 text-sm rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Close
            </button>
            @if($canEdit)
            <button wire:click="editBranchModal({{ $branch }})"
                class="py-2 px-4 text-sm rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                Edit Branch
            </button>
            @endif
        </div>
    </div>
</div>
@endif
