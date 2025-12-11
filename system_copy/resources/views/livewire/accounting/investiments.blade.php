<div>
    <div class="bg-gray-100 p-2 flex w-full rounded-md">

        <div class="w-3/4 bg-white rounded-md p-4">
            <h3 class="text-lg font-bold mb-4">Investment Report</h3>
            <div>
                @foreach ($investmentsByType as $investmentType => $investments)
                    <div class="mb-6">
                        <h2 class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ ucfirst($investmentType) }} Investments</h2>
                        <table class="min-w-full bg-white border border-gray-200 text-sm ">
                            <thead>
                            <tr>
                                <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200">Principal Amount</th>
                                <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200">Investment Date</th>
                                @if ($investmentType === 'shares')
                                    <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200">Number of Shares</th>
                                    <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200">Share Price</th>
                                @elseif ($investmentType === 'fdr')
                                    <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200">Interest Rate</th>
                                    <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200">Tenure</th>
                                @elseif ($investmentType === 'bonds')
                                    <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200">Bond Type</th>
                                    <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200">Coupon Rate</th>
                                @elseif ($investmentType === 'mutual_funds')
                                    <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200">Fund Name</th>
                                    <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200">Fund Manager</th>
                                @endif
                                <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200">Accrued Interest</th>
                                <th class="px-4 py-2 text-left bg-blue-100 border border-gray-200">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($investments as $investment)
                                <tr>
                                    <td class="px-4 py-2 border-blue-200  border">{{ number_format($investment['principal_amount'], 2) }} Tsh</td>
                                    <td class="px-4 py-2 border-blue-200  border">{{ \Carbon\Carbon::parse($investment['investment_date'])->toFormattedDateString() }}</td>
                                    @if ($investmentType === 'shares')
                                        <td class="px-4 py-2 border-blue-200  border">{{ $investment['number_of_shares'] }}</td>
                                        <td class="px-4 py-2 border-blue-200  border">{{ number_format($investment['share_price'], 2) }} Tsh</td>
                                    @elseif ($investmentType === 'fdr')
                                        <td class="px-4 py-2 border-blue-200  border">{{ number_format($investment['interest_rate'], 2) }} %</td>
                                        <td class="px-4 py-2 border-blue-200  border">{{ $investment['tenure'] }} days</td>
                                    @elseif ($investmentType === 'bonds')
                                        <td class="px-4 py-2 border-blue-200  border">{{ $investment['bond_type'] }}</td>
                                        <td class="px-4 py-2 border-blue-200  border">{{ number_format($investment['coupon_rate'], 2) }} %</td>
                                    @elseif ($investmentType === 'mutual_funds')
                                        <td class="px-4 py-2 border-blue-200  border">{{ $investment['fund_name'] }}</td>
                                        <td class="px-4 py-2 border-blue-200  border">{{ $investment['fund_manager'] }}</td>
                                    @endif

                                    <td class="px-4 py-2 border-blue-200  border text-sm">
                                        <span class="font-semibold  text-xs">{{ number_format($this->calculateInterest($investment['id']), 2) }} Tsh</span>
                                    </td>
                                    <td class="px-4 py-2 border-blue-200  border text-right flex items-center">
                                        <button wire:click="editInvestment({{ $investment['id'] }})" class="text-indigo-600 hover:text-indigo-900 text-xs">Edit</button>
                                        <button wire:click="deleteInvestment({{ $investment['id'] }})" class="text-red-600 hover:text-red-900 ml-2  text-xs">Delete</button>

                                        @if($investment['status'] != 'liquidated')
                                            @if($investmentType == 'shares')
                                                <input
                                                    type="text"
                                                    wire:model.defer="salePrice"
                                                    placeholder="Enter Sale Price"
                                                    class="border p-2 rounded-md mr-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                >
                                                <button
                                                    wire:click="liquidateShares({{ $investment['id'] }}, $salePrice)"
                                                    class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                >
                                                    Liquidate Shares
                                                </button>
                                            @elseif($investmentType == 'fdr')
                                                <button
                                                    wire:click="liquidateFdr({{ $investment['id'] }})"
                                                    class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500"
                                                >
                                                    Liquidate FDR
                                                </button>
                                            @elseif($investmentType == 'bonds')
                                                <button
                                                    wire:click="liquidateBonds({{ $investment['id'] }})"
                                                    class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500"
                                                >
                                                    Liquidate Bonds
                                                </button>
                                            @elseif($investmentType == 'mutual_funds')
                                                <button
                                                    wire:click="liquidateMutualFunds({{ $investment['id'] }})"
                                                    class="bg-purple-500 text-white px-4 py-2 rounded-md hover:bg-purple-600 focus:outline-none focus:ring-2 focus:ring-purple-500"
                                                >
                                                    Liquidate Mutual Funds
                                                </button>
                                            @endif
                                        @else
                                            <span class="text-gray-500  text-xs">Liquidated</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach


            </div>


        </div>

        <div class="w-1/4 bg-white rounded-md p-4">
            <h2 class="text-xl font-bold mb-4">Investment Manager</h2>


            <div>
                {{$investmentTypeOr}}
                <form wire:submit.prevent="submitx">
                    <!-- General Fields -->
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="investmentTypeOr">Investment Type</label>
                        <select wire:model="investmentTypeOr" id="investmentTypeOr" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Investment Type</option>
                            <option value="shares">Shares</option>
                            <option value="fdr">FDR</option>
                            <option value="bonds">Bonds</option>
                            <option value="mutual_funds">Mutual Funds</option>
                            <option value="real_estate">Real Estate</option>
                            <option value="other">Other Financial Instruments</option>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="principalAmount">Principal Amount</label>
                        <input wire:model="principalAmount" type="number" step="0.01" id="principalAmount" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="investmentDate">Investment Date</label>
                        <input wire:model="investmentDate" type="date" id="investmentDate" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                    </div>

                    {{-- Account Selection - Corrected Flow --}}
                    <div class="col-span-2 bg-gray-50 rounded-lg border border-gray-200 p-4 mb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Account Selection</h3>
                        <p class="text-sm text-gray-600 mb-4">Select where to create the investment account and the other account for double-entry posting</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="parent_account_number" class="block text-sm font-medium text-gray-700 mb-2">
                                    Parent Account (Create Investment Under) *
                                </label>
                                <select wire:model="parent_account_number" id="parent_account_number" 
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="">-- Select Parent Account --</option>
                                    @foreach($parentAccounts as $account)
                                        <option value="{{ $account->account_number }}">
                                            {{ $account->account_number }} - {{ $account->account_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">New investment account will be created under this parent</p>
                                @error('parent_account_number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label for="other_account_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Other Account (Cash/Bank) *
                                </label>
                                <select wire:model="other_account_id" id="other_account_id" 
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="">-- Select Cash/Bank Account --</option>
                                    @foreach($otherAccounts as $account)
                                        <option value="{{ $account->internal_mirror_account_number }}">
                                            {{ $account->bank_name }} - {{ $account->account_number }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Account to be credited (Cash/Bank payment)</p>
                                @error('other_account_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Shares Fields -->
                    @if ($investmentTypeOr === 'shares')
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="numberOfShares">Number of Shares</label>
                            <input wire:model="numberOfShares" type="number" id="numberOfShares" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="sharePrice">Share Price</label>
                            <input wire:model="sharePrice" type="number" step="0.01" id="sharePrice" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="brokerageFees">Brokerage Fees</label>
                            <input wire:model="brokerageFees" type="number" step="0.01" id="brokerageFees" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="dividendRate">Dividend Rate (%)</label>
                            <input wire:model="dividendRate" type="number" step="0.01" id="dividendRate" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="salePrice">Sale Price (Optional)</label>
                            <input wire:model="salePrice" type="number" step="0.01" id="salePrice" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                    @endif

                    <!-- FDR Fields -->
                    @if ($investmentTypeOr === 'fdr')
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="interestRate">Interest Rate (%)</label>
                            <input wire:model="interestRate" type="number" step="0.01" id="interestRate" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="tenure">Tenure (in years/months)</label>
                            <input wire:model="tenure" type="number" id="tenure" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="maturityDate">Maturity Date</label>
                            <input wire:model="maturityDate" type="date" id="maturityDate" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="penalty">Penalty for Early Withdrawal (Optional)</label>
                            <input wire:model="penalty" type="number" step="0.01" id="penalty" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                    @endif

                    <!-- Bonds Fields -->
                    @if ($investmentTypeOr === 'bonds')
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="bondType">Bond Type</label>
                            <input wire:model="bondType" type="text" id="bondType" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="couponRate">Coupon Rate (%)</label>
                            <input wire:model="couponRate" type="number" step="0.01" id="couponRate" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="maturityDate">Maturity Date</label>
                            <input wire:model="maturityDate" type="date" id="maturityDate" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="bondYield">Bond Yield (Optional)</label>
                            <input wire:model="bondYield" type="number" step="0.01" id="bondYield" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                    @endif

                    <!-- Mutual Funds Fields -->
                    @if ($investmentTypeOr === 'mutual_funds')
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="fundType">Fund Type</label>
                            <input wire:model="fundType" type="text" id="fundType" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="nav">NAV</label>
                            <input wire:model="nav" type="number" step="0.01" id="nav" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="expenseRatio">Expense Ratio (%)</label>
                            <input wire:model="expenseRatio" type="number" step="0.01" id="expenseRatio" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="investmentDate">Investment Date</label>
                            <input wire:model="investmentDate" type="date" id="investmentDate" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                    @endif

                    <!-- Real Estate Fields -->
                    @if ($investmentTypeOr === 'real_estate')
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="propertyType">Property Type</label>
                            <input wire:model="propertyType" type="text" id="propertyType" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="propertyValue">Property Value</label>
                            <input wire:model="propertyValue" type="number" step="0.01" id="propertyValue" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="rentalIncome">Rental Income (Optional)</label>
                            <input wire:model="rentalIncome" type="number" step="0.01" id="rentalIncome" class="w-full p-2 border bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                    @endif

                    <!-- Buttons -->
                    <div class="mt-4">
                        <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded-lg hover:bg-blue-900">
                            {{ $editMode ? 'Update Investment' : 'Add Investment' }}
                        </button>
                    </div>
                </form>

            </div>












            <div class="mb-4">
                @if (session()->has('message'))
                    <div class="bg-green-200 text-green-800 p-2 rounded mb-4">
                        {{ session('message') }}
                    </div>
                @endif
            </div>

        </div>



    </div>


</div>
