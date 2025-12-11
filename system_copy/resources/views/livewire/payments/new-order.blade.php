
<div>
    <div class="flex">

        <div class="w-1/4 border-r border-gray-200 pb-10" >

                    <p class="text-lg text-gray-600 font-semibold">Create payment order </p>

                        <div class="w-full border-b border-gray-200 dark:border-gray-700" >
                            <ul class="w-full nav nav-tabs flex flex-wrap -mb-px justify-between text-sm font-medium text-center text-gray-500 dark:text-gray-400">
                                <li class="mr-2 nav-item" role="presentation" >
                                    <a href="#tabs-homeJustify"
                                       id="tabs-home-tabJustify"
                                       data-bs-toggle="pill"
                                       data-bs-target="#tabs-homeJustify"
                                       role="tab"
                                       aria-controls="tabs-homeJustify"
                                       {{$this->oneIsSetClicked  ? "aria-selected='true'" : "aria-selected='false'" }}
                                       wire:click="changeTab(1)"
                                       class="
                                        {{$this->oneIsSetClicked  ? "text-blue-600 border-blue-600 active dark:text-blue-500  dark:border-blue-500 " : "border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 "}}
                                                   inline-flex p-4
                                                    rounded-t-lg
                                                    border-b-2
                                                    group"
                                            {{$this->oneIsSetClicked  ? "aria-current='page'" : "" }}
                                    >
                                        <svg class="mr-2 w-5 h-5 {{$this->oneIsSetClicked  ? "text-blue-600 dark:text-blue-500" : "text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300" }}" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path></svg>
                                        External
                                    </a>
                                </li>
                                <li class="mr-2 nav-item" role="presentation" >
                                    <a href="#tabs-profileJustify"
                                       id="tabs-profile-tabJustify"
                                       data-bs-toggle="pill"
                                       data-bs-target="#tabs-profileJustify"
                                       role="tab"
                                       aria-controls="tabs-profileJustify"
                                       {{$this->twoIsSetClicked  ? "aria-selected='true'" : "aria-selected='false'" }}
                                       wire:click="changeTab(2)"
                                       class="
                   {{$this->twoIsSetClicked  ? "text-blue-600 border-blue-600 active dark:text-blue-500  dark:border-blue-500 " : "border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 "}}
                                                   inline-flex p-4
                                                    rounded-t-lg
                                                    border-b-2
                                                    group"
                                            {{$this->twoIsSetClicked  ? "aria-current='page'" : "" }}
                                    >
                                        <svg class="mr-2 w-5 h-5 {{$this->twoIsSetClicked  ? "text-blue-600 dark:text-blue-500" : "text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300" }}" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                        Internal
                                    </a>
                                </li>
                                <li class="mr-2 nav-item" role="presentation" wire:click="changeTab(3)">
                                    <a href="#tabs-messagesJustify"
                                       id="tabs-messages-tabJustify"
                                       data-bs-toggle="pill"
                                       data-bs-target="#tabs-messagesJustify"
                                       role="tab"
                                       aria-controls="tabs-messagesJustify"
                                       {{$this->threeIsSetClicked  ? "aria-selected='true'" : "aria-selected='false'" }}
                                       class="
                   {{$this->threeIsSetClicked  ? "text-blue-600 border-blue-600 active dark:text-blue-500  dark:border-blue-500 " : "border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 "}}
                                                   inline-flex p-4
                                                    rounded-t-lg
                                                    border-b-2
                                                    group"
                                            {{$this->threeIsSetClicked  ? "aria-current='page'" : "" }}
                                    >
                                        <svg class="mr-2 w-5 h-5 {{$this->threeIsSetClicked  ? "text-blue-600 dark:text-blue-500" : "text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300" }}" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M5 4a1 1 0 00-2 0v7.268a2 2 0 000 3.464V16a1 1 0 102 0v-1.268a2 2 0 000-3.464V4zM11 4a1 1 0 10-2 0v1.268a2 2 0 000 3.464V16a1 1 0 102 0V8.732a2 2 0 000-3.464V4zM16 3a1 1 0 011 1v7.268a2 2 0 010 3.464V16a1 1 0 11-2 0v-1.268a2 2 0 010-3.464V4a1 1 0 011-1z"></path></svg>
                                        Utilities
                                    </a>
                                </li>

                            </ul>
                        </div >





                        <div class="w-full tab-content" id="tabs-tabContentJustify">
                            <div class="tab-pane fade {{$this->oneIsSetClicked  ? "show active" : "" }}" id="tabs-homeJustify" role="tabpanel"
                                 aria-labelledby="tabs-home-tabJustify">




                                 @if(session('message'))
                                 <div id="alert-border-1" class="flex items-center p-4 mb-4 text-blue-800 border-t-4 border-blue-300 bg-blue-50 dark:text-blue-400 dark:bg-gray-800 dark:border-blue-800" role="alert">
                                    <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                      <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                                    </svg>
                                    <div class="ms-3 text-sm font-medium">
                                        {{ session('message') }}
                                    </div>
                                    <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-blue-50 text-blue-500 rounded-lg focus:ring-2 focus:ring-blue-400 p-1.5 hover:bg-blue-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-blue-400 dark:hover:bg-gray-700" data-dismiss-target="#alert-border-1" aria-label="Close">
                                      <span class="sr-only">Dismiss</span>
                                      <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                                      </svg>
                                    </button>
                                </div>

                                @endif

                                @if(session()->has('message_fail'))

                                <div id="alert-border-1" class="flex items-center p-4 mb-4 text-red-800 border-t-4 border-red-300 bg-red-50 dark:text-blue-400 dark:bg-gray-800 dark:border-blue-800" role="alert">
                                    <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                      <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                                    </svg>
                                    <div class="ms-3 text-sm font-medium">
                                        {{ session('message_fail') }}
                                    </div>
                                    <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-blue-50 text-blue-500 rounded-lg focus:ring-2 focus:ring-blue-400 p-1.5 hover:bg-blue-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-blue-400 dark:hover:bg-gray-700" data-dismiss-target="#alert-border-1" aria-label="Close">
                                      <span class="sr-only">Dismiss</span>
                                      <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                                      </svg>
                                    </button>
                                </div>

                                @endif


                                 @if($this->twoIsSetClicked)

                                <div class="mt-4 text-l font-bold strong mx-4 " >
                                     {{ $this->title }}
                                </div>


                                <div class="bg-gray-200 bg-opacity-25 my-4 pb-px">
                                    <div class="p-3">
                                        <div class="col-span-6 sm:col-span-4">
                                            <x-jet-label for="unCommitedOrderNumber" value="{{ __('Reference  number') }}" />
                                            <x-jet-input wire:keydown="start" id="unCommitedOrderNumber" type="text" class="mt-1 block w-full" value="{{ $this->reference_number }}" wire:model.defer="unCommitedOrderNumber" autocomplete="unCommitedOrderNumber" />
                                            <x-jet-input-error for="unCommitedOrderNumber" class="mt-2" />
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-200 bg-opacity-25 my-4 pb-px">
                                    <div class="p-3">
                                        <div class="flex justify-between">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input form-check-input appearance-none rounded-full h-4 w-4 border border-gray-300 bg-white checked:bg-blue-900 checked:border-blue-600 focus:outline-none transition duration-200 mt-1 align-top bg-no-repeat bg-center bg-contain float-left mr-2 cursor-pointer" type="radio" name="inlineRadioOptions" id="inlineRadio1" value="Single Transaction" wire:model="amountOfTransactions">
                                                <label class="form-check-label inline-block text-gray-800" for="inlineRadio10">Single Transaction</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input form-check-input appearance-none rounded-full h-4 w-4 border border-gray-300 bg-white checked:bg-blue-900 checked:border-blue-600 focus:outline-none transition duration-200 mt-1 align-top bg-no-repeat bg-center bg-contain float-left mr-2 cursor-pointer" type="radio" name="inlineRadioOptions" id="inlineRadio2" value="Batch" wire:model="amountOfTransactions">
                                                <label class="form-check-label inline-block text-gray-800" for="inlineRadio20">Batch</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                @if ($amountOfTransactions=="Single Transaction")

                                <div class="p-3">
                                    <div class="form-group row">

                                        <x-jet-label for="sourceaccount" value="{{ __('Select Source  Account') }}" />
                                        <select id="sourceaccount" wire:model="sourceAccount" class="block mt-1 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm sm:px-3 sm:text-sm h-10" required>
                                            <option value="" selected>Select</option>
                                            @foreach($this->accounts as $account)
                                                <option value="{{$account->account_number}}">{{$account->account_name}} - {{$account->account_number}}</option>

                                            @endforeach
                                        </select>

                                        <x-jet-input-error for="sourceAccount" class="mt-2" />

                                    </div>

                                </div>




                                <div class="p-3">
                                    <div class="form-group row">

                                        <x-jet-label for="destination_account" value="{{ __('Select Destination  Account') }}" />
                                        <select id="destination_account" wire:model="destination_account" class="block mt-1 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm sm:px-3 sm:text-sm h-10" required>
                                            <option value="" selected>Select</option>

                                            @foreach($this->accounts as $account)

                                                <option value="{{$account->account_number}}">{{$account->account_name}} - {{$account->account_number}}</option>

                                            @endforeach
                                        </select>

                                        <x-jet-input-error for="destination_account" class="mt-2" />


                                    </div>

                                </div>

                                <div class="p-3">
                                    <div class="col-span-6 sm:col-span-4">
                                        <x-jet-label for="amount" value="{{ __(' Amount') }}" />
                                        <x-jet-input id="amount" type="number" class="mt-1 block w-full" wire:model.defer="amount" autocomplete="amount"></x-jet-input>
                                        <x-jet-input-error for="amount" class="mt-2" />
                                    </div>
                                </div>


                                @endif




                                @if($this->typeOfTransfer)
                                    <div class="bg-gray-200 bg-opacity-25 my-4 pb-px">
                                        <div class="p-3">


                                            <div class="col-span-6 sm:col-span-4">



                                                <form wire:submit.prevent="importData">



                                                    <x-jet-label for="formFileSm" value="Select {{ $this->typeOfTransfer }} Excel/CSV File" />
                                                    <input wire:model="excelFile" class="form-control block mt-1 block w-full px-2 py-1 text-sm font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none" id="formFileSm" type="file">



                                                    <div class=""  align="right">


                                                        <button type="submit" wire:loading.attr="disabled" type="button" class="inline-block px-4 py-1.5 bg-gray-400  mt-4 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-gray-400 hover:shadow-lg focus:bg-blue-900 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-gray-800 active:shadow-lg transition duration-150 ease-in-out">

                                                            Upload</button>


                                                    </div>

                                                    @error('photo') <span class="error">{{ $message }}</span> @enderror

                                                </form>

                                            </div>

                                        </div>
                                    </div>
                                @else


                                @endif



                                @endif

                                <div class="mt-5"  align="right">

                                    <button wire:click='internalFundTransfer' class="mr-4 bg-gray-50 inline-flex items-center px-4 py-2 border border-solid rounded-md font-semibold text-xs text-gray lowercase tracking-widest hover:bg-gray-200 active:bg-gray-300 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition" >
                                            TRANSFER
                                    </button>

                                </div>



                                {{-- @if($this->amountOfTransactions =='Single Transaction') --}}











                                    {{-- <div class="bg-gray-200 bg-opacity-25 my-4"> --}}


                                        {{-- <div class="p-3">
                                            <div class="form-group row">

                                                <x-jet-label for="destination" value="{{ __('Type of transaction') }}" />
                                                <select id="destination" wire:model="selectedDestination" class="block mt-1 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm sm:px-3 sm:text-sm h-10" required>
                                                    <option value="" selected>Select</option>
                                                    <option value="TISS" selected>TISS Transaction</option>
                                                    <option value="EFT" selected>EFT Transaction</option>
                                                    <option value="IFT" selected>IFT Transaction</option>
                                                    <option value="MNO">Mobile Transaction</option>
                                                </select>

                                            </div>

                                        </div> --}}





                                        {{-- <div class="p-3">
                                            <div class="form-group row">

                                                <x-jet-label for="sourceaccount" value="{{ __('Select Source Bank Account') }}" />
                                                <select id="sourceaccount" wire:model="sourceAccount" class="block mt-1 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm sm:px-3 sm:text-sm h-10" required>
                                                    <option value="" selected>Select</option>
                                                    @foreach($this->accounts as $account)
                                                        <option value="{{$account->account_number}}">{{$account->account_name}} - {{$account->account_number}}</option>

                                                    @endforeach
                                                </select>

                                            </div>

                                        </div> --}}


{{--
                                        @if($this->amountOfTransactions =='Single Transaction' && ($this->selectedDestination =='IFT' || $this->selectedDestination =='EFT' || $this->selectedDestination =='TISS') )
                                            @if($this->selectedDestination =='IFT')

                                            @else
                                                <div class="p-3">
                                                    <div class="form-group row">

                                                        <x-jet-label for="selectedbank" value="{{ __('Select Bank') }}" />
                                                        <select id="selectedbank" wire:model="selectedBank" class="block mt-1 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm sm:px-3 sm:text-sm h-10" required>
                                                            <option value="" selected>Select</option>

                                                            @foreach($this->banks as $bank)
                                                                <option value="{{$bank->bank_number}}">{{$bank->bank_name}}</option>

                                                            @endforeach
                                                        </select>

                                                    </div>

                                                </div>
                                            @endif
                                            <div class="p-3">
                                                <div class="col-span-6 sm:col-span-4">
                                                    <x-jet-label for="accountNumber" value="{{ __('Beneficiary Account Number') }}" />
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

                                            @endif

                                            <div class="p-3">
                                                <div class="col-span-6 sm:col-span-4">
                                                    <x-jet-label for="bname" value="{{ __('Beneficiary Name') }}" />
                                                    <x-jet-input id="bname" type="text" class="mt-1 block w-full" wire:model.defer="bname" autocomplete="bname" />
                                                    <x-jet-input-error for="bname" class="mt-2" />
                                                </div>
                                            </div>


                                            <div class="p-3">
                                                <div class="col-span-6 sm:col-span-4">
                                                    <x-jet-label for="amount" value="{{ __('Amount') }}" />
                                                    <x-jet-input id="amount" type="number" class="mt-1 block w-full border outline-none appearance-none" wire:model.defer="amount" autocomplete="amount" />
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


                                        @if($this->amountOfTransactions =='Single Transaction' && $this->selectedDestination =='MNO' )
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
                                                    <x-jet-label for="mobileNumber" value="{{ __('Mobile Number') }}" />
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
                                                    <x-jet-label for="amount" value="{{ __('Amount') }}" />
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

                                @else
                                @endif --}}



{{--
                                @if($this->amountOfTransactions =='Batch')

                                    <div class="bg-gray-200 bg-opacity-25 my-4 pb-px">
                                        <div class="p-3">

                                            <div class="flex justify-between">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input form-check-input appearance-none rounded-full h-4 w-4 border border-gray-300 bg-white checked:bg-blue-900 checked:border-blue-600 focus:outline-none transition duration-200 mt-1 align-top bg-no-repeat bg-center bg-contain float-left mr-2 cursor-pointer"
                                                           type="radio" name="inlineRadioOptions1" id="inlineRadio1a" value="IFT" wire:model="typeOfTransfer">
                                                    <label class="form-check-label inline-block text-gray-800" for="inlineRadio1a">IFT</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input form-check-input appearance-none rounded-full h-4 w-4 border border-gray-300 bg-white checked:bg-blue-900 checked:border-blue-600 focus:outline-none transition duration-200 mt-1 align-top bg-no-repeat bg-center bg-contain float-left mr-2 cursor-pointer"
                                                           type="radio" name="inlineRadioOptions1" id="inlineRadio2a" value="EFT" wire:model="typeOfTransfer">
                                                    <label class="form-check-label inline-block text-gray-800" for="inlineRadio2a">EFT</label>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                        </div>




                                @else


                                @endif --}}





                                {{-- @if($this->showSaveButton) --}}
                                    {{-- <div class="mt-5"  align="right">

                                        <button wire:click='createOrder' class="mr-4 inline-flex items-center px-4 py-2 border border-solid rounded-md font-semibold text-xs text-gray lowercase tracking-widest hover:bg-gray-200 active:bg-gray-300 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition" >
                                            CREATE
                                        </button>

                                    </div> --}}
                                {{-- @else


                                @endif --}}

                            </div>
                        </div>



                            {{-- <div class="tab-pane fade {{$this->twoIsSetClicked  ? "show active" : "" }}" id="tabs-profileJustify" role="tabpanel" aria-labelledby="tabs-profile-tabJustify">
                                Awaiting API
                            </div>
                            <div class="tab-pane fade {{$this->threeIsSetClicked  ? "show active" : "" }}" id="tabs-messagesJustify" role="tabpanel" aria-labelledby="tabs-profile-tabJustify">
                                Awaiting API
                            </div>
                        </div> --}}


        </div>

        <div class="w-3/4 p-2" >

            <div class="flex justify-between">
                <table class="table-auto m-4">

                    <tbody>
                    <tr>
                        <td><x-jet-label value="{{ __('Order number') }}" /></td>
                        <td >
                            <p class="ml-4 text-sm text-gray-700 dark:text-gray-500">
                                {{$this->unCommitedOrderNumber}}
                            </p>
                        </td>

                    </tr>
                    <tr>
                        <td><x-jet-label value="{{ __('Type of order') }}" /></td>
                        <td >
                            <p class="ml-4 text-sm text-gray-700 dark:text-gray-500">
                                {{$this->amountOfTransactions}}
                            </p>
                        </td>

                    </tr>
                    <tr>
                        <td><x-jet-label for="or" value="{{ __('Date') }}" /></td>
                        <td >
                            <p class="ml-4 text-sm text-gray-700 dark:text-gray-500">
                                {{date('Y-m-d H:i')}}
                            </p>
                        </td>

                    </tr>


                    </tbody>
                </table>
                @if($this->showSendButton)
                    <div class="flex">

                        <div class="mt-5"  align="right">
                            <x-jet-secondary-button wire:click="deleteOrder" wire:loading.attr="disabled" class="mr-4"  align="right">
                                {{ __('Delete') }}
                            </x-jet-secondary-button>
                        </div>


                        <div class="mt-5"  align="right">
                            <x-jet-secondary-button wire:click="requestApproval" wire:loading.attr="disabled" class="mr-4"  align="right">
                                {{ __('Send for Approval') }}
                            </x-jet-secondary-button>
                        </div>


                    </div>
                @endif
            </div>
            <livewire:payments.new-orders-table/>


        </div>

    </div>
</div>

