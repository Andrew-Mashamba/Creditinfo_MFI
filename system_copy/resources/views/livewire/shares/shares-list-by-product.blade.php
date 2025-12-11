<div class="w-full">
    <!-- message container -->

    @if(Session::get('memberToViewId'))
        <livewire:members.member-view/>
    @elseif(Session::get('memberToEditId'))
        <livewire:members.edit-member/>
    @else

        <div>


                        <div class="flex w-full gap-4">

                            <div  class="metric-card  dark:bg-gray-900 border @if($this->item == 0) bg-blue-200 border-blue-200 dark:border-blue-800  @else bg-white  border-gray-200 dark:border-gray-800 @endif rounded-lg p-4 max-w-72 w-full" >


                                <div class="flex justify-between items-center w-full">
                                    <div class="flex items-center">
                                        <div wire:loading wire:target="visit(0)" >
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin  h-5 w-5 mr-2 stroke-gray-400" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />

                                                </svg>


                                                <p>Please wait...</p>
                                            </div>

                                        </div>
                                        <div wire:loading.remove wire:target="visit(0)">


                                            <p class="flex items-center text-l font-semibold spacing-sm text-slate-600" >Number of products

                                            </p>

                                        </div>

                                    </div>
                                    <div class="flex items-center space-x-5" >

                                        <svg wire:click="visit(0)" xmlns="http://www.w3.org/2000/svg" class="cursor-pointer h-9 bg-slate-50 rounded-full stroke-slate-400 p-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>


                                    </div>
                                </div>


                                <p class="mt-2 text-sm font-semibold @if($this->item == 0) text-white @else text-slate-400 @endif  dark:text-white" >
                                    {{DB::table('sub_products')->where('product_id','3000')->count()}}
                                </p>
                            </div>


                            @foreach(DB::table('sub_products')->where('product_id', '3000')->get() as $product)

                                <div class="metric-card  dark:bg-gray-900 border @if($this->item == $product->id) bg-blue-200 border-blue-200 dark:border-blue-800  @else bg-white  border-gray-200 dark:border-gray-800 @endif rounded-lg p-4 max-w-72 w-full" >
                                    <div class="flex justify-between items-start w-full">
                                        <div class="flex items-center">
                                            <div wire:loading wire:target="visit({{ $product->id }})" >
                                                <div class="flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin  h-5 w-5 mr-2 stroke-gray-400" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                                    </svg>
                                                    <p>Please wait...</p>
                                                </div>

                                            </div>
                                            <div class="flex flex-col " wire:loading.remove wire:target="visit({{ $product->id }})">
                                                <p class="flex items-center text-l mb-3 spacing-sm text-slate-600">{{ $product->sub_product_name }}
                                                </p>
                                                <p class="text-sm"> Total accounts: {{ DB::table('sub_accounts')->where('parent_account_number', $product->product_account)->count() }}</p>
                                                <p class="text-sm">Total balance: {{ number_format(DB::table('sub_accounts')->where('parent_account_number', $product->product_account)->sum('balance'), 0, '', ',') }}</p>


                                            </div>

                                        </div>
                                        <div class="flex items-center space-x-5" >

                                            <svg wire:click="visit({{ $product->id }})" xmlns="http://www.w3.org/2000/svg" class="cursor-pointer h-9 bg-slate-50 rounded-full stroke-slate-400 p-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>


                                        </div>
                                    </div>




                                </div>

                            @endforeach


                        </div>


            <hr class="boder-b-0 my-4"/>
            <div class="bg-white rounded rounded-lg shadow-sm p-4 w-full">
                @if($this->item == 0)
                    <livewire:shares.number-of-products
                        exportable
                        searchable="sub_product_name"
                    />
                @else
                    <livewire:shares.active-shares
                        exportable
                        searchable="member,account_number,status"
                    />
                @endif
            </div>


        </div>
    @endif

