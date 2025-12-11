<div class="bg-white rounded-lg shadow-sm">

    {{-- Control Panel --}}
    <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <div class="flex flex-wrap items-center gap-3">
                {{-- Year Selection --}}
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700">Year:</label>
                    <select wire:model="selectedYear" 
                            class="text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endfor
                    </select>
                </div>

                {{-- Statement Navigation --}}
                <div class="flex space-x-1 bg-white rounded-lg border border-gray-200 p-1">
                    <button wire:click="switchStatement('dashboard')"
                            class="px-3 py-1.5 text-xs font-medium rounded {{ $currentStatement === 'dashboard' ? 'bg-blue-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        Dashboard
                    </button>
                    <button wire:click="switchStatement('balance_sheet')"
                            class="px-3 py-1.5 text-xs font-medium rounded {{ $currentStatement === 'balance_sheet' ? 'bg-blue-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        Financial Position
                    </button>
                    <button wire:click="switchStatement('income_statement')"
                            class="px-3 py-1.5 text-xs font-medium rounded {{ $currentStatement === 'income_statement' ? 'bg-blue-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        Income Statement
                    </button>
                    <button wire:click="switchStatement('cash_flow')"
                            class="px-3 py-1.5 text-xs font-medium rounded {{ $currentStatement === 'cash_flow' ? 'bg-blue-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        Cash Flows
                    </button>
                    <button wire:click="switchStatement('equity_changes')"
                            class="px-3 py-1.5 text-xs font-medium rounded {{ $currentStatement === 'equity_changes' ? 'bg-blue-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        Equity Changes
                    </button>
                    <button wire:click="switchStatement('trial_balance')"
                            class="px-3 py-1.5 text-xs font-medium rounded {{ $currentStatement === 'trial_balance' ? 'bg-blue-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        Trial Balance
                    </button>
                    <button wire:click="switchStatement('notes')"
                            class="px-3 py-1.5 text-xs font-medium rounded {{ $currentStatement === 'notes' ? 'bg-blue-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        Notes
                    </button>
                    <button wire:click="switchStatement('year_end')"
                            class="px-3 py-1.5 text-xs font-medium rounded {{ $currentStatement === 'year_end' ? 'bg-red-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        Year-End Activities
                    </button>
                </div>

                {{-- View Options --}}
                <div class="flex items-center space-x-3">
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="showDetailed" class="rounded border-gray-300 text-blue-600">
                        <span class="ml-2 text-sm text-gray-700">Detailed View</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="showComparison" class="rounded border-gray-300 text-blue-600">
                        <span class="ml-2 text-sm text-gray-700">Comparison</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="showNotes" class="rounded border-gray-300 text-blue-600">
                        <span class="ml-2 text-sm text-gray-700">Notes</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="showRatios" class="rounded border-gray-300 text-blue-600">
                        <span class="ml-2 text-sm text-gray-700">Ratios</span>
                    </label>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex space-x-2">
                <button wire:click="verifyRelationships" 
                        class="px-3 py-1.5 bg-purple-600 text-white text-xs font-medium rounded hover:bg-purple-700">
                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Verify
                </button>
                <button wire:click="regenerateStatements" 
                        class="px-3 py-1.5 bg-blue-900 text-white text-xs font-medium rounded hover:bg-blue-700">
                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Regenerate
                </button>
                <button wire:click="exportToExcel" 
                        class="px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700">
                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Excel
                </button>
                <button wire:click="exportToPDF" 
                        class="px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700">
                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    PDF
                </button>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="mx-6 mt-3 p-3 bg-green-50 border border-green-200 text-green-700 rounded-md text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mx-6 mt-3 p-3 bg-red-50 border border-red-200 text-red-700 rounded-md text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Main Content Area --}}
    <div class="p-6">
        {{-- Dashboard View --}}
        @if($currentStatement === 'dashboard')
            @include('livewire.accounting.partials.financial-dashboard')
        
        {{-- Statement of Financial Position (Balance Sheet) --}}
        @elseif($currentStatement === 'balance_sheet')
            @include('livewire.accounting.partials.balance-sheet-integrated-enhanced')
        
        {{-- Income Statement --}}
        @elseif($currentStatement === 'income_statement')
            @include('livewire.accounting.partials.income-statement-integrated')
        
        {{-- Cash Flow Statement --}}
        @elseif($currentStatement === 'cash_flow')
            @include('livewire.accounting.partials.cash-flow-integrated')
        
        {{-- Statement of Changes in Equity --}}
        @elseif($currentStatement === 'equity_changes')
            @include('livewire.accounting.partials.equity-changes-integrated')
        
        {{-- Trial Balance --}}
        @elseif($currentStatement === 'trial_balance')
            @include('livewire.accounting.partials.trial-balance-integrated')
        
        {{-- Notes to Financial Statements --}}
        @elseif($currentStatement === 'notes')
            @include('livewire.accounting.partials.notes-to-financial-statements-integrated')
        
        {{-- Year-End Activities --}}
        @elseif($currentStatement === 'year_end')
            @include('livewire.accounting.partials.year-end-activities')
        @endif

        {{-- Financial Ratios Section --}}
        @if($showRatios && count($financialRatios) > 0)
            <div class="mt-8 border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Financial Ratios & Key Performance Indicators</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($financialRatios as $category => $ratios)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">
                                {{ ucfirst($category) }} Ratios
                            </h4>
                            @foreach($ratios as $ratio)
                                <div class="mb-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-600">{{ $ratio['ratio_name'] }}</span>
                                        <span class="text-sm font-semibold 
                                            @if(isset($ratio['trend']) && $ratio['trend'] === 'improving') text-green-600
                                            @elseif(isset($ratio['trend']) && $ratio['trend'] === 'declining') text-red-600
                                            @else text-gray-900
                                            @endif">
                                            {{ number_format($ratio['value'] ?? 0, 2) }}
                                            @if(str_contains($ratio['ratio_name'], 'Margin') || str_contains($ratio['ratio_name'], 'Return') || str_contains($ratio['ratio_name'], 'Ratio'))%@endif
                                        </span>
                                    </div>
                                    @if(isset($ratio['benchmark_value']) && $ratio['benchmark_value'])
                                        <div class="mt-1">
                                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                <div class="bg-blue-900 h-1.5 rounded-full" 
                                                     style="width: {{ min(100, ($ratio['value'] / $ratio['benchmark_value']) * 100) }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-500">Benchmark: {{ number_format($ratio['benchmark_value'], 2) }}</span>
                                        </div>
                                    @endif
                                    @if(isset($ratio['interpretation']) && $ratio['interpretation'])
                                        <p class="text-xs text-gray-500 mt-1">{{ $ratio['interpretation'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Notes to Financial Statements --}}
        @if($showNotes && count($financialNotes) > 0)
            <div class="mt-8 border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Notes to Financial Statements</h3>
                <div class="space-y-4">
                    @foreach($financialNotes as $note)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-900">
                                Note {{ $note['note_number'] }}: {{ $note['note_title'] }}
                            </h4>
                            <p class="text-sm text-gray-600 mt-2">{{ $note['content'] }}</p>
                            @if($note['breakdown_data'])
                                <div class="mt-3 bg-white rounded p-3">
                                    <table class="w-full text-xs">
                                        <tbody>
                                            @foreach($note['breakdown_data'] as $key => $value)
                                                <tr>
                                                    <td class="py-1 text-gray-600">{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                                                    <td class="py-1 text-right font-medium">
                                                        {{ is_numeric($value) ? $this->formatNumber($value) : $value }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Footer with Relationships Indicator --}}
    <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 rounded-b-lg">
        <div class="flex justify-between items-center text-xs text-gray-600">
            <div>
                <span class="font-medium">Period:</span> 
                {{ $financialPeriod->display_name ?? 'Not Set' }}
                @if($financialPeriod && $financialPeriod->is_audited)
                    <span class="ml-2 px-2 py-0.5 bg-green-100 text-green-700 rounded">Audited</span>
                @endif
            </div>
            <div class="flex items-center space-x-4">
                <span>
                    <span class="inline-block w-2 h-2 bg-green-500 rounded-full"></span>
                    Income → Equity: Connected
                </span>
                <span>
                    <span class="inline-block w-2 h-2 bg-green-500 rounded-full"></span>
                    Equity → Balance Sheet: Connected
                </span>
                <span>
                    <span class="inline-block w-2 h-2 bg-green-500 rounded-full"></span>
                    Cash Flow → Balance Sheet: Connected
                </span>
            </div>
        </div>
    </div>
</div>