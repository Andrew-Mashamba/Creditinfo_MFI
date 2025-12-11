<div class="bg-white rounded rounded-lg shadow-md p-4">

    @if (session()->has('message'))
        <div class="alert alert-success text-green-600">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger text-red-600">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex items-center text-sm mb-2 font-semibold text-slate-600">
        POST A TRANSACTIONx
    </div>

    <div class="flex items-stretch p-2 bg-gray-100 gap-2 rounded-md">
        <!-- Debit Section -->
        <div class="w-1/2  rounded-md p-4 bg-white">
            <div class="flex items-center text-sm mb-2 font-semibold text-slate-600">
                SOURCE ACCOUNT

            </div>
            <hr class="boder-b-0 my-6"/>

            @if($this->left_account)
                <p for="stability" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400"> Summary </p>
                <div id="stability" class="w-full bg-gray-50 rounded rounded-lg shadow-sm   p-1 mb-4" >
                    <div class="w-full bg-white rounded rounded-lg shadow-sm  p-2 " >




                        <table class="w-full ">

                            <tbody>





                            <tr class=" border-slate-100 border-b p-4">
                                <td class="text-xs text-slate-400 dark:text-white capitalize  text-left">
                                    <p> Account Name </p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white text-right">

                                    <p class="text-red-500 font-bold  ">
                                        {{DB::table('accounts')->where('account_number', $this->left_account)->value('account_name')}}
                                    </p>


                                </td>
                            </tr>

                            <tr class=" border-slate-100 border-b p-4">
                                <td class="text-xs text-slate-400 dark:text-white capitalize  text-left">
                                    <p> Account Number</p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white text-right">

                                    <p class="text-red-500 font-bold  ">
                                        {{$this->left_account}}
                                    </p>


                                </td>
                            </tr>

                            <tr class=" border-slate-100 border-b p-4">
                                <td class="text-xs text-slate-400 dark:text-white capitalize  text-left">
                                    <p> Account Balance </p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white text-right">

                                    <p class="text-red-500 font-bold  ">
                                        {{DB::table('accounts')->where('account_number', $this->left_account)->value('balance')}}
                                    </p>


                                </td>
                            </tr>



                            </tbody>
                        </table>

                    </div>
                </div>
            @endif



        </div>

        <!-- Credit Section -->
        <div class="w-1/2  rounded-md p-4 bg-white">
            <div class="flex items-center text-sm mb-2 font-semibold text-slate-600">
                DESTINATION ACCOUNT
            </div>
            <hr class="boder-b-0 my-6"/>
            <label for="right_category" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">
                Select Credit Category
            </label>
            <select wire:model="right_category" id="right_category"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5">
                <option value="">Select Category</option>
                @foreach($gl_accounts as $account)
                    <option value="{{ $account->account_code }}">{{ $account->account_name }}</option>
                @endforeach
            </select>
            @error('right_category')
            <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror

            @if($right_category)
                <!-- Credit Sub Category -->
                <div class="mt-2">
                    <label for="right_subcategory" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">
                        Select Credit Subcategory
                    </label>
                    <select wire:model="right_subcategory" id="right_subcategory"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5">
                        <option value="">Select Subcategory</option>
                        @foreach($right_subcategories as $subcategory)
                            <option value="{{ $subcategory['category_code'] }}">{{ $subcategory['account_name'] }}</option>
                        @endforeach
                    </select>
                    @error('right_subcategory')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            @endif

            @if($right_subcategory)
                <div class="mt-2">
                    <label for="right_account" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">
                        Select Credit Account
                    </label>
                    <select wire:model="right_account" id="right_account"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5">
                        <option value="">Select Account</option>
                        @foreach($right_accounts as $account)
                            <option value="{{ $account['account_number'] }}">
                                {{ $account['account_name'] }} - {{ $account['account_number'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('right_account')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            @endif


            @if($this->right_account)

                <hr class="boder-b-0 my-6"/>



                <p for="stability1" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400"> Summary </p>
                <div id="stability1" class="w-full bg-gray-50 rounded rounded-lg shadow-sm   p-1 mb-4" >
                    <div class="w-full bg-white rounded rounded-lg shadow-sm  p-2 " >




                        <table class="w-full ">

                            <tbody>





                            <tr class=" border-slate-100 border-b p-4">
                                <td class="text-xs text-slate-400 dark:text-white capitalize  text-left">
                                    <p> Account Name </p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white text-right">

                                    <p class="text-red-500 font-bold  ">
                                        {{DB::table('accounts')->where('account_number', $this->right_account)->value('account_name')}}
                                    </p>


                                </td>
                            </tr>

                            <tr class=" border-slate-100 border-b p-4">
                                <td class="text-xs text-slate-400 dark:text-white capitalize  text-left">
                                    <p> Account Number</p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white text-right">

                                    <p class="text-red-500 font-bold  ">
                                        {{$this->right_account}}
                                    </p>


                                </td>
                            </tr>

                            <tr class=" border-slate-100 border-b p-4">
                                <td class="text-xs text-slate-400 dark:text-white capitalize  text-left">
                                    <p> Account Balance </p>
                                </td>
                                <td class="text-xs text-slate-400 dark:text-white text-right">

                                    <p class="text-red-500 font-bold  ">
                                        {{DB::table('accounts')->where('account_number', $this->right_account)->value('balance')}}
                                    </p>


                                </td>
                            </tr>



                            </tbody>
                        </table>

                    </div>
                </div>

            @endif


        </div>


    </div>


    <div class="flex items-stretch p-2 bg-gray-100 gap-2 rounded-md">

        <div class="rounded-md p-4 bg-white w-full">


            <div class="grid gap-2 mb-2 grid-cols-3 w-full">
                <div>
                    <label class="block mb-2 text-xs font-medium text-gray-900 dark:text-white">Amount</label>
                    <input wire:model="amount" type="number" class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-blue-500
                            focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500
                            dark:focus:border-blue-500" placeholder="" required />
                </div>
                <div>
                    <label class="block mb-2 text-xs font-medium text-gray-900 dark:text-white">Narration</label>
                    <input wire:model="narration" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-blue-500
                            focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500
                            dark:focus:border-blue-500" placeholder="" required />
                </div>
                <div >
                    <!-- Submit Button -->
                    <div class="flex justify-end mt-4">
                        <div wire:loading wire:target="post">
                            <button class="text-white bg-blue-400 hover:bg-blue-400 font-medium rounded-lg text-sm px-4 py-2" disabled>
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin h-5 w-5 mr-2" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v4m0 4v8m4-8H8m12 0a8 8 0 11-16 0 8 8 0 0116 0z" />
                                    </svg>
                                    Posting...
                                </div>
                            </button>
                        </div>
                        <button wire:loading.remove wire:click="post" class="text-white bg-blue-900 hover:bg-blue-800 font-medium rounded-lg text-sm px-4 py-2">
                            Post Transaction
                        </button>
                    </div>


                </div>
            </div>



        </div>

    </div>



</div>
