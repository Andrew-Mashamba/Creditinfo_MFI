<div class="space-y-4">
    <!-- Alert Messages -->
    @if (session()->has('message'))
        <div class="bg-green-50 border border-green-200 p-3 rounded text-xs" role="alert">
            <div class="flex items-center">
                <span class="text-green-800 font-medium">Success:</span>
                <span class="text-green-700 ml-1">{{ session('message') }}</span>
            </div>
        </div>
    @endif

    @if (session('alert-class') == 'alert-warning')
        <div class="bg-yellow-50 border border-yellow-200 p-3 rounded text-xs" role="alert">
            <div class="flex items-center">
                <span class="text-yellow-800 font-medium">Warning:</span>
                <span class="text-yellow-700 ml-1">{{ session('loan_message') }}</span>
            </div>
        </div>
    @endif

    @foreach($this->member as $currentClient)
    <div class="space-y-4">
        <!-- Client Profile Header -->
        <div class="w-full bg-white border border-gray-200 rounded-lg">
            <div class="p-4 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">Client Profile</h3>
            </div>
            
            <div class="p-4">
                <div class="flex items-center space-x-4">
                    <!-- Profile Photo -->
                    <div class="flex-shrink-0">
                        @if($currentClient->profile_photo_path)
                            <img class="w-16 h-16 rounded-full object-cover border border-gray-200"
                                 src="{{asset($currentClient->profile_photo_path)}}"
                                 alt="Profile photo of {{$currentClient->first_name}} {{$currentClient->last_name}}"/>
                        @else
                            <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center border border-gray-200">
                                <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        @endif
                    </div>

                    <!-- Client Basic Info -->
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-gray-900">
                            {{$currentClient->first_name}} {{$currentClient->middle_name}} {{$currentClient->last_name}}
                        </h4>
                        <p class="text-xs text-gray-600">{{$currentClient->address}}</p>
                        
                        <!-- Quick Stats -->
                        <div class="flex space-x-4 mt-2">
                            <div class="flex items-center space-x-1">
                                <span class="text-xs text-gray-600">Member #:</span>
                                <span class="text-xs font-medium text-blue-900">{{$currentClient->member_number}}</span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <span class="text-xs text-gray-600">Status:</span>
                                <span class="text-xs font-medium text-blue-900">Active</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Client Information Grid -->
        <div class="w-full bg-white border border-gray-200 rounded-lg">
            <div class="p-4 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">Personal Information</h3>
            </div>
            
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @php
                    // Get credit score data from the service (already loaded in component)
                    $creditScore = $this->creditScoreData ?? null;
                    $creditScoreValue = $creditScore['score'] ?? 500;
                    $creditScoreGrade = $creditScore['grade'] ?? 'E';
                    $creditScoreRisk = $creditScore['risk_description'] ?? 'Very High Risk - No Data';
                    $creditScoreTrend = $creditScore['trend'] ?? 'Stable';
                    $creditScoreDate = $creditScore['date'] ?? now();

                    // Get client information data from the service (already loaded in component)
                    $clientInfo = $this->clientInfoData ?? null;
                    $basicInfo = $clientInfo['basic_info'] ?? [];
                    $contactInfo = $clientInfo['contact_info'] ?? [];
                    $employmentInfo = $clientInfo['employment_info'] ?? [];
                    $financialInfo = $clientInfo['financial_info'] ?? [];
                    $riskIndicators = $clientInfo['risk_indicators'] ?? [];
                    $demographics = $clientInfo['demographics'] ?? [];
                    $statusIndicators = $clientInfo['status_indicators'] ?? [];
                    @endphp

                    <!-- Personal Details -->
                    <div>
                        @include('livewire.loans.sections.client-information')
                    </div>

                    <!-- Loan History -->
                    <div>
                        <h4 class="text-xs font-medium text-gray-700 mb-2">Loan History</h4>
                        <div class="overflow-x-auto">
                            @php
                                $loans = DB::table('loans')->where('client_number', $this->member_number)->get();
                            @endphp
                            
                            @if($loans->count() > 0)
                                <table class="w-full text-xs border border-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-2 py-1 text-left border-b border-r border-gray-200">Product</th>
                                            <th class="px-2 py-1 text-right border-b border-r border-gray-200">Amount</th>
                                            <th class="px-2 py-1 text-left border-b border-r border-gray-200">Status</th>
                                            <th class="px-2 py-1 text-left border-b border-gray-200">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($loans as $loan)
                                            <tr class="border-b">
                                                <td class="px-2 py-1 border-r border-gray-200">
                                                    {{DB::table('loan_sub_products')->where('sub_product_id', $loan->loan_sub_product)->value('sub_product_name')}}
                                                </td>
                                                <td class="px-2 py-1 text-right font-semibold text-blue-900 border-r border-gray-200">
                                                    {{number_format($loan->principle)}} TZS
                                                </td>
                                                <td class="px-2 py-1 text-left border-r border-gray-200">
                                                    <span class="text-xs font-medium text-gray-900">
                                                        {{$loan->status}}
                                                    </span>
                                                </td>
                                                <td class="px-2 py-1 text-left text-gray-600">
                                                    {{$loan->created_at ? \Carbon\Carbon::parse($loan->created_at)->format('M d, Y') : 'N/A'}}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="text-center py-4">
                                    <p class="text-xs text-gray-500">No loan history available</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

