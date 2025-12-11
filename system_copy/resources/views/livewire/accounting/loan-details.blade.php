<div class="h-full bg-gray-50 w-full">
    <!-- Success/Error Messages -->
    @if (session()->has('message'))
        <div class="mb-4 sm:mb-6 mx-2 sm:mx-6 mt-4 sm:mt-6 p-3 sm:p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-center">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-500 mr-2 sm:mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-green-800 font-medium text-xs sm:text-sm">{{ session('message') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 sm:mb-6 mx-2 sm:mx-6 mt-4 sm:mt-6 p-3 sm:p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-red-500 mr-2 sm:mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-red-800 font-medium text-xs sm:text-sm">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if(Session::get('currentloanID'))
        @php
            $loanID = session('currentloanID');
            $loan = DB::table('loans')->find($loanID);
            $exception_status = false;
            $topUpAmount = 0;

            if ($loan) {
                $member = DB::table('clients')->where('client_number', $loan->client_number)->first();
                $product = DB::table('loan_sub_products')->where('sub_product_id', $loan->loan_sub_product)->first();
            } else {
                $member = null;
                $product = null;
            }

            $score = \Illuminate\Support\Facades\DB::table('scores')->where('client_id', 111222333)->first();
            $retirementAge = 60;

            if (!is_null($member->date_of_birth)) {
                $dob = Carbon\Carbon::parse($member->date_of_birth);
                $age = $dob->age;
                $yearsToRetirement = max(0, $retirementAge - $age);
                $retirementDate = $dob->copy()->addYears($retirementAge);
                $lastDayOfMonth = $retirementDate->endOfMonth()->day;
                $retirementDay = $lastDayOfMonth == 31 ? 31 : 30;
                $retirementDate->day = $retirementDay;
                $monthsToRetirement = now()->diffInMonths($retirementDate);
            } else {
                $monthsToRetirement = null;
                $retirementDate = null;
            }

            $savings = DB::table('sub_accounts')->where('product_number','2000')->where('client_number',$loan->client_number)->sum('balance');
            $loanCount = Illuminate\Support\Facades\DB::table('loans')->where('client_number',$loan->client_number)->where('status','ACTIVE')->count() + 2;

            if ($this->non_permanent_income_taxable <= 270000) {
                $tax = 0;
            } elseif ($this->non_permanent_income_taxable <= 520000) {
                $tax = 0.08 * ($this->non_permanent_income_taxable - 270000);
            } elseif ($this->non_permanent_income_taxable <= 760000) {
                $tax = 20000 + 0.20 * ($this->non_permanent_income_taxable - 520000);
            } elseif ($this->non_permanent_income_taxable <= 1000000) {
                $tax = 68000 + 0.25 * ($this->non_permanent_income_taxable - 760000);
            } else {
                $tax = 128000 + 0.30 * ($this->non_permanent_income_taxable - 1000000);
            }

            $records = \Illuminate\Support\Facades\DB::table('query_responses')->where('CheckNumber', 111222333)->get();
            $totalContracts = 0;
            $totalContracts2 = 3;
            $ctotalAmount = 0;

            foreach ($records as $record) {
                $response_data = json_decode($record->response_data, true);
                $contracts = $response_data['response']['CustomReport']['Contracts']['ContractList']['Contract'] ?? [];

                if (isset($contracts['Subscriber'])) {
                    $totalContracts += 1;
                    $ctotalAmount += $contracts['TotalAmount']['Value'];
                } elseif (is_array($contracts)) {
                    $totalContracts += count($contracts);
                    foreach ($contracts as $contract) {
                        $ctotalAmount += $contract['TotalAmount']['Value'];
                    }
                }
            }

            if($this->approved_term) {
                // Keep existing value
            } else {
                $this->approved_term = $product->max_term;
            }

            if($this->approved_loan_value) {
                // Keep existing value
            } else {
                $this->approved_loan_value = $loan->principle;
            }
        @endphp

        <div class="h-full overflow-y-auto w-full">
            <!-- Header Section -->
            <div class="bg-white border-b border-gray-200 px-3 sm:px-6 py-3 sm:py-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-center space-x-3 sm:space-x-4">
                        <div class="w-10 h-10 sm:w-16 sm:h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-lg sm:text-xl">
                            {{ strtoupper(substr($member->first_name, 0, 1)) }}
                        </div>
                        <div>
                            <h1 class="text-lg sm:text-2xl font-bold text-gray-900">{{ $member->first_name }} {{ $member->middle_name }} {{ $member->last_name }}</h1>
                            <p class="text-xs sm:text-base text-gray-600">Client ID: {{ $member->client_number }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                        <span class="inline-flex items-center px-2 sm:px-3 py-0.5 sm:py-1 rounded-full text-xs sm:text-sm font-medium bg-yellow-100 text-yellow-800">
                            {{ $loan->loan_type_2 }}
                        </span>
                        <span class="inline-flex items-center px-2 sm:px-3 py-0.5 sm:py-1 rounded-full text-xs sm:text-sm font-medium bg-green-100 text-green-800">
                            Ready for Disbursement
                        </span>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="p-2 sm:p-4 md:p-6 space-y-4 sm:space-y-6 w-full">
                <!-- Information Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 md:gap-6">
                    <!-- Client Information Card -->
                    <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-3 sm:px-6 py-3 sm:py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Client Information
                            </h3>
                        </div>
                        <div class="p-3 sm:p-6 space-y-2 sm:space-y-4">
                            <div class="grid grid-cols-2 gap-2 sm:gap-4 text-xs sm:text-sm">
                                <div>
                                    <p class="text-gray-500 font-medium">Full Name</p>
                                    <p class="text-gray-900 font-semibold">{{ $member->first_name }} {{ $member->middle_name }} {{ $member->last_name }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 font-medium">Date of Birth</p>
                                    <p class="text-gray-900">{{ $member->date_of_birth }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 font-medium">Current Age</p>
                                    <p class="text-gray-900">{{ $age }} years</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 font-medium">Time to Retirement</p>
                                    <p class="text-gray-900">{{ $monthsToRetirement }} months</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-gray-500 font-medium">Total Savings</p>
                                    <p class="text-xl sm:text-2xl font-bold text-green-600">{{ number_format($savings, 2) }} TZS</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Parameters Card -->
                    <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-3 sm:px-6 py-3 sm:py-4 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-200">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Product Parameters
                            </h3>
                        </div>
                        <div class="p-3 sm:p-6 space-y-2 sm:space-y-4">
                            <div class="grid grid-cols-2 gap-2 sm:gap-4 text-xs sm:text-sm">
                                <div>
                                    <p class="text-gray-500 font-medium">Product Name</p>
                                    <p class="text-gray-900 font-semibold">{{ $product ? $product->sub_product_name : 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 font-medium">Interest Rate</p>
                                    <p class="text-gray-900">{{ $product ? number_format((float)$product->interest_value, 2) . '% p.a.' : 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 font-medium">Max Term</p>
                                    <p class="text-gray-900">{{ $product ? $product->max_term . ' months' : 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 font-medium">Max Amount</p>
                                    <p class="text-gray-900">{{ $product ? number_format($product->principle_max_value, 2) . ' TZS' : 'N/A' }}</p>
                                </div>
                            </div>
                            <!-- Charges Section -->
                            @if($this->charges)
                                <div class="border-t border-gray-200 pt-2 sm:pt-4">
                                    <h4 class="text-xs sm:text-sm font-semibold text-gray-900 mb-1 sm:mb-3">Charges</h4>
                                    <div class="space-y-1 sm:space-y-2">
                                        @foreach($this->charges as $charge)
                                            <div class="flex justify-between items-center text-xs sm:text-sm">
                                                <span class="text-gray-600">{{ $charge->name }}</span>
                                                <span class="font-semibold text-gray-900">
                                                    @if($charge->calculating_type === "Fixed")
                                                        {{ number_format($charge->value, 2) }} TZS
                                                    @else
                                                        {{ $product ? number_format(($this->approved_loan_value * $charge->value / 100), 2) . ' TZS' : 'N/A' }}
                                                    @endif
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Exceptions Card -->
                    <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-3 sm:px-6 py-3 sm:py-4 bg-gradient-to-r from-orange-50 to-amber-50 border-b border-gray-200">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                Exceptions & Validation
                            </h3>
                        </div>
                        <div class="p-3 sm:p-6 space-y-2 sm:space-y-4">
                            <!-- Maximum Loan Amount Exception -->
                            <div class="space-y-1 sm:space-y-2">
                                <h4 class="text-xs sm:text-sm font-semibold text-gray-900">Maximum Loan Amount</h4>
                                <div class="bg-gray-50 rounded-lg p-2 sm:p-3">
                                    <div class="flex justify-between items-center text-xs sm:text-sm">
                                        <span class="text-gray-600">Limit:</span>
                                        <span class="font-semibold">{{ number_format($product->principle_max_value, 2) }} TZS</span>
                                    </div>
                                    <div class="flex justify-between items-center text-xs sm:text-sm">
                                        <span class="text-gray-600">Requested:</span>
                                        <span class="font-semibold">{{ number_format($this->approved_loan_value, 2) }} TZS</span>
                                    </div>
                                    <div class="mt-1 sm:mt-2">
                                        @if((int)$this->approved_loan_value > (int)$product->principle_max_value)
                                            <span class="inline-flex items-center px-1.5 sm:px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                </svg>
                                                LIMIT EXCEEDED
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-1.5 sm:px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                                ACCEPTED
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Maximum Term Exception -->
                            <div class="space-y-1 sm:space-y-2">
                                <h4 class="text-xs sm:text-sm font-semibold text-gray-900">Maximum Term</h4>
                                <div class="bg-gray-50 rounded-lg p-2 sm:p-3">
                                    <div class="flex justify-between items-center text-xs sm:text-sm">
                                        <span class="text-gray-600">Limit:</span>
                                        <span class="font-semibold">{{ $product->max_term }} months</span>
                                    </div>
                                    <div class="flex justify-between items-center text-xs sm:text-sm">
                                        <span class="text-gray-600">Requested:</span>
                                        <span class="font-semibold">{{ $this->approved_term }} months</span>
                                    </div>
                                    <div class="mt-1 sm:mt-2">
                                        @if((int)$this->approved_term > (int)$product->max_term)
                                            <span class="inline-flex items-center px-1.5 sm:px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                </svg>
                                                TERM EXCEEDED
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-1.5 sm:px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                                ACCEPTED
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Approval Chain Card -->
                <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-3 sm:px-6 py-3 sm:py-4 bg-gradient-to-r from-emerald-50 to-teal-50 border-b border-gray-200">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Approval Chain
                        </h3>
                    </div>
                    <div class="p-3 sm:p-6">
                        @php
                            // Get the main approval record for this loan
                            $mainApproval = DB::table('approvals')
                                ->where('process_id', $loanID)
                                ->first();
                            
                            // Get process configuration for role information
                            $processConfig = null;
                            if ($mainApproval) {
                                $processConfig = DB::table('process_code_configs')
                                    ->where('process_code', $mainApproval->process_code)
                                    ->first();
                            }
                        @endphp

                        @if($mainApproval)
                            <div class="space-y-4 sm:space-y-6">
                                <!-- Approval Flow Visualization -->
                                <div class="flex flex-col md:flex-row items-center justify-between mb-4 sm:mb-6 gap-4">
                                    <div class="flex-1 flex flex-col md:flex-row items-center space-y-2 md:space-y-0 md:space-x-4">
                                        <!-- First Checker -->
                                        <div class="flex flex-col items-center">
                                            <div class="w-8 h-8 sm:w-12 sm:h-12 rounded-full flex items-center justify-center text-white font-semibold text-xs sm:text-sm md:text-base
                                                {{ $mainApproval->first_checker_status === 'APPROVED' ? 'bg-green-500' : 
                                                   ($mainApproval->first_checker_status === 'REJECTED' ? 'bg-red-500' : 'bg-gray-400') }}">
                                                @if($mainApproval->first_checker_status === 'APPROVED')
                                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @elseif($mainApproval->first_checker_status === 'REJECTED')
                                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @else
                                                    1
                                                @endif
                                            </div>
                                            <span class="text-xs text-gray-600 mt-1">First Checker</span>
                                        </div>
                                        <!-- Arrow -->
                                        <div class="flex-1 h-0.5 bg-gray-300 relative hidden md:block">
                                            @if($mainApproval->first_checker_status === 'APPROVED')
                                                <div class="absolute inset-0 bg-green-500 h-0.5"></div>
                                            @endif
                                        </div>
                                        <!-- Second Checker -->
                                        <div class="flex flex-col items-center">
                                            <div class="w-8 h-8 sm:w-12 sm:h-12 rounded-full flex items-center justify-center text-white font-semibold text-xs sm:text-sm md:text-base
                                                {{ $mainApproval->second_checker_status === 'APPROVED' ? 'bg-green-500' : 
                                                   ($mainApproval->second_checker_status === 'REJECTED' ? 'bg-red-500' : 'bg-gray-400') }}">
                                                @if($mainApproval->second_checker_status === 'APPROVED')
                                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @elseif($mainApproval->second_checker_status === 'REJECTED')
                                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @else
                                                    2
                                                @endif
                                            </div>
                                            <span class="text-xs text-gray-600 mt-1">Second Checker</span>
                                        </div>
                                        <!-- Arrow -->
                                        <div class="flex-1 h-0.5 bg-gray-300 relative hidden md:block">
                                            @if($mainApproval->second_checker_status === 'APPROVED')
                                                <div class="absolute inset-0 bg-green-500 h-0.5"></div>
                                            @endif
                                        </div>
                                        <!-- Final Approver -->
                                        <div class="flex flex-col items-center">
                                            <div class="w-8 h-8 sm:w-12 sm:h-12 rounded-full flex items-center justify-center text-white font-semibold text-xs sm:text-sm md:text-base
                                                {{ $mainApproval->approval_status === 'APPROVED' ? 'bg-green-500' : 
                                                   ($mainApproval->approval_status === 'REJECTED' ? 'bg-red-500' : 'bg-gray-400') }}">
                                                @if($mainApproval->approval_status === 'APPROVED')
                                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @elseif($mainApproval->approval_status === 'REJECTED')
                                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @else
                                                    3
                                                @endif
                                            </div>
                                            <span class="text-xs text-gray-600 mt-1">Final Approver</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Approval Details -->
                                <div class="space-y-2 sm:space-y-4">
                                    <!-- First Checker Details -->
                                    @if($processConfig && $processConfig->requires_first_checker)
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-4">
                                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-sm font-semibold text-gray-900">First Checker</h4>
                                                        @if($mainApproval->first_checker_id)
                                                            @php
                                                                $firstChecker = DB::table('users')->where('id', $mainApproval->first_checker_id)->first();
                                                            @endphp
                                                            <p class="text-sm text-gray-600">{{ $firstChecker ? $firstChecker->name : 'Unknown User' }}</p>
                                                            @if($mainApproval->first_checked_at)
                                                                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($mainApproval->first_checked_at)->format('M d, Y H:i') }}</p>
                                                            @endif
                                                        @else
                                                            <p class="text-sm text-gray-500">Pending Assignment</p>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                                        {{ $mainApproval->first_checker_status === 'APPROVED' ? 'bg-green-100 text-green-800' : 
                                                           ($mainApproval->first_checker_status === 'REJECTED' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                        {{ $mainApproval->first_checker_status ?? 'PENDING' }}
                                                    </span>
                                                </div>
                                            </div>
                                            @if($mainApproval->first_checker_rejection_reason)
                                                <div class="mt-3 p-3 bg-red-50 rounded-lg border-l-4 border-red-400">
                                                    <p class="text-sm text-red-700">
                                                        <span class="font-medium">Rejection Reason:</span> {{ $mainApproval->first_checker_rejection_reason }}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    <!-- Second Checker Details -->
                                    @if($processConfig && $processConfig->requires_second_checker)
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-4">
                                                    <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-sm font-semibold text-gray-900">Second Checker</h4>
                                                        @if($mainApproval->second_checker_id)
                                                            @php
                                                                $secondChecker = DB::table('users')->where('id', $mainApproval->second_checker_id)->first();
                                                            @endphp
                                                            <p class="text-sm text-gray-600">{{ $secondChecker ? $secondChecker->name : 'Unknown User' }}</p>
                                                            @if($mainApproval->second_checked_at)
                                                                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($mainApproval->second_checked_at)->format('M d, Y H:i') }}</p>
                                                            @endif
                                                        @else
                                                            <p class="text-sm text-gray-500">Pending Assignment</p>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                                        {{ $mainApproval->second_checker_status === 'APPROVED' ? 'bg-green-100 text-green-800' : 
                                                           ($mainApproval->second_checker_status === 'REJECTED' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                        {{ $mainApproval->second_checker_status ?? 'PENDING' }}
                                                    </span>
                                                </div>
                                            </div>
                                            @if($mainApproval->second_checker_rejection_reason)
                                                <div class="mt-3 p-3 bg-red-50 rounded-lg border-l-4 border-red-400">
                                                    <p class="text-sm text-red-700">
                                                        <span class="font-medium">Rejection Reason:</span> {{ $mainApproval->second_checker_rejection_reason }}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    <!-- Final Approver Details -->
                                    @if($processConfig && $processConfig->requires_approver)
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-4">
                                                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-sm font-semibold text-gray-900">Final Approver</h4>
                                                        @if($mainApproval->approver_id)
                                                            @php
                                                                $approver = DB::table('users')->where('id', $mainApproval->approver_id)->first();
                                                            @endphp
                                                            <p class="text-sm text-gray-600">{{ $approver ? $approver->name : 'Unknown User' }}</p>
                                                            @if($mainApproval->approved_at)
                                                                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($mainApproval->approved_at)->format('M d, Y H:i') }}</p>
                                                            @endif
                                                        @else
                                                            <p class="text-sm text-gray-500">Pending Assignment</p>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                                        {{ $mainApproval->approval_status === 'APPROVED' ? 'bg-green-100 text-green-800' : 
                                                           ($mainApproval->approval_status === 'REJECTED' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                        {{ $mainApproval->approval_status ?? 'PENDING' }}
                                                    </span>
                                                </div>
                                            </div>
                                            @if($mainApproval->approver_rejection_reason)
                                                <div class="mt-3 p-3 bg-red-50 rounded-lg border-l-4 border-red-400">
                                                    <p class="text-sm text-red-700">
                                                        <span class="font-medium">Rejection Reason:</span> {{ $mainApproval->approver_rejection_reason }}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    <!-- Role Information -->
                                    @if($processConfig)
                                        <div class="bg-indigo-50 rounded-lg p-2 sm:p-4 border-l-4 border-indigo-400">
                                            <h4 class="text-xs sm:text-sm font-semibold text-gray-900 mb-2 sm:mb-3">Required Roles</h4>
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 sm:gap-4 text-xs sm:text-sm">
                                                @if($processConfig->requires_first_checker)
                                                    <div>
                                                        <span class="font-medium text-gray-700">First Checker:</span>
                                                        <div class="mt-1">
                                                            @php
                                                                $firstCheckerRoleIds = is_string($processConfig->first_checker_roles) ? 
                                                                    json_decode($processConfig->first_checker_roles, true) : 
                                                                    $processConfig->first_checker_roles;
                                                                $firstCheckerRoles = DB::table('roles')->whereIn('id', $firstCheckerRoleIds ?? [])->pluck('name');
                                                            @endphp
                                                            @if($firstCheckerRoles->count() > 0)
                                                                @foreach($firstCheckerRoles as $role)
                                                                    <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded mr-1 mb-1">{{ $role }}</span>
                                                                @endforeach
                                                            @else
                                                                <span class="text-gray-500 text-xs">No specific roles</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif

                                                @if($processConfig->requires_second_checker)
                                                    <div>
                                                        <span class="font-medium text-gray-700">Second Checker:</span>
                                                        <div class="mt-1">
                                                            @php
                                                                $secondCheckerRoleIds = is_string($processConfig->second_checker_roles) ? 
                                                                    json_decode($processConfig->second_checker_roles, true) : 
                                                                    $processConfig->second_checker_roles;
                                                                $secondCheckerRoles = DB::table('roles')->whereIn('id', $secondCheckerRoleIds ?? [])->pluck('name');
                                                            @endphp
                                                            @if($secondCheckerRoles->count() > 0)
                                                                @foreach($secondCheckerRoles as $role)
                                                                    <span class="inline-block bg-orange-100 text-orange-800 text-xs px-2 py-1 rounded mr-1 mb-1">{{ $role }}</span>
                                                                @endforeach
                                                            @else
                                                                <span class="text-gray-500 text-xs">No specific roles</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif

                                                @if($processConfig->requires_approver)
                                                    <div>
                                                        <span class="font-medium text-gray-700">Final Approver:</span>
                                                        <div class="mt-1">
                                                            @php
                                                                $approverRoleIds = is_string($processConfig->approver_roles) ? 
                                                                    json_decode($processConfig->approver_roles, true) : 
                                                                    $processConfig->approver_roles;
                                                                $approverRoles = DB::table('roles')->whereIn('id', $approverRoleIds ?? [])->pluck('name');
                                                            @endphp
                                                            @if($approverRoles->count() > 0)
                                                                @foreach($approverRoles as $role)
                                                                    <span class="inline-block bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded mr-1 mb-1">{{ $role }}</span>
                                                                @endforeach
                                                            @else
                                                                <span class="text-gray-500 text-xs">No specific roles</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Overall Status -->
                                    <div class="bg-blue-50 rounded-lg p-2 sm:p-4 border-l-4 border-blue-400">
                                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
                                            <div>
                                                <h4 class="text-xs sm:text-sm font-semibold text-gray-900">Overall Process Status</h4>
                                                <p class="text-xs sm:text-sm text-gray-600">{{ $mainApproval->process_status }}</p>
                                                @if($mainApproval->process_status === 'APPROVED')
                                                    <p class="text-xs text-green-600 mt-1">✅ Loan is fully approved and ready for disbursement</p>
                                                @elseif($mainApproval->process_status === 'REJECTED')
                                                    <p class="text-xs text-red-600 mt-1">❌ Loan has been rejected and cannot proceed</p>
                                                @else
                                                    <p class="text-xs text-yellow-600 mt-1">⏳ Loan is pending approval</p>
                                                @endif
                                            </div>
                                            <span class="inline-flex items-center px-2 sm:px-3 py-0.5 sm:py-1 rounded-full text-xs font-medium
                                                {{ $mainApproval->process_status === 'APPROVED' ? 'bg-green-100 text-green-800' : 
                                                   ($mainApproval->process_status === 'REJECTED' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ $mainApproval->process_status }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Comments -->
                                    @if($mainApproval->comments)
                                        <div class="bg-blue-50 rounded-lg p-2 sm:p-4 border-l-4 border-blue-400">
                                            <h4 class="text-xs sm:text-sm font-semibold text-gray-900 mb-1 sm:mb-2">Approval Comments</h4>
                                            <p class="text-xs sm:text-sm text-gray-700">{{ $mainApproval->comments }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Approval Records</h3>
                                    <p class="text-gray-500">Approval history will appear here once the loan goes through the approval process.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Loan Repayment Schedule -->
                <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-3 sm:px-6 py-3 sm:py-4 bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Loan Repayment Schedule
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-2 sm:px-6 py-2 sm:py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Pmt</th>
                                    <th class="px-2 sm:px-6 py-2 sm:py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Installment Date</th>
                                    <th class="px-2 sm:px-6 py-2 sm:py-3 text-right font-medium text-gray-500 uppercase tracking-wider">Opening Balance</th>
                                    <th class="px-2 sm:px-6 py-2 sm:py-3 text-right font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                                    <th class="px-2 sm:px-6 py-2 sm:py-3 text-right font-medium text-gray-500 uppercase tracking-wider">Principal</th>
                                    <th class="px-2 sm:px-6 py-2 sm:py-3 text-right font-medium text-gray-500 uppercase tracking-wider">Interest</th>
                                    <th class="px-2 sm:px-6 py-2 sm:py-3 text-right font-medium text-gray-500 uppercase tracking-wider">Closing Balance</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($schedule as $index => $installment)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $index + 1 }}</td>
                                        <td class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-sm text-gray-900">{{ $installment['installment_date'] ?? '-' }}</td>
                                        <td class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format((float)$installment['opening_balance'] ?? 0, 2) }}</td>
                                        <td class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format((float)$installment['payment'] ?? 0, 2) }}</td>
                                        <td class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format((float)$installment['principal'] ?? 0, 2) }}</td>
                                        <td class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format((float)$installment['interest'] ?? 0, 2) }}</td>
                                        <td class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format((float)$installment['closing_balance'] ?? 0, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                                </svg>
                                                <span class="text-lg font-medium">No schedule available</span>
                                                <span class="text-sm">The repayment schedule will be generated after disbursement</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            
                            @isset($footer)
                                <tfoot class="bg-blue-50">
                                    <tr class="font-semibold">
                                        <td class="px-2 sm:px-6 py-2 sm:py-4 text-gray-900">Total</td>
                                        <td class="px-2 sm:px-6 py-2 sm:py-4"></td>
                                        <td class="px-2 sm:px-6 py-2 sm:py-4"></td>
                                        <td class="px-2 sm:px-6 py-2 sm:py-4 text-right text-gray-900">{{ number_format((float)$footer['total_payment'] ?? 0, 2) }}</td>
                                        <td class="px-2 sm:px-6 py-2 sm:py-4 text-right text-gray-900">{{ number_format((float)$footer['total_principal'] ?? 0, 2) }}</td>
                                        <td class="px-2 sm:px-6 py-2 sm:py-4 text-right text-gray-900">{{ number_format((float)$footer['total_interest'] ?? 0, 2) }}</td>
                                        <td class="px-2 sm:px-6 py-2 sm:py-4 text-right text-gray-900">{{ number_format((float)$footer['final_closing_balance'] ?? 0, 2) }}</td>
                                    </tr>
                                </tfoot>
                            @endisset
                        </table>
                    </div>
                </div>

                <!-- Disbursement Section -->
                <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-3 sm:px-6 py-3 sm:py-4 bg-gradient-to-r from-purple-50 to-violet-50 border-b border-gray-200">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                            Disbursement Details
                        </h3>
                    </div>
                    <div class="p-3 sm:p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-6">
                            <!-- Payment Method -->
                            <div class="space-y-2 sm:space-y-4">
                                <h4 class="text-xs sm:text-sm font-semibold text-gray-900">Payment Method</h4>
                                <div class="bg-gray-50 rounded-lg p-2 sm:p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs sm:text-gray-600">Method:</span>
                                        <span class="font-semibold text-xs sm:text-gray-900">{{ $loan->pay_method }}</span>
                                    </div>
                                    <div class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">
                                        @if($loan->pay_method == 'CASH')
                                            Default Deposit Account
                                        @elseif($loan->pay_method == 'CHEQUE')
                                            A new cheque will be generated after disbursement
                                        @elseif($loan->pay_method == 'MOBILE')
                                            {{ $member->phone_number }}
                                        @elseif($loan->pay_method == 'BANK')
                                            {{ $loan->bank }} - {{ $loan->bank_account_number }}
                                        @elseif($loan->pay_method == 'DEPOSIT')
                                            Default Deposit Account
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Disbursement Account -->
                            <div class="space-y-2 sm:space-y-4">
                                <h4 class="text-xs sm:text-sm font-semibold text-gray-900">Disbursement Account</h4>
                                <div class="space-y-3">
                                    @php
                                        // Get bank accounts for disbursement
                                        $bankAccounts = DB::table('bank_accounts')
                                            ->where('status', 'ACTIVE')
                                            ->orderBy('bank_name', 'asc')
                                            ->get();
                                        
                                        // Get selected account details for balance check
                                        $selectedAccount = null;
                                        if ($this->bank_account) {
                                            $selectedAccount = $bankAccounts->firstWhere('internal_mirror_account_number', $this->bank_account);
                                        }
                                    @endphp

                                    <select wire:model="bank_account" class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs sm:text-sm">
                                        <option value="">Select a Disbursing Account</option>
                                        @if($bankAccounts->count() > 0)
                                            @foreach($bankAccounts as $account)
                                                <option value="{{ $account->internal_mirror_account_number }}">
                                                    {{ $account->bank_name }} - {{ $account->account_name }} ({{ $account->account_number }}) : {{ number_format($account->current_balance, 2) }} TZS
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="" disabled>No disbursement accounts available</option>
                                        @endif
                                    </select>
                                    
                                    <!-- Selected Account Details -->
                                    @if($selectedAccount)
                                        <div class="bg-gray-50 rounded-lg p-2 sm:p-4 border border-gray-200">
                                            <div class="flex items-center justify-between mb-1 sm:mb-2">
                                                <h5 class="text-xs sm:text-sm font-semibold text-gray-900">{{ $selectedAccount->bank_name }}</h5>
                                                <span class="text-xs text-gray-500">{{ $selectedAccount->account_number }}</span>
                                            </div>
                                            <p class="text-xs sm:text-sm text-gray-600 mb-2 sm:mb-3">{{ $selectedAccount->account_name }}</p>
                                            
                                            <!-- Balance Information -->
                                            <div class="space-y-1 sm:space-y-2">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-xs sm:text-sm text-gray-600">Current Balance:</span>
                                                    <span class="text-base sm:text-lg font-bold text-gray-900">{{ number_format($selectedAccount->current_balance, 2) }} TZS</span>
                                                </div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-xs sm:text-sm text-gray-600">Loan Amount:</span>
                                                    <span class="text-base sm:text-lg font-bold text-blue-600">{{ number_format($this->approved_loan_value, 2) }} TZS</span>
                                                </div>
                                                <div class="flex justify-between items-center border-t border-gray-200 pt-1 sm:pt-2">
                                                    <span class="text-xs sm:text-sm font-semibold text-gray-700">Remaining Balance:</span>
                                                    <span class="text-base sm:text-lg font-bold {{ ($selectedAccount->current_balance - $this->approved_loan_value) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                        {{ number_format($selectedAccount->current_balance - $this->approved_loan_value, 2) }} TZS
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <!-- Insufficient Funds Warning -->
                                            @if($selectedAccount->current_balance < $this->approved_loan_value)
                                                <div class="mt-2 sm:mt-4 p-2 sm:p-3 bg-red-50 border border-red-200 rounded-lg">
                                                    <div class="flex items-center">
                                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-red-500 mr-1 sm:mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        <div>
                                                            <h6 class="text-xs sm:text-sm font-semibold text-red-800">Insufficient Funds</h6>
                                                            <p class="text-xs sm:text-sm text-red-700">
                                                                The selected account has insufficient balance to disburse this loan. 
                                                                Shortfall: {{ number_format($this->approved_loan_value - $selectedAccount->current_balance, 2) }} TZS
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="mt-2 sm:mt-4 p-2 sm:p-3 bg-green-50 border border-green-200 rounded-lg">
                                                    <div class="flex items-center">
                                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-500 mr-1 sm:mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        <div>
                                                            <h6 class="text-xs sm:text-sm font-semibold text-green-800">Sufficient Funds</h6>
                                                            <p class="text-xs sm:text-sm text-green-700">
                                                                The selected account has sufficient balance to process this disbursement.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                    
                                    <div class="text-xs text-gray-500">
                                        Authorized by: {{ auth()->check() ? auth()->user()->name : 'An Authorized' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Disburse Button -->
                        <div class="mt-4 sm:mt-6 flex justify-end">
                            @php
                                $hasInsufficientFunds = $selectedAccount && $selectedAccount->current_balance < $this->approved_loan_value;
                                $canDisburse = $this->bank_account && !$hasInsufficientFunds;
                            @endphp
                            
                            <button wire:click="disburseLoan('{{$loan->pay_method}}','{{$loan->loan_type_2}}','{{$loan->loan_sub_product}}')" 
                                    @if(!$canDisburse) disabled @endif
                                    class="inline-flex items-center px-4 sm:px-6 py-2 sm:py-3 font-semibold rounded-lg shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2
                                           {{ $canDisburse 
                                              ? 'bg-blue-900 hover:bg-blue-800 text-white focus:ring-blue-500' 
                                              : 'bg-gray-300 text-gray-500 cursor-not-allowed' }} text-xs sm:text-base">
                                <div wire:loading wire:target="disburseLoan" class="mr-1 sm:mr-2">
                                    <div class="w-4 h-4 sm:w-5 sm:h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                </div>
                                <div wire:loading.remove wire:target="disburseLoan" class="mr-1 sm:mr-2">
                                    @if($canDisburse)
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                    @endif
                                </div>
                                @if($canDisburse)
                                    Process Disbursement
                                @elseif(!$this->bank_account)
                                    Select Disbursement Account
                                @elseif($hasInsufficientFunds)
                                    Insufficient Funds
                                @else
                                    Cannot Process Disbursement
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
