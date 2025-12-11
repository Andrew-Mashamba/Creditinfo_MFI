<div>
    {{-- Session Flash Messages --}}
    @if(session()->has('notification'))
        @php
            $notification = session('notification');
            $type = $notification['type'] ?? 'info';
            $message = $notification['message'] ?? '';

            $bgColor = match($type) {
                'success' => 'bg-green-50 border-green-200 text-green-800',
                'error' => 'bg-red-50 border-red-200 text-red-800',
                'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
                'info' => 'bg-red-50 border-red-200 text-red-800',
                default => 'bg-gray-50 border-gray-200 text-gray-800'
            };

            $icon = match($type) {
                'success' => 'fas fa-check-circle',
                'error' => 'fas fa-exclamation-circle',
                'warning' => 'fas fa-exclamation-triangle',
                'info' => 'fas fa-info-circle',
                default => 'fas fa-info-circle'
            };
        @endphp

        <div class="mb-6 p-4 border rounded-xl {{ $bgColor }} flex items-center justify-between shadow-sm">
            <div class="flex items-center">
                <i class="{{ $icon }} mr-3 text-lg"></i>
                <span class="font-medium">{{ $message }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if(session()->has('success'))
        <div class="mb-6 p-4 border border-green-200 rounded-xl bg-green-50 text-green-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-3 text-lg"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-green-400 hover:text-green-600 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="mb-6 p-4 border border-red-200 rounded-xl bg-red-50 text-red-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-3 text-lg"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-xl border border-gray-100">
        <!-- Modern Header Section -->
        <div class="px-8 py-6 border-b border-gray-100 bg-gradient-to-r from-red-50 to-indigo-50">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-red-600 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">All Clients</h2>
                        <p class="text-sm text-gray-600 mt-1">View and manage all client records</p>
                    </div>
                </div>

                <!-- Stats Badge -->
                <div class="flex items-center gap-4">
                    <div class="px-6 py-3 bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-600">Total Clients</span>
                            <span class="px-3 py-1 text-lg font-bold bg-red-100 text-red-700 rounded-lg">
                                {{ number_format($totalRecords) }}
                            </span>
                        </div>
                    </div>

                    <!-- Export Button -->
                    <button wire:click="exportTable"
                        class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 flex items-center gap-2">
                        <i class="fas fa-file-excel"></i>
                        <span>Export to Excel</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Advanced Search Section -->
        <div class="px-8 py-6 bg-gray-50 border-b border-gray-100">
            <div class="flex flex-col gap-4">
                <!-- Main Search Bar -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input
                        wire:model.live.debounce.500ms="search"
                        type="text"
                        placeholder="Search clients by name, phone, email, member number, account number, NIDA, or branch..."
                        class="block w-full pl-12 pr-4 py-4 text-base border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all shadow-sm hover:shadow-md"
                    >
                    @if($search)
                        <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </div>

                <!-- Quick Filters -->
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-sm font-medium text-gray-700">Quick Filters:</span>

                    <!-- Status Filters -->
                    <button wire:click="$set('filters.status', 'ACTIVE')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $filters['status'] === 'ACTIVE' ? 'bg-green-100 text-green-800 border-2 border-green-300' : 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50' }}">
                        <i class="fas fa-check-circle mr-1"></i> Active
                    </button>

                    <button wire:click="$set('filters.status', 'PENDING')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $filters['status'] === 'PENDING' ? 'bg-yellow-100 text-yellow-800 border-2 border-yellow-300' : 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50' }}">
                        <i class="fas fa-clock mr-1"></i> Pending
                    </button>

                    <button wire:click="$set('filters.status', 'BLOCKED')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $filters['status'] === 'BLOCKED' ? 'bg-red-100 text-red-800 border-2 border-red-300' : 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50' }}">
                        <i class="fas fa-ban mr-1"></i> Blocked
                    </button>

                    <!-- Membership Type Filters -->
                    <div class="h-6 w-px bg-gray-300"></div>

                    <button wire:click="$set('filters.membership_type', 'Individual')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $filters['membership_type'] === 'Individual' ? 'bg-red-100 text-red-800 border-2 border-red-300' : 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50' }}">
                        <i class="fas fa-user mr-1"></i> Individual
                    </button>

                    <button wire:click="$set('filters.membership_type', 'Business')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $filters['membership_type'] === 'Business' ? 'bg-purple-100 text-purple-800 border-2 border-purple-300' : 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50' }}">
                        <i class="fas fa-briefcase mr-1"></i> Business
                    </button>

                    <!-- Clear Filters -->
                    @if($filters['status'] || $filters['membership_type'] || $search)
                        <button wire:click="clearFilters"
                            class="ml-auto px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                            <i class="fas fa-times"></i>
                            Clear All
                        </button>
                    @endif
                </div>

                <!-- Advanced Filters Toggle -->
                <div class="flex items-center justify-between pt-2">
                    <button wire:click="$toggle('showFilters')"
                        class="text-sm font-medium text-red-600 hover:text-red-800 flex items-center gap-2 transition-colors">
                        <svg class="w-4 h-4 transition-transform {{ $showFilters ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span>{{ $showFilters ? 'Hide' : 'Show' }} Advanced Filters</span>
                        @if($this->getActiveFiltersCount() > 0)
                            <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-semibold">
                                {{ $this->getActiveFiltersCount() }}
                            </span>
                        @endif
                    </button>

                    <button wire:click="$toggle('showColumnSelector')"
                        class="text-sm font-medium text-gray-600 hover:text-gray-800 flex items-center gap-2 transition-colors">
                        <i class="fas fa-columns"></i>
                        <span>Manage Columns</span>
                        <span class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold">
                            {{ $this->getVisibleColumnsCount() }}
                        </span>
                    </button>
                </div>

                <!-- Advanced Filters Panel -->
                @if($showFilters)
                    <div class="mt-4 p-6 bg-white rounded-xl border border-gray-200 shadow-sm">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Gender Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                                <select wire:model.live="filters.gender" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                    <option value="">All Genders</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <!-- Marital Status Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Marital Status</label>
                                <select wire:model.live="filters.marital_status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                    <option value="">All Status</option>
                                    <option value="single">Single</option>
                                    <option value="married">Married</option>
                                    <option value="divorced">Divorced</option>
                                    <option value="widowed">Widowed</option>
                                </select>
                            </div>

                            <!-- Nationality Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nationality</label>
                                <select wire:model.live="filters.nationality" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                    <option value="">All Nationalities</option>
                                    <option value="Tanzanian">Tanzanian</option>
                                    <option value="Kenyan">Kenyan</option>
                                    <option value="Ugandan">Ugandan</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <!-- Date Range -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                                <input type="date" wire:model.live="filters.start_date" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                                <input type="date" wire:model.live="filters.end_date" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Column Selector Panel -->
                @if($showColumnSelector)
                    <div class="mt-4 p-6 bg-white rounded-xl border border-gray-200 shadow-sm">
                        <h4 class="text-sm font-semibold text-gray-900 mb-4">Select Columns to Display</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                            @foreach(['account_number', 'client_number', 'branch_number', 'first_name', 'last_name', 'gender', 'phone_number', 'email', 'status', 'membership_type', 'created_at'] as $column)
                                <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors border border-gray-200">
                                    <input type="checkbox" wire:click="toggleColumn('{{ $column }}')"
                                        class="rounded border-gray-300 text-red-600 focus:ring-red-500 mr-2"
                                        {{ $columns[$column] ?? false ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-700">{{ $column === 'client_number' ? 'Client Number' : ($column === 'branch_number' ? 'Branch' : ($column === 'account_number' ? 'Bank Account Number' : ucfirst(str_replace('_', ' ', $column)))) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Modern Table -->
        <div class="overflow-x-auto relative">
            @if($loading)
                <div class="absolute inset-0 bg-white bg-opacity-90 flex items-center justify-center z-10">
                    <div class="flex flex-col items-center gap-3">
                        <div class="animate-spin rounded-full h-12 w-12 border-4 border-red-600 border-t-transparent"></div>
                        <span class="text-sm font-medium text-gray-600">Loading clients...</span>
                    </div>
                </div>
            @endif

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        @foreach($columns as $column => $visible)
                            @if($visible)
                                <th wire:click="sortBy('{{ $column }}')"
                                    class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-200 transition-colors">
                                    <div class="flex items-center gap-2">
                                        <span>{{ $column === 'client_number' ? 'Client Number' : ($column === 'branch_number' ? 'Branch' : ($column === 'account_number' ? 'Bank Account Number' : ucfirst(str_replace('_', ' ', $column)))) }}</span>
                                        @if($sortField === $column)
                                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if($sortDirection === 'asc')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                @endif
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                            </svg>
                                        @endif
                                    </div>
                                </th>
                            @endif
                        @endforeach
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($members as $index => $member)
                        <tr wire:key="member-{{ $member->id }}"
                            class="hover:bg-red-50 transition-colors {{ $index % 2 === 0 ? 'bg-gray-50' : 'bg-white' }}">
                            @foreach($columns as $column => $visible)
                                @if($visible)
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($column === 'status')
                                            @php
                                                $statusColors = [
                                                    'ACTIVE' => 'bg-green-100 text-green-800 border-green-200',
                                                    'PENDING' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                                    'BLOCKED' => 'bg-red-100 text-red-800 border-red-200',
                                                    'INACTIVE' => 'bg-gray-100 text-gray-800 border-gray-200',
                                                ];
                                                $statusIcons = [
                                                    'ACTIVE' => 'fa-check-circle',
                                                    'PENDING' => 'fa-clock',
                                                    'BLOCKED' => 'fa-ban',
                                                    'INACTIVE' => 'fa-minus-circle',
                                                ];
                                            @endphp
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border {{ $statusColors[$member->status] ?? 'bg-gray-100 text-gray-800 border-gray-200' }}">
                                                <i class="fas {{ $statusIcons[$member->status] ?? 'fa-circle' }}"></i>
                                                {{ $member->status }}
                                            </span>
                                        @elseif($column === 'membership_type')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold {{ $member->membership_type === 'Individual' ? 'bg-red-100 text-red-800' : 'bg-purple-100 text-purple-800' }}">
                                                <i class="fas {{ $member->membership_type === 'Individual' ? 'fa-user' : 'fa-briefcase' }}"></i>
                                                {{ $member->membership_type }}
                                            </span>
                                        @elseif($column === 'created_at')
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-gray-900">{{ $member->created_at->format('M d, Y') }}</span>
                                                <span class="text-xs text-gray-500">{{ $member->created_at->format('h:i A') }}</span>
                                            </div>
                                        @elseif($column === 'client_number' || $column === 'member_number')
                                            <span class="text-sm font-mono font-semibold text-red-600">{{ $member->$column }}</span>
                                        @elseif($column === 'branch_number')
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-building text-gray-400 text-xs"></i>
                                                <span class="text-sm text-gray-700">{{ $member->branch_name ?? $member->branch_number }}</span>
                                            </div>
                                        @elseif($column === 'phone_number' || $column === 'email')
                                            <div class="flex items-center gap-2">
                                                <i class="fas {{ $column === 'phone_number' ? 'fa-phone' : 'fa-envelope' }} text-gray-400 text-xs"></i>
                                                <span class="text-sm text-gray-700">{{ $member->$column }}</span>
                                            </div>
                                        @elseif($column === 'first_name' || $column === 'last_name')
                                            <span class="text-sm font-semibold text-gray-900">{{ $member->$column }}</span>
                                        @else
                                            <span class="text-sm text-gray-700">{{ $member->$column }}</span>
                                        @endif
                                    </td>
                                @endif
                            @endforeach
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <button wire:click="viewMember({{ $member->id }})"
                                        class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                                        title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button wire:click="editMember({{ $member->id }})"
                                        class="p-2 text-indigo-600 hover:bg-indigo-100 rounded-lg transition-colors"
                                        title="Edit Member">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="$emit('deleteMember', {{ $member->id }})"
                                        class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                                        title="Delete Member">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="20" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <div class="text-center">
                                        <p class="text-lg font-semibold text-gray-900">No members found</p>
                                        <p class="text-sm text-gray-500 mt-1">Try adjusting your search or filters</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Modern Pagination -->
        <div class="px-8 py-6 bg-gray-50 border-t border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-3">
                    <label class="text-sm font-medium text-gray-700">Rows per page:</label>
                    <select wire:model.live="perPage"
                        class="rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm font-medium">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-sm text-gray-600">
                        Showing <span class="font-semibold">{{ $members->firstItem() ?? 0 }}</span> to
                        <span class="font-semibold">{{ $members->lastItem() ?? 0 }}</span> of
                        <span class="font-semibold">{{ $members->total() }}</span> members
                    </span>
                </div>
                <div>
                    {{ $members->links() }}
                </div>
            </div>
        </div>
    </div>

    @if($showViewModal && $viewingMember)
        @include('livewire.clients.view-member', ['member' => $viewingMember])
    @endif

    @if($showEditModal && $editingMember)
        @include('livewire.clients.edit-member-modal')
    @endif
</div>
