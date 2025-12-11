{{-- Enhanced Budget Versions View --}}
@php
    // Get all versions or filtered by selected budget
    $versionsQuery = $selectedBudget 
        ? \App\Models\BudgetVersion::where('budget_id', $selectedBudget->id)
        : \App\Models\BudgetVersion::query();
    $allVersions = $versionsQuery->with('budget')->orderBy('created_at', 'desc')->get();
@endphp

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-medium text-gray-900">Budget Versions</h3>
        @if($selectedBudget)
            <button wire:click="openVersionModal({{ $selectedBudget->id }})" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                Create New Version
            </button>
        @endif
    </div>
    
    @if($selectedBudget)
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="font-medium text-gray-900">{{ $selectedBudget->budget_name }}</h4>
            <p class="text-sm text-gray-600">Viewing versions for this budget only</p>
        </div>
    @endif
    
    @if($allVersions->count() > 0)
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                @foreach($allVersions as $version)
                    <li class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-indigo-600">
                                            {{ $version->version_name }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            Budget: {{ $version->budget->budget_name ?? 'Unknown' }}
                                        </p>
                                    </div>
                                    <div class="ml-2 flex-shrink-0 flex">
                                        @if($version->status === 'ACTIVE')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Active
                                            </span>
                                        @elseif($version->status === 'INACTIVE')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                Inactive
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                {{ $version->status }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="mt-2 sm:flex sm:justify-between">
                                    <div class="sm:flex">
                                        <p class="flex items-center text-sm text-gray-500">
                                            Version #{{ $version->version_number }}
                                        </p>
                                        <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                                            Type: {{ $version->version_type }}
                                        </p>
                                        <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                                            Amount: {{ number_format($version->allocated_amount, 2) }}
                                        </p>
                                    </div>
                                    <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                        Created {{ $version->created_at->diffForHumans() }}
                                    </div>
                                </div>
                                
                                @if($version->revision_reason)
                                    <p class="mt-2 text-sm text-gray-600">
                                        <strong>Reason:</strong> {{ $version->revision_reason }}
                                    </p>
                                @endif
                                
                                @if($version->budget_data)
                                    <details class="mt-2 cursor-pointer">
                                        <summary class="text-sm font-medium text-gray-700 hover:text-indigo-600">
                                            View Details
                                        </summary>
                                        <div class="mt-2 p-3 bg-gray-50 rounded-md text-sm">
                                            @if(isset($version->budget_data['spent_amount']))
                                                <p><strong>Spent:</strong> {{ number_format($version->budget_data['spent_amount'], 2) }}</p>
                                            @endif
                                            @if(isset($version->budget_data['committed_amount']))
                                                <p><strong>Committed:</strong> {{ number_format($version->budget_data['committed_amount'], 2) }}</p>
                                            @endif
                                            
                                            @if(isset($version->budget_data['old_values']) && isset($version->budget_data['new_values']))
                                                <div class="mt-2">
                                                    <p class="font-medium">Changes Made:</p>
                                                    <ul class="ml-4 mt-1 space-y-1">
                                                        @foreach($version->budget_data['new_values'] ?? [] as $key => $newValue)
                                                            @if(isset($version->budget_data['old_values'][$key]) && $version->budget_data['old_values'][$key] != $newValue)
                                                                <li class="text-xs">
                                                                    <span class="font-medium">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                                                                    <span class="text-red-600 line-through">{{ $version->budget_data['old_values'][$key] }}</span>
                                                                    →
                                                                    <span class="text-green-600">{{ $newValue }}</span>
                                                                </li>
                                                            @endif
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                            
                                            @if(isset($version->budget_data['approved_by']))
                                                <p class="mt-2 text-xs text-gray-500">
                                                    Approved by: {{ $version->budget_data['approved_by'] }} 
                                                    @if(isset($version->budget_data['approved_at']))
                                                        at {{ $version->budget_data['approved_at'] }}
                                                    @endif
                                                </p>
                                            @endif
                                        </div>
                                    </details>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="text-center py-8">
            <p class="text-gray-500">
                @if($selectedBudget)
                    No versions found for this budget.
                @else
                    No budget versions found in the system.
                @endif
            </p>
            <p class="text-sm text-gray-400 mt-2">
                Versions are created automatically when:
                <ul class="mt-2 text-left max-w-md mx-auto">
                    <li>• Budget edits are approved</li>
                    <li>• Budget transfers are executed</li>
                    <li>• Spending milestones are reached (50%, 75%, 100%)</li>
                    <li>• Monthly/quarterly closes are performed</li>
                    <li>• Commitments are cancelled</li>
                </ul>
            </p>
        </div>
    @endif
</div>