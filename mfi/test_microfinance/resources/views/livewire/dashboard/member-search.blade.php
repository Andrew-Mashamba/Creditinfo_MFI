<div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
    <!-- Flash Messages -->
    @if(session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex items-center space-x-3 mb-4">
            <div class="p-3 bg-red-100 rounded-xl">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Member Search</h2>
                <p class="text-gray-600">Search and view member information</p>
            </div>
        </div>
    </div>

    <!-- Search Section -->
    <div class="mb-8">
        <div class="bg-gray-50 rounded-xl p-6">
            <div class="w-full flex gap-4">
 
                <!-- Search by Member Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Member Number</label>
                    <div class="relative">
                        <input wire:model.live.debounce.500ms="searchMemberNumber" 
                               type="text" 
                               placeholder="Enter Member Number"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>


                <!-- Search by Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Member Name</label>
                    <div class="relative">
                        <input wire:model.live.debounce.500ms="searchName" 
                               type="text" 
                               placeholder="Enter member name"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Search by Phone -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                    <div class="relative">
                        <input wire:model.live.debounce.500ms="searchPhone" 
                               type="text" 
                               placeholder="Enter phone number"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advanced Search Options -->
            <div class="mt-4 pt-4 border-t border-gray-200">
                <button wire:click="$toggle('showAdvancedSearch')" 
                        class="flex items-center text-sm text-red-600 hover:text-red-800 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    Advanced Search Options
                </button>

                @if($showAdvancedSearch)
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Membership Type</label>
                            <select wire:model.live="searchMembershipType" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">All Types</option>
                                <option value="Individual">Individual</option>
                                <option value="Business">Business</option>
                                <option value="Group">Group</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select wire:model.live="searchStatus" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">All Status</option>
                                <option value="ACTIVE">Active</option>
                                <option value="NEW CLIENT">New Client</option>
                                <option value="PENDING">Pending</option>
                                <option value="BLOCKED">Blocked</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                            <select wire:model.live="searchBranch" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">All Branches</option>
                                @foreach(\App\Models\BranchesModel::all() as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                            <select wire:model.live="searchGender" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">All Genders</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Clear Search Button -->
            @if($hasActiveSearch)
                <div class="mt-4 flex justify-end">
                    <button wire:click="clearSearch" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Clear Search
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Loading State -->
    @if($loading)
        <div class="flex items-center justify-center py-12">
            <div class="flex items-center space-x-3">
                <div class="w-6 h-6 border-2 border-red-600 border-t-transparent rounded-full animate-spin"></div>
                <span class="text-gray-600">Searching members...</span>
            </div>
        </div>
    @endif

    <!-- Search Results -->
    @if(!$loading && $members->count() > 0)
        <div class="space-y-6">
            <!-- Results Summary -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600">Found</span>
                    <span class="px-2 py-1 text-sm font-medium bg-red-100 text-red-800 rounded-full">{{ $members->count() }}</span>
                    <span class="text-sm text-gray-600">member(s)</span>
                </div>
                
                <div class="flex items-center space-x-2">
                    <button wire:click="exportResults" 
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-500 transition-colors">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export Results
                    </button>
                </div>
            </div>

            <!-- Members Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($members as $member)
                    <div class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                        <!-- Member Header -->
                        <div class="p-6 border-b border-gray-100">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                        @if($member->profile_photo_path)
                                            <img src="{{ asset('storage/' . $member->profile_photo_path) }}" 
                                                 alt="Profile" 
                                                 class="w-12 h-12 rounded-full object-cover">
                                        @else
                                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            {{ $member->full_name ?? $member->first_name . ' ' . $member->last_name }}
                                        </h3>
                                        <p class="text-sm text-gray-600">{{ $member->membership_type }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                                                    @if($member->client_status === 'ACTIVE')
                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Active</span>
                                @elseif($member->client_status === 'NEW CLIENT')
                                    <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">New</span>
                                @elseif($member->client_status === 'PENDING')
                                    <span class="px-2 py-1 text-xs font-medium bg-orange-100 text-orange-800 rounded-full">Pending</span>
                                @elseif($member->client_status === 'BLOCKED')
                                    <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Blocked</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">{{ $member->client_status }}</span>
                                @endif
                                </div>
                            </div>
                        </div>

                        <!-- Member Details -->
                        <div class="p-6 space-y-4">
                            <!-- Account Information -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Account Information</h4>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div>
                                        <span class="text-gray-500">Client No:</span>
                                        <span class="font-medium text-gray-900">{{ $member->client_number ?? 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Account No:</span>
                                        <span class="font-medium text-gray-900">{{ $member->account_number ?? 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Member ID:</span>
                                        <span class="font-medium text-gray-900">{{ $member->id }}</span>
                                    </div>
                                    @if($member->member_number)
                                        <div>
                                            <span class="text-gray-500">Member No:</span>
                                            <span class="font-medium text-gray-900">{{ $member->member_number }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Contact Information</h4>
                                <div class="space-y-1 text-sm">
                                    @if($member->mobile_phone_number || $member->phone_number)
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                            <span class="text-gray-900">{{ $member->mobile_phone_number ?? $member->phone_number ?? 'N/A' }}</span>
                                        </div>
                                    @endif
                                    @if($member->email)
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            <span class="text-gray-900">{{ $member->email }}</span>
                                        </div>
                                    @endif
                                    @if($member->address)
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span class="text-gray-900">{{ $member->address }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Personal Information -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Personal Information</h4>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    @if($member->gender)
                                        <div>
                                            <span class="text-gray-500">Gender:</span>
                                            <span class="font-medium text-gray-900">{{ ucfirst($member->gender) }}</span>
                                        </div>
                                    @endif
                                    @if($member->date_of_birth)
                                        <div>
                                            <span class="text-gray-500">DOB:</span>
                                            <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($member->date_of_birth)->format('M d, Y') }}</span>
                                        </div>
                                    @endif
                                    @if($member->marital_status)
                                        <div>
                                            <span class="text-gray-500">Status:</span>
                                            <span class="font-medium text-gray-900">{{ ucfirst($member->marital_status) }}</span>
                                        </div>
                                    @endif
                                    @if($member->occupation)
                                        <div>
                                            <span class="text-gray-500">Occupation:</span>
                                            <span class="font-medium text-gray-900">{{ $member->occupation }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Branch Information -->
                            @if($member->branch)
                                <div class="space-y-2">
                                    <h4 class="text-sm font-medium text-gray-700">Branch Information</h4>
                                    <div class="text-sm">
                                        <span class="text-gray-500">Branch:</span>
                                        <span class="font-medium text-gray-900">{{ $member->branch->name ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            @elseif($member->branch_id)
                                <div class="space-y-2">
                                    <h4 class="text-sm font-medium text-gray-700">Branch Information</h4>
                                    <div class="text-sm">
                                        <span class="text-gray-500">Branch ID:</span>
                                        <span class="font-medium text-gray-900">{{ $member->branch_id }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="p-6 border-t border-gray-100 bg-gray-50 rounded-b-xl">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <button wire:click="viewMemberDetails({{ $member->id }})" 
                                            class="px-3 py-1.5 text-sm font-medium text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md transition-colors">
                                        <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        View Details
                                    </button>
                          
                                </div>
                                <div class="text-xs text-gray-500">
                                    Joined {{ \Carbon\Carbon::parse($member->created_at)->format('M Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($members->hasPages())
                <div class="mt-8">
                    {{ $members->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- No Results -->
    @if(!$loading && $hasActiveSearch && $members->count() === 0)
        <div class="text-center py-12">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No members found</h3>
            <p class="text-gray-600 mb-4">Try adjusting your search criteria</p>
            <button wire:click="clearSearch" 
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-500 transition-colors">
                Clear Search
            </button>
        </div>
    @endif

    <!-- Initial State -->
    @if(!$loading && !$hasActiveSearch)
        <div class="text-center py-12">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Search for Members</h3>
            <p class="text-gray-600 mb-4">Enter search criteria above to find specific members</p>
            <div class="flex items-center justify-center space-x-4 text-sm text-gray-500">
                <div class="flex items-center space-x-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Search by Client Number, Member Number, or Name</span>
                </div>
            </div>
        </div>
    @endif

    <!-- Member Details Modal -->
    @if($showMemberModal && $selectedMember)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-hidden">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                            @if($selectedMember->profile_photo_path)
                                <img src="{{ asset('storage/' . $selectedMember->profile_photo_path) }}" 
                                     alt="Profile" 
                                     class="w-12 h-12 rounded-full object-cover">
                            @else
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            @endif
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">
                                {{ $selectedMember->full_name ?? $selectedMember->first_name . ' ' . $selectedMember->last_name }}
                            </h2>
                            <p class="text-sm text-gray-600">{{ $selectedMember->membership_type }}</p>
                        </div>
                    </div>
                    <button wire:click="closeMemberModal" 
                            class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Content -->
                <div class="overflow-y-auto max-h-[calc(90vh-120px)] p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <!-- Personal Information -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Personal Information
                                </h3>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-500">Client Number:</span>
                                        <span class="font-medium text-gray-900 block">{{ $selectedMember->client_number ?? 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Member Number:</span>
                                        <span class="font-medium text-gray-900 block">{{ $selectedMember->member_number ?? 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Account Number:</span>
                                        <span class="font-medium text-gray-900 block">{{ $selectedMember->account_number ?? 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Status:</span>
                                        <span class="font-medium text-gray-900 block">
                                            @if($selectedMember->client_status === 'ACTIVE')
                                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Active</span>
                                            @elseif($selectedMember->client_status === 'NEW CLIENT')
                                                <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">New</span>
                                            @elseif($selectedMember->client_status === 'PENDING')
                                                <span class="px-2 py-1 text-xs font-medium bg-orange-100 text-orange-800 rounded-full">Pending</span>
                                            @elseif($selectedMember->client_status === 'BLOCKED')
                                                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Blocked</span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">{{ $selectedMember->client_status }}</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Gender:</span>
                                        <span class="font-medium text-gray-900 block">{{ ucfirst($selectedMember->gender ?? 'N/A') }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Date of Birth:</span>
                                        <span class="font-medium text-gray-900 block">{{ $selectedMember->date_of_birth ? \Carbon\Carbon::parse($selectedMember->date_of_birth)->format('M d, Y') : 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Marital Status:</span>
                                        <span class="font-medium text-gray-900 block">{{ ucfirst($selectedMember->marital_status ?? 'N/A') }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Occupation:</span>
                                        <span class="font-medium text-gray-900 block">{{ $selectedMember->occupation ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    Contact Information
                                </h3>
                                <div class="space-y-3 text-sm">
                                    @if($selectedMember->mobile_phone_number || $selectedMember->phone_number)
                                        <div class="flex items-center space-x-3">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                            <span class="text-gray-900">{{ $selectedMember->mobile_phone_number ?? $selectedMember->phone_number ?? 'N/A' }}</span>
                                        </div>
                                    @endif
                                    @if($selectedMember->email)
                                        <div class="flex items-center space-x-3">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            <span class="text-gray-900">{{ $selectedMember->email }}</span>
                                        </div>
                                    @endif
                                    @if($selectedMember->address)
                                        <div class="flex items-start space-x-3">
                                            <svg class="w-4 h-4 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span class="text-gray-900">{{ $selectedMember->address }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Branch Information -->
                            @if($selectedMember->branch)
                                <div class="bg-gray-50 rounded-xl p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        Branch Information
                                    </h3>
                                    <div class="text-sm">
                                        <span class="text-gray-500">Branch:</span>
                                        <span class="font-medium text-gray-900 block">{{ $selectedMember->branch->name ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">
                            <!-- Accounts -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    Accounts ({{ $memberAccounts->count() }})
                                </h3>
                                @if($memberAccounts->count() > 0)
                                    <div class="space-y-3">
                                        @foreach($memberAccounts as $account)
                                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <h4 class="font-medium text-gray-900">{{ $account->account_name ?? $account->account_type ?? 'Account' }}</h4>
                                                        <p class="text-sm text-gray-600">{{ $account->account_number }}</p>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="text-lg font-bold text-green-600">
                                                            {{ number_format($account->balance ?? 0, 2) }}
                                                        </p>
                                                        <p class="text-xs text-gray-500">Balance</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-500 text-sm">No accounts found</p>
                                @endif
                            </div>

                            <!-- Loans -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                    </svg>
                                    Loans ({{ $memberLoans->count() }})
                                </h3>
                                @if($memberLoans->count() > 0)
                                    <div class="space-y-3">
                                        @foreach($memberLoans as $loan)
                                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                <div class="flex items-center justify-between mb-3">
                                                    <div>
                                                        <h4 class="font-medium text-gray-900">{{ $loan->loan_product_name ?? $loan->product_name ?? 'Loan Product' }}</h4>
                                                        <p class="text-sm text-gray-600">{{ $loan->loan_number ?? $loan->id ?? 'N/A' }}</p>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="text-lg font-bold text-red-600">
                                                            {{ number_format($loan->principal_amount ?? $loan->amount ?? 0, 2) }}
                                                        </p>
                                                        <p class="text-xs text-gray-500">Principal</p>
                                                    </div>
                                                </div>
                                                <button wire:click="downloadRepaymentSchedule({{ $loan->id }})" 
                                                        class="w-full px-3 py-2 text-sm font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                                                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    Download Repayment Schedule
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-500 text-sm">No loans found</p>
                                @endif
                            </div>

                            <!-- Pending Bills -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Pending Bills ({{ $memberBills->count() }})
                                </h3>
                                @if($memberBills->count() > 0)
                                    <div class="space-y-3">
                                        @foreach($memberBills as $bill)
                                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <h4 class="font-medium text-gray-900">Control Number: {{ $bill->control_number ?? 'N/A' }}</h4>
                                                        <p class="text-sm text-gray-600">{{ $bill->description ?? 'Bill Description' }}</p>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="text-lg font-bold text-red-600">
                                                            {{ number_format($bill->amount ?? 0, 2) }}
                                                        </p>
                                                        <p class="text-xs text-gray-500">Amount</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-500 text-sm">No pending bills found</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
