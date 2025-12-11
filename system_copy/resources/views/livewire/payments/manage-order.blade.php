
<div>
    <div class="grid grid-cols-12">

        <div class="col-span-3 w-full h-full pt-2 pl-2 pr-2 border-r border-gray-200 pb-10">

                        <p class="text-lg text-gray-600 font-semibold">Orders List</p>

                        @foreach($this->orders as $order)

                            @php

                                $first_authorizer = \App\Models\bladeUsers::where('id' ,$order->first_authorizer_id)->value('name');
                                $second_authorizer = \App\Models\bladeUsers::where('id' ,$order->second_authorizer_id)->value('name');
                                $third_authorizer= \App\Models\bladeUsers::where('id' ,$order->third_authorizer_id)->value('name');

                            @endphp
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
                                            @if($order->order_status == 'Processed With Exceptions')
                                                <div>
                                                    <span align="right" class="bg-red-100 text-red-800 text-sm font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-red-200 dark:text-red-900">Processed With Exceptions</span>
                                                </div>
                                            @endif

                                            @if($order->order_status == 'In Progress')
                                                <div>
                                                    <span align="right" class="bg-yellow-100 text-yellow-800 text-sm font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-yellow-200 dark:text-yellow-900">In Progress</span>
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

                                        <table class="table-auto">

                                            <tbody>
                                            @foreach(\App\Models\bladeUsers::where('current_team_id' ,$order->team_id)->get() as $user)
                                                @php
                                                    $role= \App\Models\TeamUser::where('user_id' ,$user->id)->value('role');
                                                @endphp
                                                @if($role == 'firstAuthorizer')
                                                    <tr>
                                                        <td><x-jet-label value="{{ $user->name}}" /></td>
                                                        <td >
                                                            <p class="ml-4 text-sm text-gray-700 dark:text-gray-500">
                                                                {{$order->first_authorizer_comments}}
                                                            </p>
                                                        </td>

                                                    </tr>
                                                @endif
                                                @if($role == 'secondAuthorizer')
                                                    <tr>
                                                        <td><x-jet-label value="{{ $user->name}}" /></td>
                                                        <td >
                                                            <p class="ml-4 text-sm text-gray-700 dark:text-gray-500">
                                                                {{$order->first_authorizer_comments}}
                                                            </p>
                                                        </td>

                                                    </tr>
                                                @endif
                                                @if($role == 'thirdAuthorizer')
                                                    <tr>
                                                        <td><x-jet-label value="{{ $user->name}}" /></td>
                                                        <td >
                                                            <p class="ml-4 text-sm text-gray-700 dark:text-gray-500">
                                                                {{$order->first_authorizer_comments}}
                                                            </p>
                                                        </td>

                                                    </tr>
                                                @endif
                                            @endforeach


                                            </tbody>
                                        </table>

                                        <div class="hidden sm:block">
                                            <div class="py-2">
                                                <div class="border-t border-gray-200"></div>
                                            </div>
                                        </div>

                                        <table class="table-auto">

                                            <tbody>
                                            <tr>
                                                <td><x-jet-label value="No of transactions" /></td>
                                                <td >
                                                    <p class="ml-4 text-sm text-gray-700 dark:text-gray-500">
                                                        {{\App\Models\Transactions::where('order_number' ,$order->order_number)->count()}}
                                                    </p>
                                                </td>

                                            </tr>
                                            <tr>
                                                <td><x-jet-label value="Successful " /></td>
                                                <td >
                                                    <p class="ml-4 text-sm text-gray-700 dark:text-gray-500">
                                                        {{\App\Models\Transactions::where('order_number' ,$order->order_number)->where('trans_status','Successful')->count()}}
                                                    </p>
                                                </td>

                                            </tr>
                                            <tr>
                                                <td><x-jet-label for="or" value="Failed" /></td>
                                                <td >
                                                    <p class="ml-4 text-sm text-gray-700 dark:text-gray-500">
                                                        {{\App\Models\Transactions::where('order_number' ,$order->order_number)->where('trans_status','Failed')->count()}}
                                                    </p>
                                                </td>

                                            </tr>

                                            <tr>
                                                <td><x-jet-label for="or" value="Suspect" /></td>
                                                <td >
                                                    <p class="ml-4 text-sm text-gray-700 dark:text-gray-500">
                                                        {{\App\Models\Transactions::where('order_number' ,$order->order_number)->where('trans_status','Suspect')->count()}}
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

                                                    @if($order->order_status == 'Pending')
                                                        <button wire:click="confirmDeleteOrder({{$order->id}})" type="button" class="rounded-l inline-block inline-flex items-center px-4 py-2 bg-white border font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition">
                                                            Delete
                                                        </button>
                                                        <button wire:click="getOrderDetails({{$order->id}})"
                                                                type="button" class="rounded-r inline-block inline-flex items-center px-4 py-2 bg-white border font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition">
                                                            View
                                                        </button>
                                                    @endif
                                                    @if($order->order_status == 'Pending Approval')
                                                        <button wire:click="getOrderDetails({{$order->id}})"
                                                                type="button" class="rounded-r inline-block inline-flex items-center px-4 py-2 bg-white border font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition">
                                                            View
                                                        </button>
                                                    @endif
                                                    @if($order->order_status == 'Rejected')
                                                        <button wire:click="confirmDeleteOrder({{$order->id}})" type="button" class="rounded-l inline-block inline-flex items-center px-4 py-2 bg-white border font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition">
                                                            Delete
                                                        </button>
                                                        <button wire:click="getOrderDetails({{$order->id}})"
                                                                type="button" class="rounded-r inline-block inline-flex items-center px-4 py-2 bg-white border font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition">
                                                            View
                                                        </button>
                                                    @endif

                                                    @if($order->order_status == 'Processed')
                                                        <button wire:click="getOrderDetails({{$order->id}})"
                                                                type="button" class="rounded-r inline-block inline-flex items-center px-4 py-2 bg-white border font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition">
                                                            View
                                                        </button>
                                                    @endif
                                                    @if($order->order_status == 'Processed With Errors')
                                                        <button wire:click="getOrderDetails({{$order->id}})"
                                                                type="button" class="rounded-r inline-block inline-flex items-center px-4 py-2 bg-white border font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition">
                                                            View Errors
                                                        </button>
                                                    @endif
                                                    @if($order->order_status == 'Processed With Exceptions')
                                                        <button wire:click="getOrderDetailsExceptions({{$order->id}})"
                                                                type="button" class="rounded-r inline-block inline-flex items-center px-4 py-2 bg-white border font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition">
                                                            View Exceptions
                                                        </button>
                                                    @endif
                                                    @if($order->order_status == 'Processed With Exceptions And Errors')
                                                        <button wire:click="getOrderDetailsExceptions({{$order->id}})"
                                                                type="button" class="rounded-r inline-block inline-flex items-center px-4 py-2 bg-white border font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition">
                                                            View Exceptions/Errors
                                                        </button>
                                                    @endif

                                                    @if($order->order_status == 'In Progress')

                                                        <button wire:click="getOrderDetails({{$order->id}})"
                                                                type="button" class="rounded-r inline-block inline-flex items-center px-4 py-2 bg-white border font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition">
                                                            View
                                                        </button>
                                                    @endif

                                                </div>
                                            </div>



                                        </div>

                                    </div>
                                </div>


                            </div>
                        @endforeach

        </div>
        <div class="col-span-9 p-2">

        <div>


                                @foreach($this->CurrentOrder as $x)


                                    @if($x->order_status === 'Pending')
                                        <div class="mt-4 ml-4 mb-2"  >
                                            <x-jet-secondary-button wire:click="requestApproval" wire:loading.attr="disabled" class="mr-4"  align="right">
                                                {{ __('Send For Approval') }}
                                            </x-jet-secondary-button>
                                        </div>
                                    @endif
                                    @if($x->order_status === 'Pending Approval')
                                        <div class="mt-4 ml-4 mb-2"  >
                                            <x-jet-secondary-button wire:click="cancelRequest" wire:loading.attr="disabled" class="mr-4"  align="right">
                                                {{ __('Cancel Approval Request') }}
                                            </x-jet-secondary-button>
                                        </div>
                                    @endif
                                    @if($x->order_status === 'Rejected')
                                        <div class="mt-4 ml-4 mb-2"  >
                                            <x-jet-secondary-button wire:click="requestApproval" wire:loading.attr="disabled" class="mr-4"  align="right">
                                                {{ __('Send For Approval') }}
                                            </x-jet-secondary-button>
                                        </div>
                                    @endif
                                    @if($x->order_status === 'Processed')
                                        <div class="mt-4 ml-4 mb-2"  >
                                            <x-jet-secondary-button wire:click="createNewOrder" wire:loading.attr="disabled" class="mr-4"  align="right">
                                                {{ __('Create New Order From This Order') }}
                                            </x-jet-secondary-button>
                                        </div>
                                    @endif
                                    @if($x->order_status === 'Processed With Errors')

                                        @if($this->prepareForRejection)
                                            <div class="mt-4 ml-4 mb-2"  >
                                                <x-jet-danger-button wire:click="createNewOrderErrors" wire:loading.attr="disabled" class="mr-4"  align="right">
                                                    {{ __('Proceed') }}
                                                </x-jet-danger-button>
                                            </div>
                                        @else
                                            <div class="mt-4 ml-4 mb-2"  >
                                                <x-jet-secondary-button wire:click="preReject" wire:loading.attr="disabled" class="mr-4"  align="right">
                                                    {{ __('Create New Order From Errors') }}
                                                </x-jet-secondary-button>
                                            </div>
                                        @endif
                                        @if($this->prepareForRejection)
                                            <div class="">
                                                <div class="mt-4 ml-4 mb-2">

                                                    <x-jet-label for="newOrderNumber" value="{{ __('Enter order number if available..') }}" />
                                                    <x-jet-input id="newOrderNumber" type="text" class="mt-1 block w-full"  wire:model.defer="newOrderNumber" autocomplete="newOrderNumber" />
                                                    <x-jet-input-error for="newOrderNumber" class="mt-2" />
                                                </div>
                                            </div>
                                        @endif

                                    @endif




                                    @if($x->order_status === 'Processed With Exceptions')


                                        @if($this->prepareForRejection)
                                            <div class="mt-4 ml-4 mb-2"  >
                                                <x-jet-danger-button wire:click="createNewOrderExceptions" wire:loading.attr="disabled" class="mr-4"  align="right">
                                                    {{ __('Proceed') }}
                                                </x-jet-danger-button>
                                            </div>
                                        @else
                                            <div class="mt-4 ml-4 mb-2"  >
                                                <x-jet-secondary-button wire:click="preReject" wire:loading.attr="disabled" class="mr-4"  align="right">
                                                    {{ __('Create New Order From Exceptions') }}
                                                </x-jet-secondary-button>
                                            </div>
                                        @endif
                                        @if($this->prepareForRejection)
                                            <div class="">
                                                <div class="mt-4 ml-4 mb-2">

                                                    <x-jet-label for="newOrderNumber" value="{{ __('Enter order number if available..') }}" />
                                                    <x-jet-input id="newOrderNumber" type="text" class="mt-1 block w-full"  wire:model.defer="newOrderNumber" autocomplete="newOrderNumber" />
                                                    <x-jet-input-error for="newOrderNumber" class="mt-2" />
                                                </div>
                                            </div>
                                        @endif


                                    @endif
                                    @if($x->order_status === 'Processed With Exceptions And Errors')
                                        <div class="mt-4 ml-4 mb-2"  >
                                            <x-jet-secondary-button wire:click="createNewOrderExceptionsAndErrors" wire:loading.attr="disabled" class="mr-4"  align="right">
                                                {{ __('Create New Order From Exceptions') }}
                                            </x-jet-secondary-button>
                                        </div>
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
                                                    {{$x->amountOfTransactions}}
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

                            </div>

        <livewire:payments.manage-orders-table/>

        </div>

    </div>

</div>





<div>
    <x-jet-dialog-modal id="xxxx" wire:model="modal" x-data="{ modal: @entangle('modal') }"  wire:ignore.self>
        <x-slot name="title">

        </x-slot>

        <x-slot name="content">
            <livewire:payments.edit-data/>



        </x-slot>

        <x-slot name="footer">

        </x-slot>
    </x-jet-dialog-modal>


    <x-jet-dialog-modal id="xxx" wire:model="deleteOrderModal" wire:ignore.self >
        <x-slot name="title">

        </x-slot>

        <x-slot name="content">

            <livewire:payments.confirm-modal-order/>


        </x-slot>

        <x-slot name="footer">

        </x-slot>
    </x-jet-dialog-modal>



    <x-jet-dialog-modal id="xx" wire:model="deleteEntryModal" wire:ignore.self >
        <x-slot name="title">

        </x-slot>

        <x-slot name="content">


            <livewire:payments.confirm-modal/>


        </x-slot>

        <x-slot name="footer">

        </x-slot>
    </x-jet-dialog-modal>



</div>

