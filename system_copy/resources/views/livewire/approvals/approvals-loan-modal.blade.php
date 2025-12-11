<!-- Loan Assessment Modal -->
@if($showLoanAssessmentModal)
    @php
        // Load member/client data properly
        $clientData = $memberData ?? null;
        
        // Ensure variables are properly set from component
        $loanStatistics = $loanStatistics ?? [];
        $loanAmountLimits = $loanAmountLimits ?? [];
        $incomeAssessment = $incomeAssessment ?? [];
        $termCalculation = $termCalculation ?? [];
        $collateralInfo = $collateralInfo ?? [];
        $deductions = $deductions ?? [];
        $assessmentSummary = $assessmentSummary ?? [];
        $productParameters = $productParameters ?? [];
        $policyExceptions = $assessmentData['policy_exceptions'] ?? [];
        $policyChecks = $assessmentData['policy_checks'] ?? [];
        
        // Calculate additional needed values
        $monthlyIncome = $incomeAssessment['take_home'] ?? 0;
        $monthlyInstallment = $loanStatistics['monthly_installment'] ?? 0;
        $debtServiceRatio = $monthlyIncome > 0 ? ($monthlyInstallment / $monthlyIncome) * 100 : 0;
        
        // Get credit score from assessment data
        $creditScore = $assessmentData['credit_score'] ?? 500;
        $activeLoansCount = $assessmentData['active_loans_count'] ?? 0;
        $totalSavings = $assessmentData['total_savings'] ?? 0;
        
        // Get guarantor information
        $guarantors = [];
        if ($guarantorData) {
            $guarantors[] = [
                'name' => ($guarantorData->first_name ?? '') . ' ' . ($guarantorData->last_name ?? ''),
                'relationship' => 'Guarantor',
                'id_number' => $guarantorData->id_number ?? 'N/A'
            ];
        }
    @endphp
    
    <div class="fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="bg-white rounded-lg shadow-xl max-w-7xl w-full h-screennn  flex flex-col" >
            <div class="flex flex-col h-full">
                <!-- Header (Fixed) -->
                <div class="bg-blue-900 px-6 py-4 flex-shrink-0" >
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-white" id="modal-title">
                                LOAN APPROVAL ASSESSMENT
                            </h3>
                            <p class="text-sm text-gray-300">
                                Loan ID: {{ $loanData->id ?? 'N/A' }} | Client Number: {{ $loanData->client_number ?? 'N/A' }} | Date: {{ now()->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <button wire:click="closeLoanAssessmentModal" type="button"
                            class="text-white hover:text-gray-300 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Content (Scrollable) -->
                <div class="flex-1 overflow-y-auto px-6 py-4">
                    @if($loanData)
                        <div class="space-y-6">
                            
                            <!-- Client Information Table -->
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 uppercase mb-2">1. CLIENT INFORMATION</h4>
                                <table class="min-w-full border border-gray-300">
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Full Name</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                @if($clientData)
                                                    {{ $clientData->first_name ?? '' }} {{ $clientData->middle_name ?? '' }} {{ $clientData->last_name ?? '' }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Client Number</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $loanData->client_number ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Phone Number</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                @if($clientData)
                                                    {{ $clientData->phone_number ?? $clientData->mobile_phone_number ?? 'N/A' }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Email</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                {{ $clientData->email ?? 'N/A' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">ID Number</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $clientData->id_number ?? 'N/A' }}</td>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Account Status</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $clientData->status ?? 'ACTIVE' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Loan Details Table -->
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 uppercase mb-2">2. LOAN APPLICATION DETAILS</h4>
                                <table class="min-w-full border border-gray-300">
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Product Name</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                {{ $productParameters['product_name'] ?? $loanData->loan_sub_product ?? 'N/A' }}
                                            </td>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Loan Type</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $loanData->loan_type ?? 'NEW' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Requested Amount</td>
                                            <td class="px-4 py-2 text-sm text-gray-900 font-bold">
                                                {{ number_format($loanAmountLimits['requested_amount'] ?? $loanData->approved_loan_value ?? 0, 2) }} TZS
                                            </td>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Approved Amount</td>
                                            <td class="px-4 py-2 text-sm text-gray-900 font-bold">
                                                {{ number_format($loanAmountLimits['approved_amount'] ?? $loanData->approved_loan_value ?? 0, 2) }} TZS
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Loan Term (Months)</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                {{ $termCalculation['requested_term'] ?? $loanData->tenure ?? 0 }}
                                            </td>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Interest Rate (% p.a.)</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                {{ $productParameters['interest_rate'] ?? $loanData->interest_rate ?? 0 }}%
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Purpose</td>
                                            <td class="px-4 py-2 text-sm text-gray-900" colspan="3">
                                                {{ $loanData->purpose ?? 'Not specified' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Financial Analysis Table -->
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 uppercase mb-2">3. FINANCIAL ANALYSIS</h4>
                                <table class="min-w-full border border-gray-300">
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Monthly Income (Take Home)</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ number_format($monthlyIncome, 2) }} TZS</td>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Monthly Installment</td>
                                            <td class="px-4 py-2 text-sm text-gray-900 font-bold">{{ number_format($monthlyInstallment, 2) }} TZS</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Debt Service Ratio</td>
                                            <td class="px-4 py-2 text-sm text-gray-900 font-bold">{{ number_format($debtServiceRatio, 1) }}%</td>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">DSR Status</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                {{ $debtServiceRatio > 40 ? 'EXCEEDS LIMIT (>40%)' : 'WITHIN LIMIT' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Total Repayment Amount</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                {{ number_format($loanStatistics['total_repayment'] ?? ($monthlyInstallment * ($loanData->tenure ?? 0)), 2) }} TZS
                                            </td>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Total Interest</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                {{ number_format($loanStatistics['total_interest'] ?? 0, 2) }} TZS
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Risk Assessment Table -->
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 uppercase mb-2">4. RISK ASSESSMENT</h4>
                                <table class="min-w-full border border-gray-300">
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Credit Score</td>
                                            <td class="px-4 py-2 text-sm text-gray-900 font-bold">{{ $creditScore }}</td>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Risk Classification</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                @php
                                                    $riskLevel = $creditScore >= 700 ? 'LOW RISK' : ($creditScore >= 500 ? 'MEDIUM RISK' : 'HIGH RISK');
                                                @endphp
                                                {{ $riskLevel }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Active Loans Count</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $activeLoansCount }}</td>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Total Savings</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ number_format($totalSavings, 2) }} TZS</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Loan to Savings Ratio</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                @php
                                                    $loanAmount = $loanAmountLimits['approved_amount'] ?? $loanData->approved_loan_value ?? 0;
                                                    $loanToSavingsRatio = $totalSavings > 0 ? ($loanAmount / $totalSavings) : 0;
                                                @endphp
                                                {{ number_format($loanToSavingsRatio, 2) }}:1
                                            </td>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Payment History</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $assessmentData['payment_history'] ?? 'N/A' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Security Information Table -->
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 uppercase mb-2">5. SECURITY INFORMATION</h4>
                                <table class="min-w-full border border-gray-300">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-900 uppercase">Type</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-900 uppercase">Details</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-900 uppercase">Value/Amount</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-900 uppercase">Coverage %</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @if(!empty($guarantors))
                                            @foreach($guarantors as $guarantor)
                                                <tr>
                                                    <td class="px-4 py-2 text-sm text-gray-900">Guarantor</td>
                                                    <td class="px-4 py-2 text-sm text-gray-900">
                                                        {{ $guarantor['name'] }} (ID: {{ $guarantor['id_number'] }})
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-gray-900">-</td>
                                                    <td class="px-4 py-2 text-sm text-gray-900">-</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        @if(($collateralInfo['collateral_value'] ?? 0) > 0)
                                            <tr>
                                                <td class="px-4 py-2 text-sm text-gray-900">Collateral</td>
                                                <td class="px-4 py-2 text-sm text-gray-900">{{ $collateralInfo['collateral_type'] ?? 'N/A' }}</td>
                                                <td class="px-4 py-2 text-sm text-gray-900">{{ number_format($collateralInfo['collateral_value'] ?? 0, 2) }} TZS</td>
                                                <td class="px-4 py-2 text-sm text-gray-900">{{ number_format($collateralInfo['coverage'] ?? 0, 1) }}%</td>
                                            </tr>
                                        @endif
                                        @if(empty($guarantors) && ($collateralInfo['collateral_value'] ?? 0) == 0)
                                            <tr>
                                                <td class="px-4 py-2 text-sm text-gray-900" colspan="4">No security provided</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                       
                            <!-- Deductions Table (if applicable) -->
                            @if(!empty($deductions) && ($deductions['total_deductions'] ?? 0) > 0)
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 uppercase mb-2">8. LOAN DEDUCTIONS</h4>
                                    <table class="min-w-full border border-gray-300">
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr>
                                                <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Processing Fees</td>
                                                <td class="px-4 py-2 text-sm text-gray-900">{{ number_format($deductions['processing_fees'] ?? 0, 2) }} TZS</td>
                                                <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Insurance</td>
                                                <td class="px-4 py-2 text-sm text-gray-900">{{ number_format($deductions['insurance'] ?? 0, 2) }} TZS</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Other Charges</td>
                                                <td class="px-4 py-2 text-sm text-gray-900">{{ number_format($deductions['other_charges'] ?? 0, 2) }} TZS</td>
                                                <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Top-up Clearance</td>
                                                <td class="px-4 py-2 text-sm text-gray-900">{{ number_format($deductions['top_up_clearance'] ?? 0, 2) }} TZS</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Total Deductions</td>
                                                <td class="px-4 py-2 text-sm text-gray-900 font-bold">{{ number_format($deductions['total_deductions'] ?? 0, 2) }} TZS</td>
                                                <td class="px-4 py-2 text-sm font-medium text-gray-900 bg-gray-50 border-r">Net Amount to Disburse</td>
                                                <td class="px-4 py-2 text-sm text-gray-900 font-bold">{{ number_format($deductions['net_amount'] ?? $loanData->approved_loan_value ?? 0, 2) }} TZS</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @endif


                        </div>
                    @else
                        <div class="flex items-center justify-center py-12">
                            <div class="text-center">
                                <p class="text-gray-600">Loading loan assessment data...</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Footer (Fixed) -->
                <div class="bg-gray-100 px-6 py-3 border-t border-gray-300 flex-shrink-0">
                    <div class="flex justify-between items-center">
                        <div class="text-xs text-gray-600">
                            Generated: {{ now()->format('d/m/Y H:i:s') }} | User: {{ auth()->user()->name ?? 'System' }}
                        </div>
                        <button wire:click="closeLoanAssessmentModal" type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 focus:outline-none">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif