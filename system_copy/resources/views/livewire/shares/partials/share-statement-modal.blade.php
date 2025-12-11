{{-- Share Statement Modal --}}
@if($showShareStatement)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="hideShareStatementModal"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-7xl sm:w-full">

            {{-- Modal Header --}}
            <div class="bg-blue-900 px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-medium text-white flex items-center" id="modal-title">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Share Statement
                </h3>
                <button type="button" wire:click="hideShareStatementModal" class="text-white hover:text-gray-200 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="bg-white px-6 py-4 max-h-[calc(100vh-200px)] overflow-y-auto">
                {{-- Statement Generation Form --}}
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Member Number</label>
                            <input type="text"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   wire:model="client_number"
                                   placeholder="Enter member number">
                            @error('client_number')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                            <input type="date"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   wire:model="dateFrom">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                            <input type="date"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   wire:model="dateTo">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">&nbsp;</label>
                            <button type="button"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-900 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    wire:click="generateShareStatement({{ $memberDetails->id ?? 'null' }}, '{{ $dateFrom }}', '{{ $dateTo }}')">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Generate
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Statement Display --}}
                @if($shareStatementData)
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                    {{-- Statement Header --}}
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                            <div>
                                <h6 class="text-base font-semibold text-gray-900">
                                    Statement for:
                                    <span class="text-blue-600">
                                        @if($shareStatementData['member'])
                                            {{ $shareStatementData['member']->first_name }} {{ $shareStatementData['member']->last_name }}
                                        @else
                                            Unknown Member
                                        @endif
                                    </span>
                                    @if($shareStatementData['member'])
                                        <span class="text-gray-600">({{ $shareStatementData['member']->client_number }})</span>
                                    @endif
                                </h6>
                                <p class="text-sm text-gray-500 mt-1">
                                    Period: {{ $shareStatementData['period_start']->format('d/m/Y') }} to {{ $shareStatementData['period_end']->format('d/m/Y') }}
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2 mt-3 md:mt-0">
                                @if($shareStatementData['member'])
                                    <button wire:click="exportShareStatementCSV({{ $shareStatementData['member']->id }})"
                                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Export CSV
                                    </button>
                                    <button wire:click="exportShareStatementPDF({{ $shareStatementData['member']->id }})"
                                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Export PDF
                                    </button>
                                @endif
                                <button onclick="window.print()"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-blue-900 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                    Print
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Statement Content --}}
                    <div class="px-6 py-4">
                        @foreach($shareStatementData['statements'] as $statement)
                        <div class="mb-6">
                            <div class="border-b border-gray-200 pb-2 mb-3">
                                <h6 class="text-base font-semibold text-gray-900">
                                    {{ $statement['product_name'] }}
                                    <span class="text-sm text-gray-600 font-normal">- Account: {{ $statement['account_number'] }}</span>
                                </h6>
                            </div>

                            {{-- Opening Balance --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3 bg-blue-50 p-3 rounded-md">
                                <div>
                                    <span class="font-semibold text-gray-700">Opening Balance:</span>
                                    <span class="text-gray-900">{{ number_format($statement['opening_balance']) }} shares</span>
                                </div>
                                <div class="md:text-right">
                                    <span class="font-semibold text-gray-700">Value:</span>
                                    <span class="text-gray-900">TZS {{ number_format($statement['opening_value'], 2) }}</span>
                                </div>
                            </div>


                            {{-- Transactions Table --}}
                            @if(count($statement['transactions']) > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th scope="col" class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Shares</th>
                                            <th scope="col" class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount (TZS)</th>
                                            <th scope="col" class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($statement['transactions'] as $transaction)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($transaction->date)->format('d/m/Y') }}
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm">
                                                @if($transaction->type == 'PURCHASE')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Purchase</span>
                                                @elseif($transaction->type == 'REDEMPTION')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Redemption</span>
                                                @elseif($transaction->type == 'TRANSFER_IN')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Transfer In</span>
                                                @elseif($transaction->type == 'TRANSFER_OUT')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Transfer Out</span>
                                                @else
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ $transaction->type }}</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-sm text-gray-900">{{ $transaction->reference }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-900">{{ $transaction->narration }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-right">
                                                @if(in_array($transaction->type, ['PURCHASE', 'TRANSFER_IN']))
                                                    <span class="text-green-600 font-semibold">+{{ number_format($transaction->shares) }}</span>
                                                @else
                                                    <span class="text-red-600 font-semibold">-{{ number_format($transaction->shares) }}</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 text-right">
                                                {{ number_format($transaction->amount, 2) }}
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 text-right font-medium">
                                                {{ number_format($transaction->balance_after) }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">No transactions found for this period.</p>
                                    </div>
                                </div>
                            </div>
                            @endif


                            {{-- Summary --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 bg-gray-50 p-4 rounded-lg">
                                <div>
                                    <table class="min-w-full text-sm">
                                        <tbody class="divide-y divide-gray-200">
                                            <tr>
                                                <td class="py-2 text-gray-700">Total Purchases:</td>
                                                <td class="py-2 text-right text-gray-900 font-medium">{{ number_format($statement['total_purchases']) }} shares</td>
                                            </tr>
                                            <tr>
                                                <td class="py-2 text-gray-700">Total Redemptions:</td>
                                                <td class="py-2 text-right text-gray-900 font-medium">{{ number_format($statement['total_redemptions']) }} shares</td>
                                            </tr>
                                            <tr>
                                                <td class="py-2 text-gray-700">Total Transfers In:</td>
                                                <td class="py-2 text-right text-gray-900 font-medium">{{ number_format($statement['total_transfers_in']) }} shares</td>
                                            </tr>
                                            <tr>
                                                <td class="py-2 text-gray-700">Total Transfers Out:</td>
                                                <td class="py-2 text-right text-gray-900 font-medium">{{ number_format($statement['total_transfers_out']) }} shares</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div>
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 h-full flex flex-col justify-center">
                                        <h6 class="text-base font-bold text-green-900 mb-2">Closing Balance</h6>
                                        <div class="text-sm text-green-800 space-y-1">
                                            <div>Shares: <span class="font-bold text-green-900">{{ number_format($statement['closing_balance']) }}</span></div>
                                            <div>Value: <span class="font-bold text-green-900">TZS {{ number_format($statement['closing_value'], 2) }}</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>


                    {{-- Footer --}}
                    <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                        <p class="text-xs text-gray-500">
                            Generated on: {{ $shareStatementData['generated_at']->format('d/m/Y H:i:s') }} by {{ auth()->user()->name }}
                        </p>
                    </div>
                </div>
                @endif
            </div>

            {{-- Modal Footer --}}
            <div class="bg-gray-50 px-6 py-3 flex justify-end border-t border-gray-200">
                <button type="button"
                        wire:click="hideShareStatementModal"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Print Styles --}}
<style nonce="{{ csp_nonce() }}">
@media print {
    /* Hide interactive elements when printing */
    .bg-gray-500.bg-opacity-75,
    button,
    .flex.justify-end {
        display: none !important;
    }

    /* Make modal full width for print */
    .fixed.inset-0 {
        position: static !important;
    }

    .inline-block {
        max-width: 100% !important;
        margin: 0 !important;
    }

    .shadow-xl {
        box-shadow: none !important;
    }

    /* Ensure content fits on page */
    .max-h-\[calc\(100vh-200px\)\] {
        max-height: none !important;
    }

    /* Clean up for print */
    .rounded-lg {
        border-radius: 0 !important;
    }
}
</style>