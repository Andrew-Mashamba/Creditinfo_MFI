{{-- Collection Management Dashboard --}}
<div class="space-y-6">
    <!-- Add New Case Modal -->
    @if($showAddCaseModal)
    <div class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeAddCaseModal" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Add New Collection Case
                            </h3>
                            <div class="mt-4">
                                <p class="text-sm text-gray-500">
                                    This feature would allow you to manually add a new collection case for follow-up. 
                                    In a production environment, this would include:
                                </p>
                                <ul class="mt-2 text-sm text-gray-500 list-disc list-inside">
                                    <li>Select loan from dropdown</li>
                                    <li>Set priority level</li>
                                    <li>Assign to collection officer</li>
                                    <li>Add notes and action plan</li>
                                    <li>Set follow-up date</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="closeAddCaseModal" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Flash Messages / Notifications -->
    @if (session()->has('message'))
        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">
                        {{ session('message') }}
                    </p>
                </div>
                <div class="ml-auto pl-3">
                    <button onclick="this.parentElement.parentElement.parentElement.style.display='none'" class="text-green-400 hover:text-green-600">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Header with Refresh Button -->
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-900">Collection Management</h2>
        <button wire:click="refreshData" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
            <svg class="h-4 w-4 mr-2 @if($this->isRefreshing ?? false) animate-spin @endif" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh Data
        </button>
    </div>

    <!-- Collection Performance Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Collection Rate -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Collection Rate</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($monthCollectionRate, 1) }}%</p>
                    <p class="text-sm text-gray-500 mt-1">This month</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-green-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="flex items-center text-green-500 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                    </svg>
                    <span class="ml-1">+2.1% from last month</span>
                </span>
            </div>
        </div>

        <!-- Amount Collected -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Amount Collected</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">TZS {{ number_format($monthCollected / 1000000, 1) }}M</p>
                    <p class="text-sm text-gray-500 mt-1">This month</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-red-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="flex items-center text-green-500 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                    </svg>
                    <span class="ml-1">+15.2% from last month</span>
                </span>
            </div>
        </div>

        <!-- Active Collection Cases -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Active Cases</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">{{ count($followUpRequired) }}</p>
                    <p class="text-sm text-gray-500 mt-1">Collection cases</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-orange-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-sm text-gray-500">Across all branches</span>
            </div>
        </div>

        <!-- Success Rate -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Success Rate</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($collectionRate, 1) }}%</p>
                    <p class="text-sm text-gray-500 mt-1">Overall collection rate</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-purple-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="flex items-center text-green-500 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                    </svg>
                    <span class="ml-1">+5.8% from last month</span>
                </span>
            </div>
        </div>
    </div>

    <!-- Detailed Collection Analytics -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Collection Analytics</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Today's Performance -->
            <div class="border-l-4 border-red-500 pl-4">
                <h4 class="text-sm font-medium text-gray-600 mb-2">Today's Performance</h4>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Due Amount:</span>
                        <span class="text-sm font-semibold text-gray-900">TZS {{ number_format($todayDue) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Collected:</span>
                        <span class="text-sm font-semibold text-green-600">TZS {{ number_format($todayCollected) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Collection Rate:</span>
                        <span class="text-sm font-semibold {{ $todayCollectionRate > 50 ? 'text-green-600' : 'text-orange-600' }}">{{ number_format($todayCollectionRate, 1) }}%</span>
                    </div>
                    <div class="pt-2 mt-2 border-t">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Pending:</span>
                            <span class="text-sm font-semibold text-red-600">TZS {{ number_format($todayDue - $todayCollected) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- This Week's Performance -->
            <div class="border-l-4 border-green-500 pl-4">
                <h4 class="text-sm font-medium text-gray-600 mb-2">This Week's Performance</h4>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Due Amount:</span>
                        <span class="text-sm font-semibold text-gray-900">TZS {{ number_format($weekDue) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Collected:</span>
                        <span class="text-sm font-semibold text-green-600">TZS {{ number_format($weekCollected) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Collection Rate:</span>
                        <span class="text-sm font-semibold {{ $weekCollectionRate > 50 ? 'text-green-600' : 'text-orange-600' }}">{{ number_format($weekCollectionRate, 1) }}%</span>
                    </div>
                    <div class="pt-2 mt-2 border-t">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Pending:</span>
                            <span class="text-sm font-semibold text-red-600">TZS {{ number_format($weekDue - $weekCollected) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- This Month's Performance -->
            <div class="border-l-4 border-purple-500 pl-4">
                <h4 class="text-sm font-medium text-gray-600 mb-2">This Month's Performance</h4>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Due Amount:</span>
                        <span class="text-sm font-semibold text-gray-900">TZS {{ number_format($monthDue) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Collected:</span>
                        <span class="text-sm font-semibold text-green-600">TZS {{ number_format($monthCollected) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Collection Rate:</span>
                        <span class="text-sm font-semibold {{ $monthCollectionRate > 50 ? 'text-green-600' : 'text-orange-600' }}">{{ number_format($monthCollectionRate, 1) }}%</span>
                    </div>
                    <div class="pt-2 mt-2 border-t">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Pending:</span>
                            <span class="text-sm font-semibold text-red-600">TZS {{ number_format($monthDue - $monthCollected) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Overall Summary -->
        <div class="mt-6 pt-6 border-t border-gray-200">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-900">TZS {{ number_format($totalDue / 1000000, 1) }}M</p>
                    <p class="text-xs text-gray-500 mt-1">Total Due</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-600">TZS {{ number_format($totalCollected / 1000000, 1) }}M</p>
                    <p class="text-xs text-gray-500 mt-1">Total Collected</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-red-600">TZS {{ number_format($pendingCollections / 1000000, 1) }}M</p>
                    <p class="text-xs text-gray-500 mt-1">Pending Collections</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold {{ $collectionRate > 50 ? 'text-green-600' : 'text-orange-600' }}">{{ number_format($collectionRate, 1) }}%</p>
                    <p class="text-xs text-gray-500 mt-1">Overall Collection Rate</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Collection Actions and Workflow -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Collection Workflow -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Collection Workflow</h3>
            
            <div class="space-y-4">
                <!-- Step 1: Initial Contact -->
                <div class="flex items-center p-4 bg-red-50 rounded-lg">
                    <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center mr-4">
                        <span class="text-white text-sm font-medium">1</span>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900">Initial Contact</h4>
                        <p class="text-sm text-gray-600">Phone call and SMS reminder</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900">24-48 hours</p>
                        <p class="text-xs text-gray-500">After due date</p>
                    </div>
                </div>
                
                <!-- Step 2: Follow-up -->
                <div class="flex items-center p-4 bg-yellow-50 rounded-lg">
                    <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center mr-4">
                        <span class="text-white text-sm font-medium">2</span>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900">Follow-up</h4>
                        <p class="text-sm text-gray-600">Personal visit and negotiation</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900">7-14 days</p>
                        <p class="text-xs text-gray-500">After initial contact</p>
                    </div>
                </div>
                
                <!-- Step 3: Escalation -->
                <div class="flex items-center p-4 bg-orange-50 rounded-lg">
                    <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center mr-4">
                        <span class="text-white text-sm font-medium">3</span>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900">Escalation</h4>
                        <p class="text-sm text-gray-600">Manager intervention and restructuring</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900">30-60 days</p>
                        <p class="text-xs text-gray-500">After follow-up</p>
                    </div>
                </div>
                
                <!-- Step 4: Legal Action -->
                <div class="flex items-center p-4 bg-red-50 rounded-lg">
                    <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center mr-4">
                        <span class="text-white text-sm font-medium">4</span>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900">Legal Action</h4>
                        <p class="text-sm text-gray-600">Legal proceedings and collateral recovery</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900">90+ days</p>
                        <p class="text-xs text-gray-500">After escalation</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Collection by Branch Performance -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Collection by Branch</h3>
            
            <div class="space-y-4">
                @forelse($collectionsByOfficer as $officer)
                @php
                    $officerData = is_array($officer) ? (object)$officer : $officer;
                @endphp
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
                            <span class="text-sm font-medium text-red-600">{{ substr($officerData->officer_name, 0, 2) }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $officerData->officer_name }}</p>
                            <p class="text-xs text-gray-500">{{ $officerData->loans_count }} loans</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-900">{{ number_format($officerData->collection_rate, 1) }}%</p>
                        <p class="text-xs text-gray-500">Collection Rate</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-gray-500">
                    No branch collection data available
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Active Collection Cases -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Active Collection Cases</h3>
                <div class="flex space-x-2">
                    <select wire:model="caseFilter" wire:change="filterCases" class="text-sm border border-gray-300 rounded-md px-3 py-1 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option>All Cases</option>
                        <option>Initial Contact</option>
                        <option>Follow-up</option>
                        <option>Escalation</option>
                        <option>Legal Action</option>
                    </select>
                    <button wire:click="addNewCase" class="text-sm text-red-600 hover:text-red-800 font-medium px-3 py-1 border border-red-600 rounded-md hover:bg-red-50 transition-colors">Add New Case</button>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Arrears Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days Overdue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recommended Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($followUpRequired as $case)
                    @php
                        $caseData = is_array($case) ? (object)$case : $case;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $caseData->loan_id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $caseData->client_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $caseData->phone_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">TZS {{ number_format($caseData->arrears_amount, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                @if($caseData->days_in_arrears > 90) bg-red-100 text-red-800
                                @elseif($caseData->days_in_arrears > 60) bg-orange-100 text-orange-800
                                @elseif($caseData->days_in_arrears > 30) bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ $caseData->days_in_arrears }} days
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ \Carbon\Carbon::parse($caseData->installment_date)->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                @if($caseData->recommended_action == 'Legal Action') bg-red-100 text-red-800
                                @elseif($caseData->recommended_action == 'Final Notice') bg-orange-100 text-orange-800
                                @elseif($caseData->recommended_action == 'Warning Letter') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ $caseData->recommended_action }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <button wire:click="sendReminder({{ $caseData->loan_id }})" 
                                        class="inline-flex items-center px-2.5 py-1.5 border border-red-600 text-xs font-medium rounded text-red-600 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors cursor-pointer">
                                    Remind
                                </button>
                                <button wire:click="initiateAction({{ $caseData->loan_id }}, '{{ $caseData->recommended_action }}')" 
                                        class="inline-flex items-center px-2.5 py-1.5 border border-green-600 text-xs font-medium rounded text-green-600 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors cursor-pointer">
                                    Action
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                            No loans requiring follow-up at this time
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-3 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <span class="font-medium">{{ min(1, count($followUpRequired)) }}</span> to <span class="font-medium">{{ count($followUpRequired) }}</span> of <span class="font-medium">{{ count($followUpRequired) }}</span> results
                </div>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50">Previous</button>
                    <button class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50">Next</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Recovery Actions Summary -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recovery Actions Required</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-red-600">{{ $recoveryActions['sms_reminders'] ?? 0 }}</div>
                <div class="text-xs text-gray-600 mt-1">SMS Reminders</div>
                <div class="text-xs text-gray-500">(1-7 days)</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-yellow-600">{{ $recoveryActions['phone_calls'] ?? 0 }}</div>
                <div class="text-xs text-gray-600 mt-1">Phone Calls</div>
                <div class="text-xs text-gray-500">(8-14 days)</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-orange-600">{{ $recoveryActions['reminder_letters'] ?? 0 }}</div>
                <div class="text-xs text-gray-600 mt-1">Reminder Letters</div>
                <div class="text-xs text-gray-500">(15-30 days)</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-orange-700">{{ $recoveryActions['warning_letters'] ?? 0 }}</div>
                <div class="text-xs text-gray-600 mt-1">Warning Letters</div>
                <div class="text-xs text-gray-500">(31-60 days)</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-red-600">{{ $recoveryActions['final_notices'] ?? 0 }}</div>
                <div class="text-xs text-gray-600 mt-1">Final Notices</div>
                <div class="text-xs text-gray-500">(61-90 days)</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-red-700">{{ $recoveryActions['legal_actions'] ?? 0 }}</div>
                <div class="text-xs text-gray-600 mt-1">Legal Actions</div>
                <div class="text-xs text-gray-500">(90+ days)</div>
            </div>
        </div>
    </div>

    <!-- Collection Tools and Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Bulk SMS -->
        <button wire:click="sendBulkSMS" class="flex items-center justify-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <div class="text-left">
                <p class="text-sm font-medium text-gray-900">Bulk SMS</p>
                <p class="text-xs text-gray-500">Send reminders</p>
            </div>
        </button>
        
        <!-- Phone Calls -->
        <button wire:click="makePhoneCalls" class="flex items-center justify-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
            <div class="text-left">
                <p class="text-sm font-medium text-gray-900">Phone Calls</p>
                <p class="text-xs text-gray-500">Make calls</p>
            </div>
        </button>
        
        <!-- Field Visits -->
        <button wire:click="scheduleVisits" class="flex items-center justify-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <div class="text-left">
                <p class="text-sm font-medium text-gray-900">Field Visits</p>
                <p class="text-xs text-gray-500">Schedule visits</p>
            </div>
        </button>
        
        <!-- Legal Actions -->
        <button wire:click="initiateLegalProceedings" class="flex items-center justify-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <div class="text-left">
                <p class="text-sm font-medium text-gray-900">Legal Actions</p>
                <p class="text-xs text-gray-500">Initiate proceedings</p>
            </div>
        </button>
    </div>

    <!-- Collection Reports & Export -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Reports & Export</h3>
            <div class="flex space-x-2">
                <button wire:click="exportToExcel" class="inline-flex items-center px-3 py-1.5 border border-green-600 text-xs font-medium rounded text-green-600 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export Excel
                </button>
                <button wire:click="exportToPDF" class="inline-flex items-center px-3 py-1.5 border border-red-600 text-xs font-medium rounded text-red-600 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Export PDF
                </button>
                <button wire:click="printReport" class="inline-flex items-center px-3 py-1.5 border border-gray-600 text-xs font-medium rounded text-gray-600 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print
                </button>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <button wire:click="generateDailyReport" class="flex items-center justify-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="text-sm font-medium text-gray-700">Daily Collection Report</span>
            </button>
            
            <button wire:click="generateWeeklyReport" class="flex items-center justify-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="text-sm font-medium text-gray-700">Weekly Performance</span>
            </button>
            
            <button wire:click="generateMonthlyReport" class="flex items-center justify-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="text-sm font-medium text-gray-700">Monthly Summary</span>
            </button>
        </div>
    </div>
</div>
