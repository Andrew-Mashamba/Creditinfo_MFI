{{-- Enhanced Budget Scenarios View --}}
@php
    // Get all scenarios or filtered by selected budget
    $scenariosQuery = $selectedBudget 
        ? \App\Models\BudgetScenario::where('budget_id', $selectedBudget->id)
        : \App\Models\BudgetScenario::query();
    $allScenarios = $scenariosQuery->with('budget')->orderBy('created_at', 'desc')->get();
@endphp

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-medium text-gray-900">Budget Scenarios</h3>
        <button wire:click="openScenarioModal({{ $selectedBudget->id ?? 'null' }})" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
            Create New Scenario
        </button>
    </div>
    
    @if($selectedBudget)
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="font-medium text-gray-900">{{ $selectedBudget->budget_name }}</h4>
            <p class="text-sm text-gray-600">Viewing scenarios for this budget only</p>
        </div>
    @else
        <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">What are Budget Scenarios?</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>Budget scenarios allow you to create "what-if" analyses for your budgets:</p>
                        <ul class="list-disc list-inside mt-1">
                            <li><strong>Best Case:</strong> Optimistic projections with increased revenue/reduced costs</li>
                            <li><strong>Worst Case:</strong> Conservative projections with reduced revenue/increased costs</li>
                            <li><strong>Most Likely:</strong> Realistic projections based on current trends</li>
                            <li><strong>Custom:</strong> Any specific scenario you want to model</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    @if($allScenarios->count() > 0)
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($allScenarios as $scenario)
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">
                                {{ $scenario->scenario_name }}
                            </h3>
                            @if($scenario->is_active)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            @endif
                        </div>
                        
                        <dl class="mt-3 space-y-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Budget</dt>
                                <dd class="text-sm text-gray-900">{{ $scenario->budget->budget_name ?? 'Unknown' }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Scenario Type</dt>
                                <dd class="text-sm text-gray-900">
                                    @switch($scenario->scenario_type)
                                        @case('BEST_CASE')
                                            <span class="text-green-600">Best Case</span>
                                            @break
                                        @case('WORST_CASE')
                                            <span class="text-red-600">Worst Case</span>
                                            @break
                                        @case('EXPECTED')
                                            <span class="text-blue-600">Most Likely</span>
                                            @break
                                        @default
                                            <span class="text-gray-600">{{ $scenario->scenario_type }}</span>
                                    @endswitch
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Projected Amount</dt>
                                <dd class="text-sm text-gray-900">{{ number_format($scenario->projected_amount ?? 0, 2) }}</dd>
                            </div>
                            
                            @if($scenario->adjustment_percentage)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Adjustment</dt>
                                    <dd class="text-sm text-gray-900">
                                        @if($scenario->adjustment_percentage > 0)
                                            <span class="text-green-600">+{{ $scenario->adjustment_percentage }}%</span>
                                        @elseif($scenario->adjustment_percentage < 0)
                                            <span class="text-red-600">{{ $scenario->adjustment_percentage }}%</span>
                                        @else
                                            <span>0%</span>
                                        @endif
                                    </dd>
                                </div>
                            @endif
                            
                            @if($scenario->assumptions)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Key Assumptions</dt>
                                    <dd class="mt-1">
                                        <details class="cursor-pointer">
                                            <summary class="text-sm text-indigo-600 hover:text-indigo-500">View assumptions</summary>
                                            <div class="mt-2 text-sm text-gray-600">
                                                @if(is_array($scenario->assumptions))
                                                    <ul class="list-disc list-inside space-y-1">
                                                        @foreach($scenario->assumptions as $key => $assumption)
                                                            <li>{{ is_numeric($key) ? $assumption : $key . ': ' . $assumption }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    {{ $scenario->assumptions }}
                                                @endif
                                            </div>
                                        </details>
                                    </dd>
                                </div>
                            @endif
                        </dl>
                        
                        <div class="mt-4 flex justify-between items-center">
                            <p class="text-xs text-gray-500">
                                Created {{ $scenario->created_at->diffForHumans() }}
                            </p>
                            @if(!$scenario->is_active)
                                <button wire:click="activateScenario({{ $scenario->id }})" 
                                        class="text-xs text-indigo-600 hover:text-indigo-500">
                                    Activate
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No scenarios created yet</h3>
            <p class="mt-1 text-sm text-gray-500">
                @if($selectedBudget)
                    Get started by creating a new scenario for this budget.
                @else
                    Select a budget from the Overview tab to create scenarios, or view existing scenarios when available.
                @endif
            </p>
            @if($selectedBudget)
                <div class="mt-6">
                    <button wire:click="openScenarioModal({{ $selectedBudget->id }})" 
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Create First Scenario
                    </button>
                </div>
            @endif
        </div>
    @endif
    
    @if(!$selectedBudget && $allScenarios->count() == 0)
        <div class="bg-gray-50 rounded-lg p-6">
            <h4 class="text-sm font-medium text-gray-900 mb-2">Quick Tips for Budget Scenarios:</h4>
            <ul class="text-sm text-gray-600 space-y-1">
                <li>• Create multiple scenarios to compare different budget outcomes</li>
                <li>• Use adjustment percentages to quickly model changes</li>
                <li>• Document your assumptions for future reference</li>
                <li>• Activate the most realistic scenario for reporting</li>
                <li>• Review and update scenarios quarterly for accuracy</li>
            </ul>
        </div>
    @endif
</div>