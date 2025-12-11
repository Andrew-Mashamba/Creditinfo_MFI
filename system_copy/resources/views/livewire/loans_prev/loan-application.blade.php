<div class="w-full" >




        <!-- message container -->




        <div>
                @if (session()->has('loan_message'))

                    @if (session('alert-class') == 'alert-success')
                        <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md mb-8" role="alert">
                            <div class="flex">
                                <div class="py-1"><svg class="fill-current h-6 w-6 text-teal-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                                <div>
                                    <p class="font-bold">The process is completed</p>
                                    <p class="text-sm">{{ session('loan_message') }} </p>
                                </div>
                            </div>
                        </div>
                    @endif
                        @if (session('alert-class') == 'alert-warning')
                            <div class="bg-yellow-100 border-t-4 border-yellow-500 rounded-b text-yellow-900 px-4 py-3 shadow-md mb-8" role="alert">
                                <div class="flex">
                                    <div class="py-1"><svg class="fill-current h-6 w-6 text-yellow-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                                    <div>
                                        <p class="font-bold">Error !!</p>
                                        <p class="text-sm">{{ session('loan_message') }} </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                @endif
            </div>

    @if(!$this->viewAllClients)
            <livewire:loans.client-loan-table />

    @else
        <nav class="bg-red-100   rounded-lg pl-2 pr-2 shadow-2xl">
            <div class="relative flex h-16 items-center justify-between">
                <div class="flex flex-1 items-center justify-between">
                    <div class="flex flex-shrink-0 items-start">
                        <div class="flex items-center justify-between">
                            <div wire:loading wire:target="menuItemClicked" >
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin  h-5 w-5 mr-2 stroke-gray-400" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />

                                    </svg>
                                    <p>Please wait...</p>
                                </div>

                            </div>
                            <div wire:loading.remove wire:target="menuItemClicked">

                                <div class="flex items-center justify-between">
                                    <div>
                                        <button type="button" class="flex rounded-full bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                                            <?php
                                            $urlValue=  \App\Models\ClientsModel::where('client_number',Session::get('viewMemberLoan'))->value('profile_photo_path');
                                            ?>
                                            @if( $urlValue)
                                                <img src="{{$urlValue}}">
                                            @else
                                                <img class="h-8 w-8 rounded-full" src="{{asset('/images/download-1.png')}}" alt="">
                                            @endif

                                        </button>

                                    </div>
                                    <p class="font-semibold ml-3 text-slate-600 text-green-900">{{App\Models\ClientsModel::where('client_number',Session::get('viewMemberLoan'))->value('first_name').' '.App\Models\ClientsModel::where('client_number',Session::get('viewMemberLoan'))->value('middle_name').' '.App\Models\ClientsModel::where('client_number',Session::get('viewMemberLoan'))->value('last_name')}}</p>

                                </div>
                                <div class="text-red-400 ml-4 mr-5 ">
                                    <div class="ml-12 text-blue-900 font-bold">
                                        {{ strtoupper(App\Models\LoansModel::where('id',Session::get('currentloanID'))->value('status'))}}
                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>
                    <div class="flex ">

                        <button type="button" class="rounded-full bg-white p-1 text-gray-400 hover:text-blue-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                            <svg wire:click="$toggle('viewAllClients')" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 " fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>

                    </div>
                </div>

            </div>

        </nav>

        <livewire:loans.client-table />


{{--        <livewire:loans.client-history />--}}


    @endif


</div>
