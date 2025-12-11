<div>
    {{-- Be like water. --}}
    <div class=" flex justify-end item-end ">

        <div class="flex w-1/4 justify-end item-end mb-2">
            <button id="states-button"  class="flex-shrink-0 z-10 inline-flex items-center py-2.5 px-4 text-sm font-medium text-center text-gray-500 bg-gray-100 border border-gray-300 rounded-l-lg hover:bg-gray-200 focus:ring-4 focus:outline-none focus:ring-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-700 dark:text-white dark:border-gray-600" type="button">
                DATE
            </button>

            <label for="states" class="sr-only">Choose a date</label>
            <input type="date" id="states" wire:model="date" wire:change="refreshComponent"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-r-lg
                        border-l-gray-100 dark:border-l-gray-700 border-l-2
                        block w-1/2 p-2.5 dark:bg-gray-700 dark:border-gray-600
                        dark:placeholder-gray-400 dark:text-white">
        </div>
    </div>

    <livewire:teller-management.teller-position-table />






    <div class="w-full container-fluid">
        @if($this->approveBoolean)
            <div class="fixed z-10 inset-0 overflow-y-auto"  >
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                    <div class="fixed inset-0 transition-opacity">
                        <div class="absolute inset-0 bg-gray-500 opacity-0"></div>
                    </div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;
                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <!-- Your form elements go here -->
                        <div class="p-4">
                            <div>
                                @if (session()->has('message'))
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

                                @if (session()->has('message_fail'))
                                    <div class="bg-red-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md mb-8" role="alert">
                                        <div class="flex">
                                            <div class="py-1"><svg class="fill-current h-6 w-6 text-teal-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                                            <div>
                                                <p class="font-bold">The process is uncompleted</p>
                                                <p class="text-sm">{{ session('message_fail') }} </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="mt-2">
                            </div>
                            <div class="w-full p-4 ">
                                <p  class="block mb-1 text-sm capitalize text-slate-400 dark:text-white ">USER SELECTED</p>
                                <div class="flex items-center mb-2 text-sm spacing-sm text-slate-600 mt-2" >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>

                                    {{$this->employee_name}}
                                </div>

                                <table class="min-w-full bg-white border border-gray-300">
                                    <thead class="text-start justify">
                                    <tr>
                                        <th class="border border-gray-300 px-4 py-2 text-left">TIL Number</th>
                                        <th class="border border-gray-300 px-4 py-2 text-left">Status</th>
                                        @if($this->status=="OVER")
                                            <th class="border border-gray-300 px-4 py-2 text-left">Amount Exceeded</th>
                                        @elseif($this->status=="UNDER")
                                            <th class="border border-gray-300 px-4 py-2 text-left">Pending Amount</th>
                                        @endif
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td class="border border-gray-300 px-4 py-2">{{ $this->til_number }} </td>
                                        <td class="border border-gray-300 px-4 py-2"> {{$this->status}}</td>
                                        <td class="border border-gray-300 px-4 py-2"> @if($this->status=="OVER") {{ number_format($this->overAmount,2) }} TZS
                                            @elseif($this->status=="UNDER")   {{ number_format($this->underAmount,2) }} TZS @endif
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>



                                @if($this->status=="OVER")
                                    <div class="mt-4 w-full">
                                        <p for="userSelected" class="block mb-1 text-sm capitalize text-slate-400 dark:text-white ">SELECT COLLECTION ACCOUNT</p>
                                        <div class="flex gap-4 items-center text-center">
                                            <select  wire:model="account_number_destination" name="setSubMenuPermission" class="mt-1 block w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500" required>
                                                <option> select ...</option>
                                                @foreach(DB::table('accounts')->where('category_code',2800 )->get() as $account)
                                                    <option value="{{$account->account_number}}"> {{$account->account_name}} ({{$account->account_number}})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @elseif($this->status=="UNDER")
                                    <div class="mt-4 w-full">
                                        <p for="userSelected" class="block mb-1 text-sm capitalize text-slate-400 dark:text-white ">ENTER  ACCOUNT NUMBER</p>
                                        <div class="flex gap-4 items-center text-center">
                                            <input  wire:model="account_number" type="text" name="setSubMenuPermission" class="mt-1 block w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500" required />
                                        </div>
                                    </div>

                                    @if($this->account_number)
                                        @if($this->member_information)
                                            <div>
                                                <table>
                                                    <tr>
                                                        <td class="text-md"> MEMBER ACCOUNT NAME</td>
                                                        <td class="text-md leading-light mx-2"> : {{$this->member_information->account_name}}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-md"> MEMBER BRANCH NAME</td>
                                                        <td class="text-md leading-light mx-2"> : {{DB::table('branches')->where('id',$this->member_information->branch_number)->value('name')}}  </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-red-500"> Account does not exist</div>

                                        @endif




                                    @endif

                                    @error('account_number')
                                    <div class="text-red-500">
                                        Account number is not valid
                                    </div>
                                    @enderror
                                @endif



                                <p for="password" class="block mb-1 mt-4 text-sm capitalize text-slate-400 dark:text-white ">ENTER PASSWORD TO CONFIRM</p>
                                <input wire:model.defer="password" id="current_password" type="password" class="mt-1 block w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500" autocomplete="current-password" />
                                <x-jet-input-error for="current_password" class="mt-2" />
                            </div>
                            <div class="mt-2"></div>
                        </div>
                        <!-- Add more form fields as needed -->
                        <div class="flex items-center bg-gray-200 justify-end py-3 sm:px-6 sm:rounded-bl-lg sm:rounded-br-lg">
                            <button type="button" wire:click="$toggle('approveBoolean')" class="mr-4 inline-flex justify-center px-4 py-2 text-sm font-medium   border border-transparent rounded-md  focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2  bg-white">
                                Cancel
                            </button>
                            <button type="submit" wire:click="closeTil" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-red-400 border border-transparent rounded-md  focus-visible:ring-2 focus-visible:ring-offset-2 ">
                                Proceed
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
