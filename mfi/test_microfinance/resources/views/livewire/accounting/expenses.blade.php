
<div>
    <div class="p-4">
        <div class="bg-white p-4 sm:p-6 overflow-hidden mb-2 rounded-2xl shadow-md shadow-gray-200">
            <livewire:accounting.expenses-table/>
        </div>
    </div>



    <!-- Log Out Other Devices Confirmation Modal -->
    <x-jet-dialog-modal wire:model="approveExpenses">
        <x-slot name="title">

        </x-slot>

        <x-slot name="content">
            <div>
                @if (session()->has('message'))

                    @if (session('alert-class') == 'alert-success')
                        <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md mb-8" role="alert">
                            <div class="flex">
                                <div class="py-1"><svg class="fill-current h-6 w-6 text-teal-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                                <div>
                                    <p class="font-bold">The process is completed</p>
                                    <p class="text-sm">{{ session('message') }} </p>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif


                <div class="bg-white ">

                    <div class="max-w-md mx-auto bg-white rounded-xl  overflow-hidden md:max-w-2xl">
                        <div class="md:flex">
                            <!-- Right Column -->
                            <div class="p-4">
                                <div class="uppercase tracking-wide text-sm text-indigo-500 font-semibold"> Expenses Request </div>
                                <h4 class="block mt-1 text-lg leading-tight font-medium text-black hover:underline">{{$this->member_name}}
                                </h4>
                                <p class="mt-2 text-gray-500">
                                    {{$this->description}}
                                </p>
                                <hr class="h-px my-8 bg-gray-200 border-0 dark:bg-gray-700">
                                <!-- Data displayed in two columns -->
                                <div class="mt-4 w-full">
                                    <div class="grid  w-full gap-2">
                                       <table class="w-full">
                                           <thead>
                                           <tr>
                                               <th class="text-left">Account Number</th>
                                               <th class="text-right">{{$this->source_account}}</th>
                                           </tr>
                                               <tr>
                                               <th class="text-left">Account Name</th>
                                               <th class="text-right">{{$this->source_account_name}}</th>
                                              </tr>
                                           <tr>
                                               <th class="text-left">Available Funds</th>
                                               <th class="text-right">{{number_format($this->account_balance,2)}} TZS</th>
                                           </tr>
                                           </thead>

                                            <tbody>
                                           <tr>
                                               <td class="text-left">Requested Amount</td>
                                               <td class="text-right"> {{number_format($this->request_amount,2)}} TZS</td>
                                           </tr>
                                           </tbody>

                                       </table>
                                        <!-- Add more data columns as needed -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>



                </div>
            </div>



        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('approveExpenses')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-jet-secondary-button>
            <div wire:loading.remove wire:target="updateExpense" >

                <x-jet-button class="ml-3"
                              wire:click="updateExpense"
                              wire:loading.attr="disabled">
                    {{ __('PAY') }}
                </x-jet-button>

            </div>
            <div wire:loading wire:target="updateExpense">
                <x-jet-button class="ml-3 "  >
                    <svg aria-hidden="true" role="status" class="inline w-4 h-4 mr-3 text-white animate-spin" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="#E5E7EB"/>
                        <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentColor"/>
                    </svg>
                    Please wait...
                </x-jet-button>
            </div>

        </x-slot>
    </x-jet-dialog-modal>

</div>




