
<div>


    <div class="w-full">
        <!-- message container -->

        <div class="w-full h-full grid justify-items-center">

            <div class="flex justify-center px-4 pt-4 pb-4">
                <div class=" flex flex-row gap-2">

                        <input wire:model.live.debounce="client_number" type="number" id="client_number" class="inline-flex items-center py-2 px-4 text-xs font-medium text-center text-gray-900
                            bg-white rounded-lg border border-gray-300 hover:bg-gray-100 focus:ring-4
                            focus:outline-none focus:ring-gray-200 dark:bg-gray-800 dark:text-white dark:border-gray-600
                            dark:hover:bg-gray-700 dark:hover:border-gray-700 dark:focus:ring-gray-700
                         dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Guarantor Number" @disabled(Session::get('disableInputs'))>


                        <input wire:model.bounce="relationship" type="text" id="relationship" class="inline-flex items-center py-2 px-4 text-xs font-medium text-center text-gray-900
                            bg-white rounded-lg border border-gray-300 hover:bg-gray-100 focus:ring-4
                            focus:outline-none focus:ring-gray-200 dark:bg-gray-800 dark:text-white dark:border-gray-600
                            dark:hover:bg-gray-700 dark:hover:border-gray-700 dark:focus:ring-gray-700
                         dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Relationship" @disabled(Session::get('disableInputs'))>



                </div>




                <div class="flex justify-end w-auto" >
                    <div wire:loading wire:target="set" >
                        <button class="ml-2 inline-flex items-center py-2 px-4 text-xs font-medium text-center text-gray-900
                            bg-white rounded-lg border border-gray-300 hover:bg-gray-100 focus:ring-4
                            focus:outline-none focus:ring-gray-200 dark:bg-gray-800 dark:text-white dark:border-gray-600
                            dark:hover:bg-gray-700 dark:hover:border-gray-700 dark:focus:ring-gray-700
                         dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" disabled>
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin  h-5 w-5 mr-2 stroke-white-800" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />

                                </svg>
                                <p>Please wait...</p>
                            </div>
                        </button>
                    </div>

                </div>


                <div class="flex justify-end w-auto" >
                    <div wire:loading.remove wire:target="set" >
                        <button wire:click="set" class="ml-2 inline-flex items-center py-2 px-4 text-xs font-medium text-center text-gray-900
                            bg-white rounded-lg border border-gray-300 hover:bg-gray-100 focus:ring-4
                            focus:outline-none focus:ring-gray-200 dark:bg-gray-800 dark:text-white dark:border-gray-600
                            dark:hover:bg-gray-700 dark:hover:border-gray-700 dark:focus:ring-gray-700
                         dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" @disabled(Session::get('disableInputs'))>
                            <p class="text-gray-700">Set</p>
                        </button>
                    </div>
                </div>
            </div>
            
          @if($this->client)
                @foreach($this->client as $currentClient)
                    <div class="w-fit m-auto grid justify-items-center">
                        <div class="w-fit text-center m-4" >

                            <div style="display: flex; justify-content: center;">
                                @if($currentClient->profile_photo_path)
                                    <img class="mb-3 w-32 h-32 rounded-full shadow-lg"
                                         src="{{$currentClient->profile_photo_path}}"
                                         alt="{{$currentClient->first_name}}"/>
                                @else
                                    <img class="mb-3 w-32 h-32 rounded-full shadow-lg" src="{{asset('/images/download-1.png')}}" alt="">

                                @endif
                            </div>
                            <p class="text-2xl mt-4 border-b-2 border-b-blue-400 ">{{$currentClient->first_name}} {{$currentClient->middle_name}} {{$currentClient->last_name}}</p>
                            <p class="m-4">{{$currentClient->address}}</p>


                        </div>







                        <div class="w-full bg-gray-200 rounded-lg pl-2 pr-2 pt-2 pb-2 mb-2 ml-2 mr-2">
                            <!-- message container -->

                            <div>
                                <div class="flex">
                                    <div class="container mx-auto ">
                                        <div class="flex flex-col w-full" >
                                            <div class="grid gap-2 grid-cols-1 sm:grid-cols-3 mb-2">
                                                <div class="metric-card  dark:bg-gray-900 border bg-white  border-gray-200 dark:border-gray-800 rounded-lg p-4 max-w-72 w-full" >

                                                    <table>
                                                        <tbody class="bg-white">
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p> Customer Code

                                                                </p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900">
                                                                    {{$currentClient->customer_code

    }}
                                                                </p>
                                                            </td>
                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Present Surname</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900">
                                                                    {{$currentClient->present_surname

    }}
                                                                </p>
                                                            </td>
                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Birth Surname</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900">
                                                                    {{$currentClient->birth_surname}}
                                                                </p>
                                                            </td>
                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>First Name</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900">
                                                                    {{$currentClient->first_name}}
                                                                </p>
                                                            </td>

                                                        </tr>


                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Middle Names</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900">
                                                                    {{$currentClient->middle_names}}
                                                                </p>
                                                            </td>

                                                        </tr>

                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Full Name</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900">
                                                                    {{$currentClient->full_name}}
                                                                </p>
                                                            </td>

                                                        </tr>

                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Number of Spouse</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900">
                                                                    {{$currentClient->number_of_spouse}}
                                                                </p>
                                                            </td>

                                                        </tr>

                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Number of Children</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900">
                                                                    {{$currentClient->number_of_children}}
                                                                </p>
                                                            </td>

                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Classification of Individual</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900">
                                                                    {{$currentClient->classification_of_individual}}
                                                                </p>
                                                            </td>

                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Gender</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900">
                                                                    {{$currentClient->gender}}
                                                                </p>
                                                            </td>

                                                        </tr>

                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Date of Birth</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900">
                                                                    {{$currentClient->date_of_birth}}
                                                                </p>
                                                            </td>

                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Country of Birth</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900">
                                                                    {{$currentClient->country_of_birth}}
                                                                </p>
                                                            </td>

                                                        </tr>

                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Marital Status</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900">
                                                                    {{$currentClient->marital_status}}
                                                                </p>
                                                            </td>

                                                        </tr>   <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Fate Status</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900">
                                                                    {{$currentClient->fate_status}}
                                                                </p>
                                                            </td>

                                                        </tr>

                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Social Status</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900">
                                                                    {{$currentClient->social_status}}
                                                                </p>
                                                            </td>

                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Residency</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900">
                                                                    {{$currentClient->residency}}
                                                                </p>
                                                            </td>

                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Citizenship</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900">
                                                                    {{$currentClient->citizenship}}
                                                                </p>
                                                            </td>

                                                        </tr>





                                                        </tbody>
                                                    </table>


                                                </div>


                                                <div class="metric-card  dark:bg-gray-900 border bg-white  border-gray-200 dark:border-gray-800 rounded-lg p-4 max-w-72 w-full" >

                                                    <table>

                                                        <tbody class="bg-white">
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p> Nationality</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900">
                                                                    {{$currentClient->nationality}}
                                                                </p>
                                                            </td>

                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Employment</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->Employment}}
                                                                </p>
                                                            </td>

                                                        </tr>

                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p> Employer Name</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900">
                                                                    {{$currentClient->employer_name}}
                                                                </p>
                                                            </td>

                                                        </tr>

                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p> Education</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->education}}
                                                                </p>
                                                            </td>

                                                        </tr>

                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p> Income Available</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->income_available}}
                                                                </p>
                                                            </td>

                                                        </tr>

                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p> Monthly Expenses</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->monthly_expenses}}
                                                                </p>
                                                            </td>

                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p> Negative Status of Individual</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->negative_status_of_individual}}
                                                                </p>
                                                            </td>

                                                        </tr>

                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p> Tax Identification Number</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->tax_identification_number}}
                                                                </p>
                                                            </td>

                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>National ID</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->national_iD}}
                                                                </p>
                                                            </td>

                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Passport Number</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->passport_number}}
                                                                </p>
                                                            </td>

                                                        </tr>   <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Passport Issuer Country</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->passport_issuer_country}}
                                                                </p>
                                                            </td>

                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Driving License Number</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->driving_license_number}}
                                                                </p>
                                                            </td>

                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Voter's ID</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->voters_iD}}
                                                                </p>
                                                            </td>

                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Foreign Unique ID</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->foreign_unique_iD}}
                                                                </p>
                                                            </td>

                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Custom ID Number 1</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->custom_id_number_1}}
                                                                </p>
                                                            </td>

                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Custom ID Number 2</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->custom_id_number_2}}
                                                                </p>
                                                            </td>

                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Main Address</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->main_address}}
                                                                </p>
                                                            </td>

                                                        </tr>


                                                        </tbody>
                                                    </table>



                                                </div>

                                                <div class="metric-card  dark:bg-gray-900 border bg-white  border-gray-200 dark:border-gray-800 rounded-lg p-4 max-w-72 w-full" >


                                                    <table>

                                                        <tbody class="bg-white">
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p> Street</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900">
                                                                    {{$currentClient->street}}
                                                                </p>
                                                            </td>

                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Number of Building</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->number_of_building}}
                                                                </p>
                                                            </td>

                                                        </tr>

                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p> Postal Code</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900">
                                                                    {{$currentClient->postal_code}}
                                                                </p>
                                                            </td>

                                                        </tr>

                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p> Region</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->region}}
                                                                </p>
                                                            </td>

                                                        </tr>

                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p> District</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->district}}
                                                                </p>
                                                            </td>

                                                        </tr>

                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p> Country</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->country}}
                                                                </p>
                                                            </td>

                                                        </tr>   <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p> Mobile Phone</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->mobile_phone}}
                                                                </p>
                                                            </td>

                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p> Fixed Line</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->fixed_line}}
                                                                </p>
                                                            </td>

                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p> Email</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->email}}
                                                                </p>
                                                            </td>

                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p> Web Page</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->web_page}}
                                                                </p>
                                                            </td>

                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p> Trade Name</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->trade_name}}
                                                                </p>
                                                            </td>

                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Legal Form</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->legal_form}}
                                                                </p>
                                                            </td>

                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Establishment Date</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->establishment_date}}
                                                                </p>
                                                            </td>

                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Registration Country</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->registration_country}}
                                                                </p>
                                                            </td>

                                                        </tr> <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Industry Sector</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->industry_sector}}
                                                                </p>
                                                            </td>

                                                        </tr>
                                                        <tr class="whitespace-nowrap">
                                                            <td class="px-6 py-4 text-xs text-gray-500 font-semibold">
                                                                <p>Registration Number</p>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <p class="text-xs text-gray-900 whitespace-normal">
                                                                    {{$currentClient->registration_number}}
                                                                </p>
                                                            </td>

                                                        </tr>


                                                        </tbody>
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


            @else

            @endif

        </div>




    </div>

</div>

