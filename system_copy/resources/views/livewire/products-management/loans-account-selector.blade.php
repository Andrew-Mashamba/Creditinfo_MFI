<!-- Account Mapping Tab with Hierarchical Selectors -->
@if($activeTab === 'accounts')
<div id="accounts-tab" class="tab-pane">
    <div class="space-y-6">
        <!-- Primary Accounts -->
        <div class="bg-gradient-to-r from-blue-50 to-cyan-50 p-6 rounded-xl border border-blue-100">
            <h3 class="text-xl font-bold text-blue-700 mb-6 flex items-center">
                <svg class="w-6 h-6 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Account Mapping & Configuration
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Loan Product Account (Asset Accounts) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Loan Product Account</label>
                    <div class="space-y-2">
                        @php
                            $loanProductLevel2 = \App\Models\Account::where('type', 'asset_accounts')
                                ->where('account_level', '2')
                                ->whereIn('account_number', ['010110001200']) // LOAN PORTFOLIO
                                ->first();
                        @endphp
                        
                        @if($loanProductLevel2)
                            <select wire:model.lazy="selectedLoanProductLevel2"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 text-sm">
                                <option value="">Select Category</option>
                                <option value="{{ $loanProductLevel2->account_number }}" 
                                        @if($selectedLoanProductLevel2 == $loanProductLevel2->account_number) selected @endif>
                                    {{ $loanProductLevel2->account_name }}
                                </option>
                            </select>
                        @endif
                        
                        <select wire:model.defer="form.loan_product_account" 
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200">
                            <option value="">Select Loan Account</option>
                            @php
                                $loanAccounts = \App\Models\Account::where('type', 'asset_accounts')
                                    ->where('account_level', '3')
                                    ->where('category_code','1200')
                                    ->orderBy('account_name')
                                    ->get();
                            @endphp
                            @foreach($loanAccounts as $account)
                                <option value="{{ $account->account_number }}">{{ $account->account_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('form.loan_product_account') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <!-- Interest Collection Account (Income Accounts) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Interest Collection Account <span class="text-red-500">*</span></label>
                    <div class="space-y-2">
                        @php
                            $interestLevel2 = \App\Models\Account::where('type', 'income_accounts')
                                ->where('account_level', '2')
                                ->whereIn('account_number', ['010140004000']) // INTEREST INCOME
                                ->first();
                        @endphp
                        
                        @if($interestLevel2)
                            <select wire:model.lazy="selectedInterestLevel2" 
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 text-sm">
                                <option value="">Select Category</option>
                                <option value="{{ $interestLevel2->account_number }}">{{ $interestLevel2->account_name }}</option>
                            </select>
                        @endif
                        
                        <select wire:model.defer="form.collection_account_loan_interest" 
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200">
                            <option value="">Select Interest Account</option>
                            @php
                                $interestAccounts = \App\Models\Account::where('type', 'income_accounts')
                                    ->where('account_level', '3')
                                    ->where('account_number', 'like', '010140004000%')
                                    ->orderBy('account_name')
                                    ->get();
                            @endphp
                            @foreach($interestAccounts as $account)
                                <option value="{{ $account->account_number }}">{{ $account->account_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('form.collection_account_loan_interest') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <!-- Principal Collection Account (Asset Accounts) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Principal Collection Account <span class="text-red-500">*</span></label>
                    <div class="space-y-2">
                        @php
                            $principalLevel2 = \App\Models\Account::where('type', 'asset_accounts')
                                ->where('account_level', '2')
                                ->whereIn('account_number', ['010110001200', '010110001000']) // LOAN PORTFOLIO or CASH
                                ->get();
                        @endphp
                        
                        <select wire:model.lazy="selectedPrincipalLevel2"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 text-sm">
                            <option value="">Select Category</option>
                            @foreach($principalLevel2 as $level2)
                                <option value="{{ $level2->account_number }}" 
                                        @if($selectedPrincipalLevel2 == $level2->account_number) selected @endif>
                                    {{ $level2->account_name }}
                                </option>
                            @endforeach
                        </select>
                        
                        <select wire:model.defer="form.collection_account_loan_principle" 
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200">
                            <option value="">Select Principal Account</option>
                            @php
                                $principalAccounts = \App\Models\Account::where('type', 'asset_accounts')
                                    ->where('account_level', '3')
                                    ->where('category_code','1200');
                                if (!empty($selectedPrincipalLevel2)) {
                                    $principalAccounts = $principalAccounts;
                                } else {
                                    $principalAccounts = $principalAccounts->whereIn('account_number', ['0101100012001210', '0101100012001220', '0101100012001240']);
                                }
                                $principalAccounts = $principalAccounts->orderBy('account_name')->get();
                            @endphp
                            @foreach($principalAccounts as $account)
                                <option value="{{ $account->account_number }}">{{ $account->account_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('form.collection_account_loan_principle') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <!-- Charges Collection Account (Income Accounts) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Charges Collection Account <span class="text-red-500">*</span></label>
                    <div class="space-y-2">
                        @php
                            $chargesLevel2 = \App\Models\Account::where('type', 'income_accounts')
                                ->where('account_level', '2')
                                ->whereIn('account_number', ['010140004100', '010140004200']) // LOAN FEES AND CHARGES, SERVICE FEES
                                ->get();
                        @endphp
                        
                        <select wire:model.lazy="selectedChargesLevel2"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 text-sm">
                            <option value="">Select Category</option>
                            @foreach($chargesLevel2 as $level2)
                                <option value="{{ $level2->account_number }}" 
                                        @if($selectedChargesLevel2 == $level2->account_number) selected @endif>
                                    {{ $level2->account_name }}
                                </option>
                            @endforeach
                        </select>
                        
                        <select wire:model.defer="form.collection_account_loan_charges" 
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200">
                            <option value="">Select Charges Account</option>
                            @php
                                $chargesAccounts = \App\Models\Account::where('type', 'income_accounts')
                                    ->where('account_level', '3');
                                if (!empty($selectedChargesLevel2)) {
                                    $chargesAccounts = $chargesAccounts->where('account_number', 'like', $selectedChargesLevel2 . '%');
                                } else {
                                    $chargesAccounts = $chargesAccounts->whereIn('account_number', ['0101400041004120', '0101400041004130', '0101400042004215']);
                                }
                                $chargesAccounts = $chargesAccounts->orderBy('account_name')->get();
                            @endphp
                            @foreach($chargesAccounts as $account)
                                <option value="{{ $account->account_number }}">{{ $account->account_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('form.collection_account_loan_charges') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <!-- Penalties Collection Account (Income Accounts) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Penalties Collection Account <span class="text-red-500">*</span></label>
                    <div class="space-y-2">
                        @php
                            $penaltiesLevel2 = \App\Models\Account::where('type', 'income_accounts')
                                ->where('account_level', '2')
                                ->whereIn('account_number', ['010140004100']) // LOAN FEES AND CHARGES
                                ->first();
                        @endphp
                        
                        @if($penaltiesLevel2)
                            <select wire:model.lazy="selectedPenaltiesLevel2" 
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 text-sm">
                                <option value="">Select Category</option>
                                <option value="{{ $penaltiesLevel2->account_number }}">{{ $penaltiesLevel2->account_name }}</option>
                            </select>
                        @endif
                        
                        <select wire:model.defer="form.collection_account_loan_penalties" 
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200">
                            <option value="">Select Penalties Account</option>
                            @php
                                $penaltiesAccounts = \App\Models\Account::where('type', 'income_accounts')
                                    ->where('account_level', '3')
                                    ->whereIn('account_number', ['0101400041004110']) // LATE PAYMENT FEES
                                    ->orderBy('account_name')
                                    ->get();
                            @endphp
                            @foreach($penaltiesAccounts as $account)
                                <option value="{{ $account->account_number }}">{{ $account->account_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('form.collection_account_loan_penalties') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <!-- Insurance Account (Liability Accounts) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Insurance Account</label>
                    <div class="space-y-2">
                        @php
                            $insuranceLevel2 = \App\Models\Account::where('type', 'capital_accounts')
                                ->where('account_level', '2')
                              
                                ->get();
                        @endphp
                        
                        <select wire:model.lazy="selectedInsuranceLevel2"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 text-sm">
                            <option value="">Select Category</option>
                            @foreach($insuranceLevel2 as $level2)
                                <option value="{{ $level2->account_number }}" 
                                        @if($selectedInsuranceLevel2 == $level2->account_number) selected @endif>
                                    {{ $level2->account_name }}
                                </option>
                            @endforeach
                        </select>
                        
                        <select wire:model.defer="form.insurance_account" 
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200">
                            <option value="">Select Insurance Account</option>
                            @php
                                $insuranceAccounts = \App\Models\Account::where('type', 'capital_accounts')
                                    ->where('account_level', '3');
                                if (!empty($selectedInsuranceLevel2)) {
                                    $insuranceAccounts = $insuranceAccounts;
                                } else {
                                    $insuranceAccounts = $insuranceAccounts->whereIn('account_number', ['0101200025002530', '0101200029002920']);
                                }
                                $insuranceAccounts = $insuranceAccounts->orderBy('account_name')->get();
                            @endphp
                            @foreach($insuranceAccounts as $account)
                                <option value="{{ $account->account_number }}">{{ $account->account_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('form.insurance_account') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>
                
            </div>
        </div>
    </div>
</div>
@endif