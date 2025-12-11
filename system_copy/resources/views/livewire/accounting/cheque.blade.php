<div>
        <div class="border-b border-gray-200 dark:border-gray-700">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500 dark:text-gray-400 w-full">
                <div class=" flex space-x-4 w-1/2">
                <li class="mr-2" wire:click="setView(1)">
                    <a href="#" class="inline-flex p-4 @if($selected == 1) text-blue-600 border-b-2 border-blue-600
                    active dark:text-blue-500 dark:border-blue-500 @else border-transparent hover:text-gray-600
                    hover:border-gray-300 dark:hover:text-gray-300 @endif rounded-t-lg
                      group" @if($selected == 1) aria-current="page" @endif>
                        <svg aria-hidden="true" class="w-5 h-5 mr-2 @if($selected == 1) text-blue-600 dark:text-blue-500
                        @else text-gray-400 group-hover:text-gray-500
                        dark:text-gray-500 dark:group-hover:text-gray-300 @endif " fill="currentColor"
                             viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd"
                                                                                          d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5
                             5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0
                             0010 11z" clip-rule="evenodd"></path></svg>Cheque Leaves
                    </a>
                </li>
                </div>
                <div class=" flex space-x-4 w-1/2">

                <li class="mr-2" wire:click="setView(2)">
                    <a href="#" class="inline-flex p-4 @if($selected == 2) text-blue-600 border-b-2 border-blue-600
                    active dark:text-blue-500 dark:border-blue-500 @else border-transparent hover:text-gray-600
                    hover:border-gray-300 dark:hover:text-gray-300 @endif rounded-t-lg
                      group" @if($selected == 2) aria-current="page" @endif >
                        <svg aria-hidden="true" class="w-5 h-5 mr-2 @if($selected == 2) text-blue-600 dark:text-blue-500
                        @else text-gray-400 group-hover:text-gray-500
                        dark:text-gray-500 dark:group-hover:text-gray-300 @endif "
                             fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5
                            11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0
                            012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2
                            2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>Cheque Book
                    </a>
                </li>
                </div>

            </ul>
        </div>


@if($this->selected==2)
<div class="p-2">


    @php
        $menuItems = [
            ['id' => 1, 'label' => 'New Cheque Book'],


        ];

    @endphp


    @foreach ($menuItems as $menuItem)
        <button
                wire:click="newCheque"
                class="flex hover:text-white text-center items-center
            @if ($this->tab_id == $menuItem['id']) bg-blue-900 @else bg-gray-100 @endif
                @if ($this->tab_id == $menuItem['id']) text-white font-bold @else text-gray-400 font-semibold @endif
                        py-2 px-4 rounded-lg"

                onmouseover="this.style.backgroundColor='#2D3D88'; this.style.color='white';"
                onmouseout="this.style.backgroundColor=''; this.style.color='';"
        >

            <div wire:loading wire:target="newCheque">
                <svg aria-hidden="true" class="w-8 h-8 mr-2 text-gray-200 animate-spin dark:text-gray-900 fill-red-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                </svg>
            </div>
            <div wire:loading.remove wire:target="newCheque">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="red"
                     class="w-4 h-4 mr-2 fill-current">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
            </div>
            {{ $menuItem['label'] }}
        </button>
    @endforeach







</div>
@endif



     @if($this->selected==1)
         <div class="mt-2 ">

         </div>
    <livewire:accounting.cheque-table/>
     @else

        <livewire:accounting.cheque-book/>

       @endif


    @if($this->chequeModal)
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
                        </div>
                        <div class="header-elements text-center justify-center font-bold  stroke-current">
                            <h3 class="fw-bold">
                                ISSUE NEW CHEQUE
                            </h3>
                        </div>
                        <div class="mt-5"> </div>
                        <x-jet-label for="selected_bank" value="{{ __('Select Bank') }}" />
                        <select wire:model.bounce="selected_bank" name="selected_bank" id="selected_bank" class="mt-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <option selected unselected >Select ....</option>
                            @foreach(DB::table('accounts')->where('category_code','1000')->get() as $account)
                                <option  value=" {{$account->id}}">{{$account->account_name}} ({{$account->account_number}})</option>
                            @endforeach
                        </select>
                        @error('selected_bank')
                        <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                            <p>Branch is mandatory.</p>
                        </div>
                        @enderror

                         @if($selected_bank)

                            <div class="mt-1 mx-2">
                                <div class="fw-bold">

                                    <div class="max-w-md mx-auto bg-white p-4 rounded-lg shadow">
                                        <table class="w-full">
                                            <thead>
                                            <tr>
                                                <td class="py-2">Balance :</td>
                                                <td>{{ number_format($this->bank_present_value) }} TZS</td>
                                            </tr>

                                        </table>
                                    </div>
                                </div>
                            </div>

                        @endif

                        @error('cheque_book_present_value')
                        <div class="border rounded-b text-red-100 px-4 py-3 text-red-700 mt-1">
                            <p> Account selected do not have cheque book number  </p>
                        </div>
                        @enderror


                        <x-jet-label for="cheque_number" value="{{ __('Enter Cheque Number') }}" />
                        <input id="cheque_number" name="cheque_number" type="text" class="mt-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model.bounce="cheque_number" autofocus >
                        </input>
                        @error('cheque_number')
                        <div class="border border-red-400 rounded-b text-red-100 px-4 py-3 text-red-700 mt-1">
                            <p> cheque number  is mandatory</p>
                        </div>
                        @enderror
                        <div class="mt-2"></div>

                        <div class="mt-2">

                        </div><div class="mt-2"></div>

                    </div>
                    <!-- Add more form fields as needed -->
                    <div class="flex items-center bg-gray-200 justify-end py-3 sm:px-6 sm:rounded-bl-lg sm:rounded-br-lg">
                        <button type="button" wire:click="$toggle('chequeModal')" class="mr-4 inline-flex justify-center px-4 py-2 text-sm font-medium   border border-transparent rounded-md  focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2  bg-white">
                            Cancel
                        </button>
                        <button type="submit" wire:click="issueNewCheque" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-blue-400 border border-transparent rounded-md  focus-visible:ring-2 focus-visible:ring-offset-2 ">
                            Proceed
                        </button>
                    </div>
                </div>
            </div>
        </div>

    @endif


    @if($this->newChqueBookModal)

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
                        </div>
                        <div class="header-elements text-center justify-center font-bold  stroke-current">
                            <h3 class="fw-bold">
                                REGISTER NEW CHEQUE BOOK
                            </h3>
                        </div>

                        <div class="mt-5"> </div>
                        <div class="mt-5"> </div>
                        <x-jet-label for="account_id" value="{{ __('Select Bank') }}" />
                        <select wire:model="account_id" name="account_id" id="account_id" class="mt-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <option selected unselected >Select ....</option>
                            @foreach(DB::table('accounts')->where('category_code','1000')->get() as $account)
                                <option  value=" {{$account->id}}">{{$account->account_name}} ({{$account->account_number}})</option>
                            @endforeach
                        </select>
                        @error('account_id')
                        <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                            <p>bank is mandatory.</p>
                        </div>
                        @enderror

                        <x-jet-label for="leave_number" value="{{ __('Number of Leaves ') }}" />
                        <input id="leave_number" name="leave_number" type="number" class="mt-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model="leave_number" autofocus >

                        @error('leave_number')
                        <div class="border border-red-400 rounded-b text-red-100 px-4 py-3 text-red-700 mt-1">
                            <p> number of leaves  is mandatory</p>
                        </div>
                        @enderror
                        <div class="mt-2"></div>

                        <div class="mt-2">

                        </div><div class="mt-2"></div>

                    </div>
                    <!-- Add more form fields as needed -->
                    <div class="flex items-center bg-gray-200 justify-end py-3 sm:px-6 sm:rounded-bl-lg sm:rounded-br-lg">
                        <button type="button" wire:click = "$toggle('newChqueBookModal')" class="mr-4 inline-flex justify-center px-4 py-2 text-sm font-medium   border border-transparent rounded-md  focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2  bg-white">
                            Cancel
                        </button>
                        <button type="submit" wire:click="addChequeBook" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-red-400 border border-transparent rounded-md  focus-visible:ring-2 focus-visible:ring-offset-2 ">
                            Proceed
                        </button>
                    </div>
                </div>
            </div>
        </div>

    @endif



    {{-- If you look to others for fulfillment, you will never truly be fulfilled. --}}
</div>
