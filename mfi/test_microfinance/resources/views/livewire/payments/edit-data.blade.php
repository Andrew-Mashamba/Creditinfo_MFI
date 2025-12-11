<div>

        <div>
            <button type="button" wire:click="$emit('closeEditEntryModal')"
                    class="absolute top-3 right-2.5 text-gray-400 bg-transparent
                 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5
                  ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white"
                    data-modal-toggle="modal">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
            </button>
            <div class="py-6 px-6 lg:px-8">
                <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Update entry</h3>
                <div class="bg-gray-200 bg-opacity-25 my-4">

                    <div class="p-3">
                        <div class="form-group row">

                            <x-jet-label for="sourceaccount" value="{{ __('Select Source Bank Account') }}" />
                            <select id="sourceaccount" wire:model="sourceAccount" class="block mt-1 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm sm:px-3 sm:text-sm h-10" required>
                                <option value="" selected>Select</option>
                                @foreach($this->accounts as $account)
                                    <option value="{{$account->account_number}}">{{$account->account_name}} - {{$account->account_number}}</option>

                                @endforeach
                            </select>

                        </div>

                    </div>

                    <div class="p-3">
                        <div class="form-group row">

                            <x-jet-label for="destination" value="{{ __('Select Destination') }}" />
                            <select id="destination" wire:model="selectedDestination" class="block mt-1 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm sm:px-3 sm:text-sm h-10" required>
                                <option value="" selected>Select</option>
                                <option value="bank" selected>Bank account</option>
                                <option value="mno">Mobile Wallet</option>
                            </select>

                        </div>

                    </div>


                    @if($this->selectedDestination =='bank' )
                        <div class="p-3">
                            <div class="form-group row">

                                <x-jet-label for="selectedbank" value="{{ __('Select Bank') }}" />
                                <select id="selectedbank" wire:model="selectedBank" class="block mt-1 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm sm:px-3 sm:text-sm h-10" required>
                                    <option value="" selected>Select</option>
                                    <option value="mcb">Mwalimu Commercial Bank</option>
                                    <option value="crdb">CRDB Bank</option>
                                    <option value="nmb">NMB Bank</option>
                                </select>

                            </div>

                        </div>

                        <div class="p-3">
                            <div class="col-span-6 sm:col-span-4">
                                <x-jet-label for="accountNumber" value="{{ __('Entre Account Number') }}" />
                                <x-jet-input id="accountNumber" type="text" class="mt-1 block w-full" wire:model.defer="accountNumber" autocomplete="accountNumber"></x-jet-input>
                                <x-jet-input-error for="accountNumber" class="mt-2" />
                            </div>
                        </div>
                        @if($this->selectedBank == 'mcb')
                            @if($this->recipientName !='')
                                <div class="ml-12">
                                    <div class="mt-2 text-sm text-gray-500">
                                        Recipient Name : Peter Mwamlima.
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="ml-12">
                                <div class="mt-2 text-sm text-red-400">
                                    Notice : We can not verify this account at this moment.
                                </div>
                            </div>
                        @endif

                        <div class="p-3">
                            <div class="col-span-6 sm:col-span-4">
                                <x-jet-label for="amount" value="{{ __('Entre Amount') }}" />
                                <x-jet-input id="amount" type="number" class="mt-1 block w-full" wire:model.defer="amount" autocomplete="amount" />
                                <x-jet-input-error for="amount" class="mt-2" />
                            </div>
                        </div>

                        <div class="p-3">
                            <div class="col-span-6 sm:col-span-4">
                                <x-jet-label for="description" value="{{ __('Description') }}" />
                                <x-jet-input id="description" type="text" class="mt-1 block w-full" wire:keydown="activateSaveButton" wire:model.defer="description" autocomplete="description" />
                                <x-jet-input-error for="description" class="mt-2" />
                            </div>
                        </div>
                    @else
                    @endif


                    @if($this->selectedDestination =='mno' )
                        <div class="p-3">
                            <div class="form-group row">

                                <x-jet-label for="selectedmno" value="{{ __('Select Mobile Network Operator') }}" />
                                <select id="selectedmno" wire:model="selectedMno" class="block mt-1 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm sm:px-3 sm:text-sm h-10" required>
                                    <option value="Tigo" selected>Tigo</option>
                                    <option value="VodaCom">VodaCom</option>
                                    <option value="AirTel">AirTel</option>
                                    <option value="TTCL">TTCL</option>
                                    <option value="HaloTel">HaloTel</option>
                                </select>

                            </div>

                        </div>

                        <div class="p-3">
                            <div class="col-span-6 sm:col-span-4">
                                <x-jet-label for="mobileNumber" value="{{ __('Enter Mobile Number') }}" />
                                <x-jet-input id="mobileNumber" type="text" class="mt-1 block w-full" wire:model.defer="mobileNumber" autocomplete="mobileNumber"></x-jet-input>
                                <x-jet-input-error for="mobileNumber" class="mt-2" />
                            </div>
                        </div>
                        @if($this->mobileNumber !='')
                            <div class="ml-12">
                                <div class="mt-2 text-sm text-gray-500">
                                    Recipient Name : Peter Mwamlima.
                                </div>
                            </div>
                        @endif

                        <div class="p-3">
                            <div class="col-span-6 sm:col-span-4">
                                <x-jet-label for="amount" value="{{ __('Enter Amount') }}" />
                                <x-jet-input id="amount" type="number" class="mt-1 block w-full" wire:model.defer="amount" autocomplete="amount" />
                                <x-jet-input-error for="amount" class="mt-2" />
                            </div>
                        </div>

                        <div class="p-3">
                            <div class="col-span-6 sm:col-span-4">
                                <x-jet-label for="description" value="{{ __('Description') }}" />
                                <x-jet-input id="description" type="text" class="mt-1 block w-full" wire:keydown="activateSaveButton" wire:model.defer="description" autocomplete="description" />
                                <x-jet-input-error for="description" class="mt-2" />
                            </div>
                        </div>
                    @else
                    @endif


                </div>
            </div>

            @if($this->showSaveButton)

                <div class="mt-5"  align="right">

                    <button wire:click="createOrder" wire:loading.attr="disabled" class="mr-4 inline-flex items-center px-4 py-2 border border-solid rounded-md font-semibold text-xs text-gray lowercase tracking-widest hover:bg-gray-200 active:bg-gray-300 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition" >
                        UPDATE
                    </button>

                </div>

            @else


            @endif


        </div>


</div>
