
<div class="w-4/6 flex">

    <div class="w-full">
        <!-- message container -->
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
        </div>


        <div class="bg-gray-100 rounded px-6 rounded-lg shadow-sm  pt-4 pb-4 ">


            <div class="flex items-stretch">

                <div class="w-1/2 mr-2" >

                    <p for="member" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Select Member</p>
                    <select wire:model.bounce="member" name="member" id="member" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option selected value="">Select</option>
                        @foreach(App\Models\ClientsModel::all() as $members)
                            <option value="{{$members->client_number}}">{{$members->first_name}} {{$members->middle_name}} {{$members->last_name}}</option>
                        @endforeach

                    </select>
                    @error('member')
                    <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                        <p>Branch is mandatory.</p>
                    </div>
                    @enderror
                    <div class="mt-2"></div>

                    @if($this->member)
                        <div class="flex justify-between">
                            <p for="member" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Existing shares accounts</p>
                            <div>
                                @foreach(App\Models\AccountsModel::where('member_number',$this->member)->where('product_number','11')->get() as $account)
                                    @php
                                        $this->account_number = $account->account_number;
                                    @endphp
                                    <p class="block mb-2 text-sm font-medium text-red-500 dark:text-gray-400">{{$account->account_number}}</p>
                                @endforeach
                            </div>


                        </div>

                        <div class="mt-2"></div>
                    @endif


                    <p for="product" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Product</p>
                    <select wire:model.bounce="product" name="product" id="product" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option selected value="">Select</option>
                        @foreach(App\Models\sub_products::where('product_id','11')->get() as $product)
                            <option value="{{$product->sub_product_id}}">{{$product->sub_product_name}}</option>
                        @endforeach

                    </select>
                    @error('product')
                    <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                        <p>Branch is mandatory.</p>
                    </div>
                    @enderror
                    <div class="mt-2"></div>


                </div>


                <div class="w-1/2 ml-2" >


                </div>



            </div>


            <div class="flex justify-end w-auto" >
                <div wire:loading wire:target="save" >
                    <button class="text-white bg-blue-400 hover:bg-blue-400 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-400 dark:hover:bg-blue-400 dark:focus:ring-blue-400" disabled>
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin  h-5 w-5 mr-2 stroke-white-800" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />

                            </svg>
                            <p class="text-white">Please wait...</p>
                        </div>
                    </button>
                </div>

            </div>


            <div class="flex justify-end w-auto" >
                <div wire:loading.remove wire:target="save" >
                    <button wire:click="save" class="text-white bg-blue-400 hover:bg-blue-400 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-400 dark:hover:bg-blue-400 dark:focus:ring-blue-400">
                        <p class="text-white">Create Account</p>
                    </button>

                </div>
            </div>

        </div>


    </div>

</div>

