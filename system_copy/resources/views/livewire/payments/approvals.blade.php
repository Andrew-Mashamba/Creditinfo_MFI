
<div>
    <div class="grid grid-cols-12">
        <div class="col-span-3 w-full h-full pt-2 pl-2 pr-2 border-r border-gray-200 pb-10">



                        <p class="text-lg text-gray-600 font-semibold">Orders List</p>

                        @foreach($this->orders as $order)
                            @if($order->order_status == 'Pending Approval')
                                <div class="bg-gray-200 bg-opacity-25 my-4 pb-px">
                                    <div class="p-3">
                                        <div class="col-span-6 sm:col-span-4">

                                            <div class="flex justify-end">
                                                @if($order->order_status == 'Pending')
                                                    <div>
                                                        <span align="right" class="bg-gray-100 text-gray-800 text-sm font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-gray-200 dark:text-gray-900">Pending </span>
                                                    </div>
                                                @endif
                                                @if($order->order_status == 'Pending Approval')
                                                    <div>
                                                        <span align="right" class="bg-yellow-100 text-yellow-800 text-sm font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-yellow-200 dark:text-yellow-900">Pending Approval</span>
                                                    </div>
                                                @endif
                                                @if($order->order_status == 'Rejected')
                                                    <div>
                                                        <span align="right" class="bg-red-100 text-red-800 text-sm font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-red-200 dark:text-red-900">Rejected</span>
                                                    </div>
                                                @endif

                                                @if($order->order_status == 'Processed')
                                                    <div>
                                                        <span align="right" class="bg-green-100 text-white-800 text-sm font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-green-200 dark:text-white-900">Processed</span>
                                                    </div>
                                                @endif
                                                @if($order->order_status == 'Processed With Errors')
                                                    <div>
                                                        <span align="right" class="bg-red-100 text-red-800 text-sm font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-red-200 dark:text-red-900">Processed With Errors</span>
                                                    </div>
                                                @endif

                                            </div>

                                            <table class="table-auto">

                                                <tbody>
                                                <tr>
                                                    <td><x-jet-label value="{{ __('Order number') }}" /></td>
                                                    <td >
                                                        <p class="ml-4 text-sm text-gray-700 dark:text-gray-500">
                                                            {{$order->order_number}}
                                                        </p>
                                                    </td>

                                                </tr>
                                                <tr>
                                                    <td><x-jet-label value="{{ __('Type of order') }}" /></td>
                                                    <td >
                                                        <p class="ml-4 text-sm text-gray-700 dark:text-gray-500">
                                                            {{$order->typeOfTransfer}} : {{$order->amountOfTransactions}}
                                                        </p>
                                                    </td>

                                                </tr>
                                                <tr>
                                                    <td><x-jet-label for="or" value="{{ __('Date') }}" /></td>
                                                    <td >
                                                        <p class="ml-4 text-sm text-gray-700 dark:text-gray-500">
                                                            {{$order->created_at}}
                                                        </p>
                                                    </td>

                                                </tr>


                                                </tbody>
                                            </table>
                                            <div class="hidden sm:block">
                                                <div class="py-2">
                                                    <div class="border-t border-gray-200"></div>
                                                </div>
                                            </div>

                                            <div class="flex justify-end" >

                                                <div class="flex items-center justify-center" >
                                                    <div class="inline-flex " role="group" >

                                                        <button wire:click="getOrderDetails({{$order->id}})"
                                                                type="button" class="rounded-l rounded-r inline-block inline-flex items-center px-4 py-2 bg-white border font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition">
                                                            View
                                                        </button>
                                                    </div>
                                                </div>



                                            </div>

                                        </div>
                                    </div>


                                </div>
                            @endif
                        @endforeach


        </div>
        <div class="col-span-9 p-2">

            <div>

                @foreach($this->CurrentOrder as $x)

                    @if($x->order_status === 'Pending Approval')
                        <div class="flex">
                            <div class="mt-4 ml-4 mb-2"  >
                                <x-jet-secondary-button wire:click="approve" wire:loading.attr="disabled" class="mr-4"  align="right">
                                    {{ __('Approve Order') }}
                                </x-jet-secondary-button>
                            </div>
                            @if($this->prepareForRejection)
                            @else
                                <div class="mt-4 ml-4 mb-2"  >
                                    <x-jet-secondary-button wire:click="preReject" wire:loading.attr="disabled" class="mr-4"  align="right">
                                        {{ __('Reject Order') }}
                                    </x-jet-secondary-button>
                                </div>
                            @endif
                            @if($this->prepareForRejection)
                                <div class="mt-4 ml-4 mb-2"  >
                                    <x-jet-danger-button wire:click="reject" wire:loading.attr="disabled" class="mr-4"  align="right">
                                        {{ __('Proceed') }}
                                    </x-jet-danger-button>
                                </div>
                            @endif
                        </div>

                        @if($this->prepareForRejection)
                            <div class="">
                                <div class="mt-4 ml-4 mb-2">
                                    <p class="text-red-600/75">{{$this->reasonError}}</p>
                                    <x-jet-label for="rejectionReason" value="{{ __('Rejection reason') }}" />
                                    <x-jet-input id="rejectionReason" type="text" class="mt-1 block w-full"  wire:model.defer="rejectionReason" autocomplete="description" />
                                    <x-jet-input-error for="rejectionReason" class="mt-2" />
                                </div>
                            </div>
                        @endif
                    @endif

                @endforeach

                <table class="table-auto ml-4 mt-2">

                    <tbody>
                    <tr>
                        <td><x-jet-label value="{{ __('Order number') }}" /></td>
                        <td >
                            <p class="ml-4 text-sm text-gray-700 dark:text-gray-500">
                                @foreach($this->CurrentOrder as $x)
                                    {{$x->order_number}}
                                @endforeach

                            </p>
                        </td>

                    </tr>
                    <tr>
                        <td><x-jet-label value="{{ __('Type of order') }}" /></td>
                        <td >
                            <p class="ml-4 text-sm text-gray-700 dark:text-gray-500">
                                @foreach($this->CurrentOrder as $x)
                                    {{$x->typeOfTransfer}} :  {{$x->amountOfTransactions}}
                                @endforeach
                            </p>
                        </td>

                    </tr>
                    <tr>
                        <td><x-jet-label for="or" value="{{ __('Date') }}" /></td>
                        <td >
                            <p class="ml-4 text-sm text-gray-700 dark:text-gray-500">
                                @foreach($this->CurrentOrder as $x)
                                    {{$x->created_at}}
                                @endforeach
                            </p>
                        </td>

                    </tr>


                    </tbody>
                </table>

                    <livewire:payments.approvals-table/>

            </div>


        </div>

    </div>

</div>





<div>
    <x-jet-dialog-modal id="xxxx" wire:model="modal" wire:ignore.self >
        <x-slot name="title">

        </x-slot>

        <x-slot name="content">


            <button wire:click="closeEditEntryModal" type="button" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white" data-modal-toggle="authentication-modal">
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


        </x-slot>

        <x-slot name="footer">
            <div class="mt-5"  align="right">

                <button wire:click='createOrder' class="mr-4 inline-flex items-center px-4 py-2 border border-solid rounded-md font-semibold text-xs text-gray lowercase tracking-widest hover:bg-gray-200 active:bg-gray-300 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition" >
                    UPDATE
                </button>

            </div>
        </x-slot>
    </x-jet-dialog-modal>


    <x-jet-dialog-modal id="xxx" wire:model="deleteOrderModal" wire:ignore.self >
        <x-slot name="title">

        </x-slot>

        <x-slot name="content">


            <button type="button" wire:click="closeDeleteOrderModal" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white" data-modal-toggle="authentication-modal">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
            </button>
            <div class="py-6 px-6 lg:px-8">
                <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Delete Order</h3>
                <div class="my-4">

                    <div class="p-3">
                        <div class="mt-2 text-sm text-gray-500">
                            Are you sure, you want to delete this Order?
                        </div>

                    </div>

                </div>
            </div>


        </x-slot>

        <x-slot name="footer">
            <div class="mt-5"  align="right">

                <button wire:click="$emit('deleteOrderConfirmed')" class="mr-4 inline-flex items-center px-4 py-2 border border-solid rounded-md font-semibold text-xs text-gray lowercase tracking-widest hover:bg-gray-200 active:bg-gray-300 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition" >
                    YES
                </button>

            </div>
            <div class="mt-5"  align="right">

                <button wire:click='closeDeleteOrderModal' class="mr-4 inline-flex items-center px-4 py-2 border border-solid rounded-md font-semibold text-xs text-gray lowercase tracking-widest hover:bg-gray-200 active:bg-gray-300 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition" >
                    CANCEL
                </button>

            </div>
        </x-slot>
    </x-jet-dialog-modal>



    <x-jet-dialog-modal id="xx" wire:model="deleteEntryModal" wire:ignore.self >
        <x-slot name="title">

        </x-slot>

        <x-slot name="content">


            <button type="button" wire:click="closeDeleteOrderModal" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white" data-modal-toggle="authentication-modal">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
            </button>
            <div class="py-6 px-6 lg:px-8">
                <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Delete Order</h3>
                <div class="my-4">

                    <div class="p-3">
                        <div class="mt-2 text-sm text-gray-500">
                            Are you sure, you want to delete this Entry?
                        </div>

                    </div>

                </div>
            </div>


        </x-slot>

        <x-slot name="footer">
            <div class="mt-5"  align="right">

                <button wire:click="$emit('deleteEntryConfirmed')" class="mr-4 inline-flex items-center px-4 py-2 border border-solid rounded-md font-semibold text-xs text-gray lowercase tracking-widest hover:bg-gray-200 active:bg-gray-300 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition" >
                    YES
                </button>

            </div>
            <div class="mt-5"  align="right">

                <button wire:click='closeDeleteEntryModal' class="mr-4 inline-flex items-center px-4 py-2 border border-solid rounded-md font-semibold text-xs text-gray lowercase tracking-widest hover:bg-gray-200 active:bg-gray-300 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition" >
                    CANCEL
                </button>

            </div>
        </x-slot>
    </x-jet-dialog-modal>



    <!-- Role Management Modal -->
    <x-jet-dialog-modal wire:model="currentlyManagingStatus" x-data="{ currentlyManagingStatus: @entangle('currentlyManagingStatus') }">
        <x-slot name="title">
            {{ __('Manage Role') }}
        </x-slot>

        <x-slot name="content">
            <div class="relative z-0 mt-1 border border-gray-200 rounded-lg cursor-pointer">

                <button type="button" class="relative px-4 py-3 inline-flex w-full rounded-lg focus:z-10
                    focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 "
                        wire:click="$set('currentStatus', 'oo')">

                </button>

            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="stopManagingRequests" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-jet-secondary-button>

            <x-jet-button class="ml-3" wire:click="updateStatus" wire:loading.attr="disabled">
                {{ __('Save') }}
            </x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>





</div>

