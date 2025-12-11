<div class="py-4 px-5">

    <div class="flex justify-between">
        <div class="flex justify-between gap-4">
            <div>
                <label for="name" class="block mb-2 sm:text-xs font-medium text-gray-900
                                       ">Institution Name</label>
                <input wire:model.bounce="name" type="text" id="name" class="block w-full p-2 text-gray-900 border
                                        border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500

                                          ">
                @error('name')
                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                    <p>Share Product Name is mandatory and should be more than three characters.</p>
                </div>
                @enderror
            </div>
            <div>
                <label for="prefix" class="block mb-2 sm:text-xs font-medium text-gray-900
                                       ">Institution number</label>
                <input wire:model.bounce="institution_id" type="text" id="institution_id" class="disabled w-14 block w-full
                                         p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs
                                         focus:ring-blue-500 focus:border-blue-500

                                         ">
                @error('institution_id')
                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                    <p>Prefix is mandatory and should be more than three characters.</p>
                </div>
                @enderror
            </div>
        </div>
        <div>
            <label class="block mb-2 sm:text-xs font-medium text-gray-900">Institution Status</label>
            <label for="institution_status" class="relative inline-flex items-center mb-2 cursor-pointer">
                <input type="checkbox" wire:model="institution_status" id="institution_status" class="sr-only peer" >
                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300
                                          peer-checked:after:translate-x-full
                                         peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5
                                         after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full
                                          after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-900"></div>
                <span class="ml-3 text-sm font-medium text-gray-900">Active</span>
            </label>

        </div>
    </div>


    <x-jet-section-border />

    <div class="flex gap-2">
        <div class="w-1/4" >
            <label for="countries" class="block mb-2 text-sm  font-medium text-gray-900 ">Currency</label>
            <select wire:model="currency" id="countries" class="mb-4 bg-gray-50 border border-gray-300 text-gray-900 sm:text-xs   rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5    ">
                @foreach($this->currencies as $currency)
                    <option value="{{ $currency->currency_id }}" >{{ $currency->currency_name }}</option>
                @endforeach
            </select>



                <x-jet-section-border />

                <div>
                    <label for="total_shares" class="block mb-2 sm:text-xs font-medium text-gray-900
                                       ">Region</label>
                    <input wire:model.bounce="region" type="text" id="region" class="sm:text-xs block w-full p-2 text-gray-900 border
                                        border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500

                                          ">
                    @error('region')
                    <div class="border border-red-400 rounded-b bg-red-100 px-4 sm:text-xs py-3 text-red-700 mt-1">
                        <p>Region is mandatory and should be more than three characters.</p>
                    </div>
                    @enderror
                </div>

                <x-jet-section-border />

                <div>
                    <label for="wilaya" class="block mb-2 sm:text-xs font-medium text-gray-900
                                       ">Wilaya</label>
                    <input wire:model.bounce="wilaya" type="text" id="wilaya" class="sm:text-xs block w-full p-2 text-gray-900 border
                                        border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500

                                          ">
                    @error('wilaya')
                    <div class="border border-red-400 rounded-b bg-red-100 px-4 sm:text-xs py-3 text-red-700 mt-1">
                        <p>Total Shares is mandatory and should be more than three characters.</p>
                    </div>
                    @enderror
                </div>

                <x-jet-section-border />

                <div>
                    <label for="phone_number" class="block mb-2 sm:text-xs font-medium text-gray-900
                                       ">Phone number</label>
                    <input wire:model.bounce="phone_number" type="text" id="phone_number" class="sm:text-xs block w-full p-2 text-gray-900 border
                                        border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500

                                          ">
                    @error('phone_number')
                    <div class="border border-red-400 rounded-b bg-red-100 px-4 sm:text-xs py-3 text-red-700 mt-1">
                        <p>Shares Per Client is mandatory and should be more than three characters.</p>
                    </div>
                    @enderror
                </div>

                <x-jet-section-border />

                <div>
                    <label for="email" class="block mb-2 sm:text-xs font-medium text-gray-900
                                       ">Email</label>
                    <input wire:model.bounce="email" type="email" id="email" class="sm:text-xs block w-full p-2 text-gray-900 border
                                        border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500

                                          ">
                    @error('email')
                    <div class="border border-red-400 rounded-b bg-red-100 px-4 sm:text-xs py-3 text-red-700 mt-1">
                        <p>Shares Per Client is mandatory and should be more than three characters.</p>
                    </div>
                    @enderror
                </div>



            <x-jet-section-border />

            <div>
                <label for="admin" class="block mb-2 sm:text-xs font-medium text-gray-900
                                       ">Administrator</label>
                <input wire:model.bounce="admin" type="text" id="admin" class="sm:text-xs block w-full p-2 text-gray-900 border
                                        border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500

                                          ">
                @error('admin')
                <div class="border border-red-400 rounded-b bg-red-100 px-4 sm:text-xs py-3 text-red-700 mt-1">
                    <p>Administrator is mandatory and should be more than three characters.</p>
                </div>
                @enderror
            </div>




        </div>

        <div class="w-1/4 border-l pl-2">


            <label for="application_fee" class="block mb-2 sm:text-xs font-medium text-gray-900">Application Fee</label>
            <input wire:model.bounce="application_fee" type="number" id="application_fee" class="block w-full p-2 text-gray-900 border
                                         border-gray-300 rounded-lg bg-gray-50
                                        sm:text-xs focus:ring-blue-500 focus:border-blue-500
                                          ">
            @error('application_fee')
            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                <p>Charges is mandatory and should be more than three characters.</p>
            </div>
            @enderror

            <label for="fees_holding_account" class="block mb-2 sm:text-xs font-medium text-gray-900">Fees Holding Account</label>
            <select wire:model="fees_holding_account" id="fees_holding_account" class="mb-4 bg-gray-50 border border-gray-300 text-gray-900 sm:text-xs   rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5    ">
                <option value="" >Select</option>
                @foreach(App\Models\AccountsModel::where('institution_number',Session::get('institution'))->get() as $account)
                    <option value="{{ $account->account_number }}" >{{ $account->account_name }}</option>
                @endforeach
            </select>

            <x-jet-section-border />


            <label for="minimum_shares" class="block mb-2 sm:text-xs font-medium text-gray-900">Minimum shares</label>
            <input wire:model.bounce="minimum_shares" type="number" id="minimum_shares" class="block w-full p-2 text-gray-900 border
                                         border-gray-300 rounded-lg bg-gray-50
                                        sm:text-xs focus:ring-blue-500 focus:border-blue-500
                                          ">
            @error('minimum_shares')
            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                <p>Charges is mandatory and should be more than three characters.</p>
            </div>
            @enderror

            <x-jet-section-border />

            <label for="initial_shares" class="block mb-2 sm:text-xs font-medium text-gray-900">Mandatory shares</label>
            <input wire:model.bounce="minimum_shares" type="number" id="minimum_shares" class="block w-full p-2 text-gray-900 border
                                         border-gray-300 rounded-lg bg-gray-50
                                        sm:text-xs focus:ring-blue-500 focus:border-blue-500
                                          ">
            @error('initial_shares')
            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                <p> is mandatory and should be more than one characters.</p>
            </div>
            @enderror

            <x-jet-section-border />


            <label for="value_per_share" class="block mb-2 sm:text-xs font-medium text-gray-900">Value per share</label>
            <input wire:model.bounce="value_per_share" type="number" id="value_per_share" class="block w-full p-2 text-gray-900 border
                                         border-gray-300 rounded-lg bg-gray-50
                                        sm:text-xs focus:ring-blue-500 focus:border-blue-500
                                          ">
            @error('value_per_share')
            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                <p>Charges is mandatory and should be more than three characters.</p>
            </div>
            @enderror

            <x-jet-section-border />


            <label for="initial_shares" class="block mb-2 sm:text-xs font-medium text-gray-900"> initia _shares </label>
            <input wire:model.bounce="initial_shares" type="number" id="initial_shares" class="block w-full p-2 text-gray-900 border
                                         border-gray-300 rounded-lg bg-gray-50
                                        sm:text-xs focus:ring-blue-500 focus:border-blue-500
                                          ">
            @error('initial_shares')
            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                <p>Charges is mandatory and should be more than three characters.</p>
            </div>
            @enderror

            <label for="temp_shares_holding_account" class="block mb-2 sm:text-xs font-medium text-gray-900">Temp Shares Holding Account</label>
            <select wire:model="temp_shares_holding_account" id="temp_shares_holding_account" class="mb-4 bg-gray-50 border border-gray-300 text-gray-900 sm:text-xs   rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5    ">
                <option value="" >Select</option>
                @foreach(App\Models\AccountsModel::where('institution_number',Session::get('institution'))->get() as $account)
                    <option value="{{ $account->account_number }}" >{{ $account->account_name }}</option>
                @endforeach
            </select>

            <x-jet-section-border />

            <label for="deposit" class="relative inline-flex items-center mb-2 cursor-pointer">
                <input type="checkbox" wire:model="deposit" id="deposit" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300
                                          peer-checked:after:translate-x-full
                                         peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5
                                         after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full
                                         after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-900"></div>
                <span class="ml-3 text-sm font-medium text-gray-900">Deposit</span>
            </label>

            @if($this->deposit)
                <label for="deposit_charge" class="block mb-2 sm:text-xs font-medium text-gray-900">Charges</label>
                <input wire:model.bounce="deposit_charge" type="number" id="deposit_charge" class="block w-full p-2 text-gray-900 border
                                         border-gray-300 rounded-lg bg-gray-50
                                        sm:text-xs focus:ring-blue-500 focus:border-blue-500
                                          ">
                @error('deposit_charge')
                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                    <p>Charges is mandatory and should be more than three characters.</p>
                </div>
                @enderror
                <div class="flex justify-between gap-2">
                    <div class="w-1/2">
                        <label for="deposit_charge_min_value" class="block mb-2 sm:text-xs font-medium text-gray-900">Min Value</label>
                        <input wire:model.bounce="deposit_charge_min_value" type="number" id="deposit_charge_min_value" class="w-20 p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs
                                                 focus:ring-blue-500 focus:border-blue-500
                                                  ">
                        @error('deposit_charge_min_value')
                        <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                            <p>Min Value is mandatory and should be more than three characters.</p>
                        </div>
                        @enderror
                    </div>
                    <div class="w-1/2">
                        <label for="deposit_charge_max_value" class="block mb-2 sm:text-xs font-medium text-gray-900">Max Value</label>
                        <input wire:model.bounce="deposit_charge_max_value" type="number" id="deposit_charge_max_value" class="w-20 p-2 text-gray-900 border
                                                 border-gray-300 rounded-lg bg-gray-50 sm:text-xs
                                                focus:ring-blue-500 focus:border-blue-500
                                                  ">
                        @error('deposit_charge_max_value')
                        <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                            <p>Max Value is mandatory and should be more than three characters.</p>
                        </div>
                        @enderror
                    </div>
                </div>

            @else
            @endif


            <x-jet-section-border />






            <label for="withdraw" class="relative inline-flex items-center mb-2 cursor-pointer">
                <input type="checkbox" wire:model="withdraw" id="withdraw" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300
                                          peer-checked:after:translate-x-full
                                         peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5
                                         after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full
                                         after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-900"></div>
                <span class="ml-3 text-sm font-medium text-gray-900">Withdraw</span>
            </label>

            @if($this->withdraw)
                <label for="withdraw_charge" class="block mb-2 sm:text-xs font-medium text-gray-900">Charges</label>
                <input wire:model.bounce="withdraw_charge" type="number" id="withdraw_charge" class="block w-full p-2 text-gray-900 border
                                         border-gray-300 rounded-lg bg-gray-50
                                        sm:text-xs focus:ring-blue-500 focus:border-blue-500
                                          ">
                @error('withdraw_charge')
                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                    <p>Charges is mandatory and should be more than three characters.</p>
                </div>
                @enderror
                <div class="flex justify-between gap-2">
                    <div class="w-1/2">
                        <label for="withdraw_charge_min_value" class="block mb-2 sm:text-xs font-medium text-gray-900">Min Value</label>
                        <input wire:model.bounce="withdraw_charge_min_value" type="number" id="withdraw_charge_min_value" class="w-20 p-2 text-gray-900 border
                                                 border-gray-300 rounded-lg bg-gray-50 sm:text-xs
                                                 focus:ring-blue-500 focus:border-blue-500
                                                  ">
                        @error('withdraw_charge_min_value')
                        <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                            <p>Min Value is mandatory and should be more than three characters.</p>
                        </div>
                        @enderror
                    </div>
                    <div class="w-1/2">
                        <label for="withdraw_charge_max_value" class="block mb-2 sm:text-xs font-medium text-gray-900">Max Value</label>
                        <input wire:model.bounce="withdraw_charge_max_value" type="number" id="withdraw_charge_max_value" class="w-20 p-2 text-gray-900 border
                                                 border-gray-300 rounded-lg bg-gray-50 sm:text-xs
                                                focus:ring-blue-500 focus:border-blue-500
                                                  ">
                        @error('withdraw_charge_max_value')
                        <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                            <p>Max Value is mandatory and should be more than three characters.</p>
                        </div>
                        @enderror
                    </div>
                </div>

            @else
            @endif



            <label for="interest" class="relative inline-flex items-center mb-2 cursor-pointer">
                <input type="checkbox" wire:model="interest" id="interest" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300
                                          peer-checked:after:translate-x-full
                                         peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5
                                         after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full
                                         after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-900"></div>
                <span class="ml-3 text-sm font-medium text-gray-900">Interest</span>
            </label>
            @if($this->interest)
                <div class="flex justify-between gap-2">
                    <div class="w-1/2">
                        <label class="block mb-2 sm:text-xs font-medium text-gray-900">Interest Value</label>
                        <div class="flex">
                                                  <span class="inline-flex items-center px-3 sm:text-xs text-gray-900 bg-gray-200 border border-r-0 border-gray-300 rounded-l-md
                                                  ">
                                                    %
                                                  </span>
                            <input type="number" wire:model.bounce="interest_value" id="interest_value" class="w-12 p-2 text-gray-900 border border-gray-300
                                                    rounded-r-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500
                                                       ">
                        </div>
                    </div>

                    <div class="w-1/2">
                        <label for="interest_tenure" class="block mb-2 sm:text-xs font-medium text-gray-900">Tenure (Months)</label>
                        <input wire:model.bounce="interest_tenure" type="number" id="interest_tenure" class="w-20 p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500    ">
                        @error('interest_tenure')
                        <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                            <p>Tenure (Months) is mandatory and should be more than three characters.</p>
                        </div>
                        @enderror
                    </div>
                </div>
            @else
            @endif



            <x-jet-section-border />



            <label for="maintenance_fees" class="relative inline-flex items-center mb-2 cursor-pointer">
                <input type="checkbox" wire:model="maintenance_fees" id="maintenance_fees" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300
                                          peer-checked:after:translate-x-full
                                         peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5
                                         after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full
                                         after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-900"></div>
                <span class="ml-3 text-sm font-medium text-gray-900">Maintenance fees</span>
            </label>
            @if($this->maintenance_fees)
                <div class="">

                    <label for="maintenance_fees_value" class="block mb-2 sm:text-xs font-medium text-gray-900">Fees per month</label>
                    <input type="text" wire:model.bounce="maintenance_fees_value" id="maintenance_fees_value" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500    ">
                    @error('maintenance_fees_value')
                    <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                        <p>Maintenance fees is mandatory and should be more than three characters.</p>
                    </div>
                    @enderror
                </div>
            @else
            @endif

            <x-jet-section-border />


            <div>
                <label for="profit_account" class="block mb-2 text-sm font-medium text-gray-900">Collection/Profit Account</label>
                <input type="text" wire:model.bounce="profit_account"  id="profit_account" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500    ">
                @error('profit_account')
                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                    <p>Profit Account is mandatory and should be more than three characters.</p>
                </div>
                @enderror
            </div>

            <x-jet-section-border />
            <label for="dormant_period_months" class="block mb-2 text-sm font-medium text-gray-900">Dormant Period (months)</label>
            <p class="text-xs text-gray-500 mb-2">Members will be marked as DORMANT if all their accounts have no activity for this period.</p>
            <fieldset>
                <div class="flex items-center mb-4">
                    <input wire:model="dormant_period_months" id="dormant_six_months"
                           type="radio" name="dormant_period_months" value="6" class="w-4 h-4 border-gray-300 focus:ring-2
                                                    focus:ring-blue-300 ">
                    <label for="dormant_six_months" class="block ml-2 text-sm font-medium text-gray-900">
                        6 months of inactivity
                    </label>
                </div>

                <div class="flex items-center mb-4">
                    <input wire:model="dormant_period_months" id="dormant_twelve_months"
                           type="radio" name="dormant_period_months" value="12" class="w-4 h-4 border-gray-300 focus:ring-2
                                                   focus:ring-blue-300 ">
                    <label for="dormant_twelve_months" class="block ml-2 text-sm font-medium text-gray-900">
                        12 months of inactivity
                    </label>
                </div>

                <div class="flex items-center mb-4">
                    <input wire:model="dormant_period_months" id="dormant_eighteen_months"
                           type="radio" name="dormant_period_months" value="18" class="w-4 h-4 border-gray-300 focus:ring-2
                                                   focus:ring-blue-300">
                    <label for="dormant_eighteen_months" class="block ml-2 text-sm font-medium text-gray-900">
                        18 months of inactivity
                    </label>
                </div>

                <div class="flex items-center mb-4">
                    <input wire:model="dormant_period_months" id="dormant_twenty_four_months"
                           type="radio" name="dormant_period_months" value="24" class="w-4 h-4 border-gray-300 focus:ring-2
                                                   focus:ring-blue-300 ">
                    <label for="dormant_twenty_four_months" class="block ml-2 text-sm font-medium text-gray-900">
                        24 months of inactivity
                    </label>
                </div>
            </fieldset>

        </div>

        <div class="w-1/4 border-l pl-2">

            <label for="ledger_fees" class="relative inline-flex items-center mb-2 cursor-pointer">
                <input type="checkbox" wire:model="ledger_fees" id="ledger_fees" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300
                                          peer-checked:after:translate-x-full
                                         peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5
                                         after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full
                                         after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-900"></div>
                <span class="ml-3 text-sm font-medium text-gray-900">Ledger fees</span>
            </label>
            @if($this->ledger_fees)
                <div class="">

                    <label for="maintenance_fees_value" class="block mb-2 sm:text-xs font-medium text-gray-900">Fees per month</label>
                    <input type="text" wire:model.bounce="ledger_fees_value" id="ledger_fees_value" class="block w-full p-2 text-gray-900 border
                                            border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500
                                                ">
                    @error('ledger_fees_value')
                    <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                        <p>Ledger fees is mandatory and should be more than three characters.</p>
                    </div>
                    @enderror
                </div>
            @else
            @endif

            <x-jet-section-border />



            <fieldset>
                <legend class="sr-only">Checkbox variants</legend>

                <div class="flex items-center mb-4">
                    <input wire:model="create_during_registration" id="create_during_registration" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500  focus:ring-2" >
                    <label for="create_during_registration" class="ml-2 text-sm font-medium text-gray-900">Account is created during member registration</label>
                </div>

                <div class="flex items-center mb-4">
                    <input wire:model="activated_by_lower_limit" id="activated_by_lower_limit" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500  focus:ring-2">
                    <label for="activated_by_lower_limit" class="ml-2 text-sm font-medium text-gray-900">Account is activated when the lower limit is reached</label>
                </div>

                <div class="flex items-center mb-4">
                    <input wire:model="requires_approval" id="requires_approval" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500  focus:ring-2">
                    <label for="requires_approval" class="ml-2 text-sm font-medium text-gray-900">Requires approval to be activated</label>
                </div>

                <div class="flex items-center mb-4">
                    <input wire:model="generate_atm_card_profile" id="generate_atm_card_profile" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500  focus:ring-2">
                    <label for="generate_atm_card_profile" class="ml-2 text-sm font-medium text-gray-900">Generate ATM Card Profile</label>
                </div>

                <div class="flex items-center mb-4">
                    <input wire:model="generate_mobile_profile" id="generate_mobile_profile" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500  focus:ring-2">
                    <label for="generate_mobile_profile" class="ml-2 text-sm font-medium text-gray-900">Create Mobile Profile</label>
                </div>

                <div class="flex items-center mb-4">
                    <input wire:model="allow_statement_generation" id="allow_statement_generation" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500  focus:ring-2">
                    <label for="allow_statement_generation" class="ml-2 text-sm font-medium text-gray-900">Allow Statement queries</label>
                </div>

                <div class="flex items-center mb-4">
                    <input wire:model="send_notifications" id="send_notifications" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500  focus:ring-2">
                    <label for="send_notifications" class="ml-2 text-sm font-medium text-gray-900">Send Notifications</label>
                </div>


                <div class="flex items-center mb-4">
                    <input wire:model="require_image_member" id="require_image_member" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500  focus:ring-2">
                    <label for="require_image_member" class="ml-2 text-sm font-medium text-gray-900">Require image - member</label>
                </div>

                <div class="flex items-center mb-4">
                    <input wire:model="require_image_id" id="require_image_id" type="checkbox" value="" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500  focus:ring-2">
                    <label for="require_image_id" class="ml-2 text-sm font-medium text-gray-900">Require image - ID with signature</label>
                </div>

                <div class="flex items-center mb-4">
                    <input wire:model="require_mobile_number" id="require_mobile_number"  type="checkbox"  class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500  focus:ring-2">
                    <label for="require_mobile_number" class="ml-2 text-sm font-medium text-gray-900">Require Mobile Number</label>
                </div>



            </fieldset>
        </div>

        <div class="w-1/4 border-l pl-2">

            <label for="number_of_accounts" class="block mb-1 mt-2 sm:text-xs  font-medium text-gray-900 ">Number of accounts</label>
            <span id="number_of_accounts" class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-gray-300 rounded-md ">
                                            {{$this->number_of_accounts}}
                                    </span>

            <label for="total_value_of_accounts" class="block mb-1 mt-2 sm:text-xs  font-medium text-gray-900 ">Total value of accounts</label>
            <span id="total_value_of_accounts" class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-gray-300 rounded-md ">
                                            {{$this->total_value_of_accounts}}
                                    </span>

            <label for="profits" class="block mb-1 mt-2 sm:text-xs  font-medium text-gray-900 ">Profits generated so far</label>
            <span id="profits" class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-gray-300 rounded-md ">
                                            {{$this->profits}}
                                    </span>


        </div>
    </div>

    <x-jet-section-border />

    <div class="mt-2"></div>


    <x-jet-label for="notes" value="{{ __('Notes') }}" />
    <textarea id="notes" name="notes" wire:model.defer="notes" rows="5" class="mt-1 block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500    " placeholder="Your notes..."></textarea>
    @error('notes')
    <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
        <p>Notes is mandatory, it should be more than three characters and unique.</p>
    </div>
    @enderror

    <div class="mt-2"></div>

    <div class="flex justify-end">
        <div >
            <div wire:loading.remove wire:target="updateInstitution">
                <button wire:click='updateInstitution' class="bg-red-500 text-white inline-flex items-center px-4 py-1 border border-solid rounded-md font-semibold transition" >
                    Save Product
                </button>
            </div>

            <div wire:loading wire:target="updateInstitution">
                <button type="button" class="disabled bg-red-500 text-white inline-flex items-center px-4 py-1 border border-solid rounded-md font-semibold transition" >

                    <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin  h-6 w-6 mr-3 stroke-white" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>

                    Processing...
                </button>
            </div>


        </div>
    </div>

</div>
