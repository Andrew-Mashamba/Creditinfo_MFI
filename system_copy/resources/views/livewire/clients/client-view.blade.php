
<div class="w-full">
    <!-- message container -->
    <div class="h-16 border flex justify-between items-center w-full px-5 py-2 shadow-sm mb-4">

        <div class="flex items-center space-x-5" hidden>
            <svg xmlns="http://www.w3.org/2000/svg" wire:click="back" class="cursor-pointer h-12 bg-slate-50 rounded-full stroke-blue-400 p-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>


        </div>

        <div wire:loading wire:target="back" >

            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin  h-5 w-5 mr-2 stroke-white-800" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />

                </svg>
                <p>Please wait...</p>
            </div>

        </div>

        <div wire:loading.remove wire:target="back" >


        </div>

    </div>

    <div class="w-full h-full grid justify-items-center">
        @foreach($this->member as $currentClient)
            <div class="w-fit m-auto grid justify-items-center">
                <div class="w-fit text-center m-4" >

                    <div style="display: flex; justify-content: center;">
                        <img class="mb-3 w-20 h-20 rounded-full shadow-lg"
                             src="http://96.46.181.165/projects/pamojacbs/storage/app/public/{{$currentClient->profile_photo_path}}"
                             alt="{{$currentClient->first_name}}"/>
                    </div>

                    <p class="text-2xl mt-4 border-b-2 border-b-blue-400 ">{{$currentClient->first_name}} {{$currentClient->middle_name}} {{$currentClient->last_name}}</p>
                    <p class="m-4">{{$currentClient->address}}</p>
                </div>
                <div class="w-fit bg-gray-200 rounded-lg pl-2 pr-2 pt-2 pb-2 mb-2 ml-2 mr-2">
                    <!-- message container -->

                    <div>

                        <div class="flex">

                            <div class="container mx-auto ">
                                <div class="flex flex-col w-full" >

                                    <div class="grid gap-2 grid-cols-1 sm:grid-cols-2 mb-2">


                                        <div class="metric-card  dark:bg-gray-900 border bg-white  border-gray-200 dark:border-gray-800 rounded-lg p-4 max-w-72 w-full" >


                                            <table>

                                                <tbody class="bg-white">
                                                guydtyyu
                                                @foreach ($variables as $variable)
                                                    <tr class="whitespace-nowrap">
                                                        <td class="px-6 py-4 text-sm text-gray-500 font-semibold">
                                                            <p>{{ $variable['label'] }}</p>
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <p class="text-sm text-gray-900">
                                                                {{ $currentClient->{$variable['name']} }}
                                                            </p>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="metric-card  dark:bg-gray-900 border bg-white  border-gray-200 dark:border-gray-800 rounded-lg p-4 max-w-72 w-full" >
                                            <table>
                                                <tbody class="bg-white">
                                                <tr class="whitespace-nowrap">
                                                    <td class="px-6 py-4 text-sm text-gray-500 font-semibold">
                                                        <p> Clientship Type</p>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <p class="text-sm text-gray-900">
                                                            {{$currentClient->membership_type}}
                                                        </p>
                                                    </td>
                                                </tr>
                                                <tr class="whitespace-nowrap">
                                                    <td class="px-6 py-4 text-sm text-gray-500 font-semibold">
                                                        <p> Business Name</p>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <p class="text-sm text-gray-900 whitespace-normal">
                                                            {{$currentClient->business_name}}
                                                        </p>
                                                    </td>
                                                </tr>
                                                <tr class="whitespace-nowrap">
                                                    <td class="px-6 py-4 text-sm text-gray-500 font-semibold">
                                                        <p> Incorporation Number</p>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <p class="text-sm text-gray-900">
                                                            {{$currentClient->incorporation_number}}
                                                        </p>
                                                    </td>
                                                </tr>
                                                <tr class="whitespace-nowrap">
                                                    <td class="px-6 py-4 text-sm text-gray-500 font-semibold">
                                                        <p> Address</p>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <p class="text-sm text-gray-900 whitespace-normal">
                                                            {{$currentClient->address}}
                                                        </p>
                                                    </td>
                                                </tr>
                                                <tr class="whitespace-nowrap">
                                                    <td class="px-6 py-4 text-sm text-gray-500 font-semibold">
                                                        <p> Street</p>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <p class="text-sm text-gray-900 whitespace-normal">
                                                            {{$currentClient->street}}
                                                        </p>
                                                    </td>
                                                </tr>
                                                <tr class="whitespace-nowrap">
                                                    <td class="px-6 py-4 text-sm text-gray-500 font-semibold">
                                                        <p> Notes</p>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <p class="text-sm text-gray-900 whitespace-normal">
                                                            {{$currentClient->notes}}
                                                        </p>
                                                    </td>

                                                </tr>


                                                </tbody>
                                            </table>


                                        </div>



                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-1 w-full">

                                        <div class="metric-card  dark:bg-gray-900 border bg-white  border-gray-200 dark:border-gray-800 rounded-lg p-4 max-w-72 w-full" >

                                            <table class="w-full" >


                                                <tr >
                                                    <td class="mt-2 text-sm font-semibold  text-slate-400 dark:text-white capitalize  ">
                                                        <p class="flex items-center text-l font-semibold spacing-sm text-slate-600">
                                                            Loans
                                                        </p>

                                                        <div class="flex-grow border-t border-gray-400"></div>
                                                    </td>
                                                </tr>

                                                @foreach($this->loanStatus as $loan)

                                                    <tr >
                                                        <td class="mt-2 text-sm font-semibold  text-slate-400 dark:text-white capitalize  ">
                                                            <p> {{App\Models\sub_products::where('product_id',$loan->loan_sub_product)->value('sub_product_name')}}</p>
                                                        </td>
                                                        <td class="pl-2 mt-2 text-sm font-semibold spacing-sm  text-slate-400 dark:text-white">

                                                            <p>{{strtolower($loan->account_number)}}</p>
                                                        </td>
                                                        <td class="pl-2 mt-2 text-l font-bold font-semibold spacing-sm  text-black dark:text-white text-right">

                                                            <p>{{number_format($loan->principle,2)}} TZS</p>
                                                        </td>
                                                    </tr>


                                                @endforeach

                                            </table>








                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>

                    </div>

                </div>
            </div>


        @endforeach

    </div>




</div>
