<div>

    <div class="p-2">

    <div class="relative p-4 mb-2 overflow-hidden rounded-lg bg-white" >

        <!-- Background illustration -->
        <div class="absolute right-0 top-0 -mt-4 mr-16 pointer-events-none hidden xl:block" aria-hidden="true">

            <svg width="319" height="198" xmlns:xlink="http://www.w3.org/1999/xlink">
                <defs>
                    <path id="welcome-a" d="M64 0l64 128-64-20-64 20z" />
                    <path id="welcome-e" d="M40 0l40 80-40-12.5L0 80z" />
                    <path id="welcome-g" d="M40 0l40 80-40-12.5L0 80z" />
                    <linearGradient x1="50%" y1="0%" x2="50%" y2="100%" id="welcome-b">
                        <stop stop-color="#2D3D88" offset="0%" /> <!-- Dark Blue -->
                        <stop stop-color="#2D3D88" offset="100%" /> <!-- Light Blue -->
                    </linearGradient>
                    <linearGradient x1="50%" y1="24.537%" x2="50%" y2="100%" id="welcome-c">
                        <stop stop-color="#2D3D88" offset="0%" /> <!-- Light Blue -->
                        <stop stop-color="#2D3D88" stop-opacity="0" offset="100%" /> <!-- Dark Blue -->
                    </linearGradient>
                </defs>
                <g fill="none" fill-rule="evenodd">
                    <g transform="rotate(64 36.592 105.604)">
                        <mask id="welcome-d" fill="#fff">
                            <use xlink:href="#welcome-a" />
                        </mask>
                        <use fill="url(#welcome-b)" xlink:href="#welcome-a" />
                        <path fill="url(#welcome-c)" mask="url(#welcome-d)" d="M64-24h80v152H64z" />
                    </g>
                    <g transform="rotate(-51 91.324 -105.372)">
                        <mask id="welcome-f" fill="#fff">
                            <use xlink:href="#welcome-e" />
                        </mask>
                        <use fill="url(#welcome-b)" xlink:href="#welcome-e" />
                        <path fill="url(#welcome-c)" mask="url(#welcome-f)" d="M40.333-15.147h50v95h-50z" />
                    </g>
                    <g transform="rotate(44 61.546 392.623)">
                        <mask id="welcome-h" fill="#fff">
                            <use xlink:href="#welcome-g" />
                        </mask>
                        <use fill="url(#welcome-b)" xlink:href="#welcome-g" />
                        <path fill="url(#welcome-c)" mask="url(#welcome-h)" d="M40.333-15.147h50v95h-50z" />
                    </g>
                </g>
            </svg>

        </div>

        <!-- Content -->
        <div class="relative w-full">
            <div class="min-w-full text-center text-sm font-light">
                <div class="text-xl text-slate-400 font-bold mb-1 ">
                   MANAGEMENT APPROVAL

                </div>

            </div>
            <div>

                <ul class="max-w-md space-y-1 text-gray-500 list-inside dark:text-gray-400">
                    <li class="flex items-center">
                        <svg class="w-3.5 h-3.5 mr-2 text-blue-900 dark:text-blue-900 flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                        </svg>
                         Total Loans: {{ App\Models\LoansModel::count() }}
                    </li>
                    <li class="flex items-center">
                        <svg class="w-3.5 h-3.5 mr-2 text-blue-900 dark:text-blue-900 flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                        </svg>
                        Total writer off:  0
                    </li>
                    <li class="flex items-center">
                        <svg class="w-3.5 h-3.5 mr-2 text-blue-900 dark:text-blue-900 flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                        </svg>

                       Total recovery
                        <span>
                            <span class="ml-2 bg-red-100 text-red-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-red-400 border border-red-400">  0  </span>
                        </span>

                    </li>
                </ul>
            </div>

        </div>

    </div>








    <div class="bg-white p-4 sm:p-6 overflow-hidden mb-2 rounded-lg">

        <div class="grid grid-cols-4 gap-2 p-2">
            @php
                $menuItems = [
                    ['id' => 1, 'label' => 'Loans'],
                    ['id' => 2, 'label' => 'Expenses'],
                    ['id' => 3, 'label' => 'Budget and Budget changes'],
                    ['id' => 4, 'label' => 'New Hires'],
                    ['id' => 5, 'label' => 'External Accounts'],
                    ['id' => 6, 'label' => 'Member Exit'],
                    ['id' => 7, 'label' => 'End of Year'],


                ];
            @endphp


            @foreach ($menuItems as $menuItem)
                <button
                        wire:click="setView({{ $menuItem['id'] }})"
                        class="flex hover:text-white text-center items-center w-full
        @if ($this->tab_id == $menuItem['id']) bg-blue-900 @else bg-gray-100 @endif
                        @if ($this->tab_id == $menuItem['id']) text-white font-bold @else text-gray-400 font-semibold @endif
                                py-2 px-4 rounded-lg"

                        onmouseover="this.style.backgroundColor='#2D3D88'; this.style.color='white';"
                        onmouseout="this.style.backgroundColor=''; this.style.color='';"
                >
                    <div wire:loading wire:target="setView({{ $menuItem['id'] }})">
                        <svg aria-hidden="true" class="w-8 h-8 mr-2 text-gray-200 animate-spin dark:text-gray-900 fill-red-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                            <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                        </svg>
                    </div>
                    <div wire:loading.remove wire:target="setView({{ $menuItem['id'] }})">
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


        <hr class="h-px my-8 bg-gray-200 border-0 dark:bg-gray-700">

        <div class="tab-pane fade " id="tabs-homeJustify"
             role="tabpanel" aria-labelledby="tabs-home-tabJustify">
            <div class="mt-2"></div>
            <div class="w-full flex items-center justify-center">
                <div wire:loading wire:target="setView">
                    <div class="h-96 m-auto flex items-center justify-center">
                        <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-white"></div>
                    </div>
                </div>
            </div>

            @switch($this->tab_id)
            @case('1')

            @if($this->view_loan)



            <div>{{-- In work, do what you enjoy. --}}
                <div>
                    <nav class="bg-blue-100   rounded-lg pl-2 pr-2 ">
                        <div class="relative flex h-16 items-center justify-between">
                            <div class="flex flex-1 items-center justify-between">
                                <div class="flex flex-shrink-0 items-start">
                                    <div class="">
                                        <div class="flex items-center justify-between">

                                            <p class="font-semibold ml-3 text-slate-600 text-red-900">
                                                {{ App\Models\ClientsModel::where('client_number', Session::get('client_number'))->value('first_name') . ' ' . App\Models\ClientsModel::where('client_number', Session::get('client_number'))->value('middle_name') . ' ' . App\Models\ClientsModel::where('client_number', Session::get('client_number'))->value('last_name') }}
                                            </p>

                                        </div>
                                        <div class="text-red-400 ml-4 mr-5 ">
                                            <div class="text-blue-900 font-bold">
                                                {{ App\Models\ClientsModel::where('client_number', Session::get('client_number'))->value('client_status') }}
                                            </div>
                                        </div>


                                    </div>

                                </div>
                                <div class="flex">
                                    <div class="flex space-x-4">
                                        <!-- Current: "bg-gray-900 text-white", Default:"text-gray-300 hover:bg-gray-700 hover:text-white" -->
                                    </div>
                                    <button type="button"  class="rounded-full bg-white p-1 text-gray-400 hover:text-blue-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                                        <svg wire:click="$toggle('view_loan')"
                                            xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 " fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </nav>
                    <div class="relative flex py-5 items-center">
                        <div class="flex-grow border-t border-gray-400"></div>
                        <span class="flex-shrink mx-4 text-gray-400"> General Info  </span>
                        <div class="flex-grow border-t border-gray-400"></div>
                    </div>

                    @foreach ($loan_general_info as $info )
                        <div
                        class="grid grid-cols-1 sm:grid-cols-2 p-4 border-blue-800 border-2 md:grid-cols-3 gap-6 pb-4 bg-gray-50 rounded-xl p-4 mt-4">

                        <div>
                            <label for="hisa"
                                class="block text-sm font-medium text-gray-700"> Principle Amount</label>
                            <div class="text-sm font-medium text-gray-500"> {{ number_format($info->principle,2) }} TZS </div>
                        </div>

                        <div>
                            <label for="akiba"
                                class="block text-sm font-medium text-gray-700"> Interest </label>
                            <div class="text-sm font-medium text-gray-500">  {{ $info->interest }} %</div>
                        </div>

                        <div>
                            <label for="amana"
                                class="block text-sm font-medium text-gray-700"> Tenure </label>
                            <div class="text-sm font-medium text-gray-500"> {{ $info->tenure }} months</div>
                        </div>

                        <div>
                            <label for="amana"
                                class="block text-sm font-medium text-gray-700"> Outstanding balance </label>
                            <div class="text-sm font-medium text-gray-500">
                                {{
                                number_format(
                                DB::table('loans_schedules')
                                    ->where('loan_id', $info->loan_id)
                                    ->where('completion_status', '!=', "CLOSED")
                                    ->sum('installment'),2)
                                }} TZS </div>
                        </div>



                        <div>
                            <label for="amana"
                                class="block text-sm font-medium text-gray-700"> Collateral Value </label>
                            <div class="text-sm font-medium text-gray-500"> {{ number_format(DB::table('collaterals')
                                ->where('loan_id', $info->id)
                                ->sum(DB::raw('CAST(collateral_value AS DECIMAL(10,2))'))
                            ,2) }} TZS</div>
                        </div>




                        </div>




                    @endforeach

                    <div class="relative flex py-5 items-center">
                        <div class="flex-grow border-t border-gray-400"></div>
                        <span class="flex-shrink mx-4 text-gray-400"> Client  Data </span>
                        <div class="flex-grow border-t border-gray-400"></div>
                    </div>

                    <div class="w-full">
                        @php        $members=\App\Models\ClientsModel::where('client_number',session()->get('client_number'))->get();   @endphp
                        @foreach ($members as $member)
                            {{-- <div class="  w-full  items-center justify-center ">
                                <section
                                    class="bg-white-300 flex flex-col items-center justify-center rounded-full mx-auto"
                                    style="width: 200px; height: 200px;">
                                    @if ($member->profile_photo_path)
                                        <img class="object-fill  rounded-full"
                                            src="{{ $member->profile_photo_path }}"
                                            style="width: 200px; height: 200px;">
                                    @else
                                        <img class="object-fill  rounded-full"
                                            src="{{ asset('images/avatar.png') }}"
                                            style="width: 200px; height: 200px;">
                                    @endif
                                </section>

                            </div> --}}


                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 pb-4 bg-gray-50 rounded-xl p-4 mt-4">

                                <div>
                                    <label for="hisa"
                                        class="block text-sm font-medium text-gray-700">Hisa</label>
                                    <div class="text-sm font-medium text-gray-500">{{ $member->hisa }}</div>
                                </div>

                                <div>
                                    <label for="akiba"
                                        class="block text-sm font-medium text-gray-700">Akiba</label>
                                    <div class="text-sm font-medium text-gray-500">{{ $member->akiba }}</div>
                                </div>

                                <div>
                                    <label for="amana"
                                        class="block text-sm font-medium text-gray-700">Amana</label>
                                    <div class="text-sm font-medium text-gray-500">{{ $member->amana }}</div>
                                </div>

                            </div>



                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 pb-4 bg-gray-50 rounded-xl p-4 mt-4">
                                <div>
                                    <label for="membership_type"
                                        class="block text-sm font-medium text-gray-700">Membership Type</label>
                                    <div class="text-sm font-medium text-gray-500">{{ $member->membership_type }}
                                    </div>
                                </div>

                                <div>
                                    <label for="branch"
                                        class="block text-sm font-medium text-gray-700">Branch</label>
                                    <div class="text-sm font-medium text-gray-500">
                                        {{ App\Models\BranchesModel::find($member->branch)->name }}</div>
                                </div>

                                <div>
                                    <label for="phone_number"
                                        class="block text-sm font-medium text-gray-700">Phone Number</label>
                                    <div class="text-sm font-medium text-gray-500">{{ $member->phone_number }}
                                    </div>
                                </div>

                                @if ($member->membership_type == 'Business' or $member->membership_type == 'Group')
                                    <div>
                                        <label for="business_name"
                                            class="block text-sm font-medium text-gray-700">Business Name</label>
                                        <div class="text-sm font-medium text-gray-500">
                                            {{ $member->business_name }}</div>
                                    </div>
                                @endif

                                @if ($member->membership_type == 'Individual')
                                    <div>
                                        <label for="first_name"
                                            class="block text-sm font-medium text-gray-700">First Name</label>
                                        <div class="text-sm font-medium text-gray-500">{{ $member->first_name }}
                                        </div>
                                    </div>
                                    <div>
                                        <label for="middle_name"
                                            class="block text-sm font-medium text-gray-700">Middle Name</label>
                                        <div class="text-sm font-medium text-gray-500">{{ $member->middle_name }}
                                        </div>
                                    </div>
                                    <div>
                                        <label for="last_name"
                                            class="block text-sm font-medium text-gray-700">Last Name</label>
                                        <div class="text-sm font-medium text-gray-500">{{ $member->last_name }}
                                        </div>
                                    </div>
                                @endif

                                @if ($member->membership_type == 'Business' or $member->membership_type == 'Group')
                                    <div>
                                        <label for="incorporation_number"
                                            class="block text-sm font-medium text-gray-700">Incorporation
                                            Number</label>
                                        <div class="text-sm font-medium text-gray-500">
                                            {{ $member->incorporation_number }}</div>
                                    </div>
                                @endif

                                <div>
                                    <label for="email"
                                        class="block text-sm font-medium text-gray-700">Email</label>
                                    <div class="text-sm font-medium text-gray-500">{{ $member->email }}</div>
                                </div>

                                @if ($member->membership_type == 'Individual')
                                    <div>
                                        <label for="place_of_birth"
                                            class="block text-sm font-medium text-gray-700">Place of Birth</label>
                                        <div class="text-sm font-medium text-gray-500">
                                            {{ $member->place_of_birth }}</div>
                                    </div>
                                    <div>
                                        <label for="marital_status"
                                            class="block text-sm font-medium text-gray-700">Marital Status</label>
                                        <div class="text-sm font-medium text-gray-500">
                                            {{ $member->marital_status }}</div>
                                    </div>
                                @endif

                                <div>
                                    <label for="address"
                                        class="block text-sm font-medium text-gray-700">Address</label>
                                    <div class="text-sm font-medium text-gray-500">{{ $member->address }}</div>
                                </div>

                                @if ($member->membership_type == 'Individual')
                                    <div>
                                        <label for="next_of_kin_name"
                                            class="block text-sm font-medium text-gray-700">Next of Kin
                                            Name</label>
                                        <div class="text-sm font-medium text-gray-500">
                                            {{ $member->next_of_kin_name }}</div>
                                    </div>
                                    <div>
                                        <label for="next_of_kin_phone"
                                            class="block text-sm font-medium text-gray-700">Next of Kin
                                            Phone</label>
                                        <div class="text-sm font-medium text-gray-500">
                                            {{ $member->next_of_kin_phone }}</div>
                                    </div>
                                @endif

                                <div>
                                    <label for="tin_number" class="block text-sm font-medium text-gray-700">TIN
                                        Number</label>
                                    <div class="text-sm font-medium text-gray-500">{{ $member->tin_number }}</div>
                                </div>

                                <div>
                                    <label for="nationarity"
                                        class="block text-sm font-medium text-gray-700">Nationality</label>
                                    <div class="text-sm font-medium text-gray-500">{{ $member->nationarity }}
                                    </div>
                                </div>

                                @if ($member->membership_type == 'Individual')
                                    <div>
                                        <label for="number_of_spouse"
                                            class="block text-sm font-medium text-gray-700">Number of
                                            Spouse</label>
                                        <div class="text-sm font-medium text-gray-500">
                                            {{ $member->number_of_spouse }}</div>
                                    </div>
                                    <div>
                                        <label for="number_of_children"
                                            class="block text-sm font-medium text-gray-700">Number of
                                            Children</label>
                                        <div class="text-sm font-medium text-gray-500">
                                            {{ $member->number_of_children }}</div>
                                    </div>
                                    <div>
                                        <label for="gender"
                                            class="block text-sm font-medium text-gray-700">Gender</label>
                                        <div class="text-sm font-medium text-gray-500">{{ $member->gender }}</div>
                                    </div>
                                    <div>
                                        <label for="date_of_birth"
                                            class="block text-sm font-medium text-gray-700">Date of Birth</label>
                                        <div class="text-sm font-medium text-gray-500">
                                            {{ $member->date_of_birth }}</div>
                                    </div>
                                @endif

                                <div>
                                    <label for="citizenship"
                                        class="block text-sm font-medium text-gray-700">Citizenship</label>
                                    <div class="text-sm font-medium text-gray-500">{{ $member->citizenship }}
                                    </div>
                                </div>

                                @if ($member->membership_type == 'Individual')
                                    <div>
                                        <label for="employment"
                                            class="block text-sm font-medium text-gray-700">Employment</label>
                                        <div class="text-sm font-medium text-gray-500">{{ $member->employment }}
                                        </div>
                                    </div>
                                    <div>
                                        <label for="employer_name"
                                            class="block text-sm font-medium text-gray-700">Employer Name</label>
                                        <div class="text-sm font-medium text-gray-500">
                                            {{ $member->employer_name }}</div>
                                    </div>
                                    <div>
                                        <label for="education"
                                            class="block text-sm font-medium text-gray-700">Education</label>
                                        <div class="text-sm font-medium text-gray-500">{{ $member->education }}
                                        </div>
                                    </div>
                                @endif

                                <div>
                                    <label for="income_available"
                                        class="block text-sm font-medium text-gray-700">Income Available</label>
                                    <div class="text-sm font-medium text-gray-500">{{ $member->income_available }}
                                    </div>
                                </div>

                                <div>
                                    <label for="income_source"
                                        class="block text-sm font-medium text-gray-700">Income Source</label>
                                    <div class="text-sm font-medium text-gray-500">{{ $member->income_source }}
                                    </div>
                                </div>

                                <div>
                                    <label for="guarantor_first_name"
                                        class="block text-sm font-medium text-gray-700">Guarantor First
                                        Name</label>
                                    <div class="text-sm font-medium text-gray-500">
                                        {{ $member->guarantor_first_name }}</div>
                                </div>

                                <div>
                                    <label for="guarantor_middle_name"
                                        class="block text-sm font-medium text-gray-700">Guarantor Middle
                                        Name</label>
                                    <div class="text-sm font-medium text-gray-500">
                                        {{ $member->guarantor_middle_name }}</div>
                                </div>

                                <div>
                                    <label for="guarantor_last_name"
                                        class="block text-sm font-medium text-gray-700">Guarantor Last Name</label>
                                    <div class="text-sm font-medium text-gray-500">
                                        {{ $member->guarantor_last_name }}</div>
                                </div>

                                <div>
                                    <label for="guarantor_full_name"
                                        class="block text-sm font-medium text-gray-700">Guarantor Membership
                                        Number</label>
                                    <div class="text-sm font-medium text-gray-500">
                                        {{ $member->guarantor_full_name }}</div>
                                </div>

                                <div>
                                    <label for="guarantor_email"
                                        class="block text-sm font-medium text-gray-700">Guarantor Email</label>
                                    <div class="text-sm font-medium text-gray-500">{{ $member->guarantor_email }}
                                    </div>
                                </div>


                                <div>
                                    <label for="barua" class="block text-sm font-medium text-gray-700">Barua Ya
                                        Maombi</label>
                                    <!-- Input type file does not have a direct display option -->
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>



            <div class="relative flex py-5 items-center">
                <div class="flex-grow border-t border-gray-400"></div>
                <span class="flex-shrink mx-4 text-gray-400"> Collateral Information </span>
                <div class="flex-grow border-t border-gray-400"></div>
            </div>


            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-6 pb-2 bg-gray-50 rounded-xl p-4 mt-4">

            @foreach ($collaterals as $data )

            <div class="grid grid-cols-2 border-blue-900 border-2 sm:grid-cols-2 md:grid-cols-2 gap-6 pb-4 bg-white rounded-xl p-4 mt-4">

                <div>
                    <span class="bg-blue-900 text-white rounded-full px-3 py-1 text-center">
                        {{ $loop->iteration }}
                      </span>

                  <label for="membership_type"
                      class="block text-sm font-medium text-gray-700"> Collateral Type  </label>
                  <div class="text-sm font-medium text-gray-500">{{ str_replace("_"," ", $data->main_collateral_type) }}
                  </div>
               </div>

               <div>
                <span class="text-white  px-3 py-1 text-center">

                  </span>

                  <label for="membership_type"
                  class="block text-sm font-medium text-gray-700"> Collateral Category  </label>
                 <div class="text-sm font-medium text-gray-500">{{ $data->collateral_category }} </div>
              </div>

              <div>
              <label for="membership_type"
                class="block text-sm font-medium text-gray-700"> Collateral Value  </label>
                <div class="text-sm font-medium text-gray-500">{{  number_format( $data->collateral_value )}} TZS  </div>
              </div>

                <div>
                <label for="membership_type"
                class="block text-sm font-medium text-gray-700"> Account number   </label>
                <div class="text-sm font-medium text-gray-500">{{DB::table('accounts')->where('id',$data->account_id)->value('account_number') }}
                </div>
                </div>

                @if($data->main_collateral_type=="self_guaranted")

                <div>
                    <label for="membership_type"
                    class="block text-sm font-medium text-gray-700"> Member Id   </label>
                    <div class="text-sm font-medium text-gray-500">{{ $data->member_number }}
                    </div>
                    </div>


                    <div>
                        <label for="membership_type"
                        class="block text-sm font-medium text-gray-700"> Member Name    </label>
                        <div class="text-sm font-medium text-gray-500">
                             @php
                                 $member=DB::table('clients')->where('client_number',$data->member_number)->first();

                                 $name = $member->first_name." ". $member->middle_name ." ".  $member->last_name;
                             @endphp
                            {{$name  }}
                        </div>
                        </div>
                @endif
            </div>
            @endforeach
            </div>
            <div class="relative flex py-5 items-center">
                <div class="flex-grow border-t border-gray-400"></div>
                <span class="flex-shrink mx-4 text-gray-400"> Business Information </span>
                <div class="flex-grow border-t border-gray-400"></div>
            </div>


            @foreach ($businessInfo as $value )


            <div class="grid grid-cols-2 border-blue-900 border-2 sm:grid-cols-2 md:grid-cols-2 gap-6 pb-4 bg-white rounded-xl p-4 mt-4">

                <div>
                  <label for="membership_type"
                      class="block text-sm font-medium text-gray-700">  Business Name  </label>
                  <div class="text-sm font-medium text-gray-500">{{ $value->business_name }}
                  </div>
               </div>

               <div>


                <label for="membership_type"
                    class="block text-sm font-medium text-gray-700">  Age of the business
                   </label>
                <div class="text-sm font-medium text-gray-500">{{ $value->business_name }}
                </div>
             </div>


             <div>


                <label for="membership_type"
                    class="block text-sm font-medium text-gray-700">   Business Type

                </label>
                <div class="text-sm font-medium text-gray-500">{{ $value->business_type }}
                </div>
             </div>

             <div>


                <label for="membership_type"
                    class="block text-sm font-medium text-gray-700">   Business Category

                </label>
                <div class="text-sm font-medium text-gray-500">{{ $value->business_category }}
                </div>
             </div>


             <div>


                <label for="membership_type"
                    class="block text-sm font-medium text-gray-700">   Business Licence Number
                </label>
                <div class="text-sm font-medium text-gray-500">{{ $value->business_licence_number}}
                </div>
             </div>



             <div>
                <label for="membership_type"
                    class="block text-sm font-medium text-gray-700">
                    Business TIN Number
                </label>
                <div class="text-sm font-medium text-gray-500">{{ $value->business_tin_number }}
                </div>
             </div>



             <div>
                <label for="membership_type"
                    class="block text-sm font-medium text-gray-700">
                    Business Inventory
                </label>
                <div class="text-sm font-medium text-gray-500">{{ number_format($value->business_inventory,2) }}TZS
                </div>
             </div>

             <div>
                <label for="membership_type"
                    class="block text-sm font-medium text-gray-700">
                    Cash At Hand
                </label>
                <div class="text-sm font-medium text-gray-500">{{ number_format($value->cash_at_hand,2) }} TZS
                </div>
             </div>

             <div>
                <label for="membership_type"
                    class="block text-sm font-medium text-gray-700">
                    Daily Average Sales
                </label>
                <div class="text-sm font-medium text-gray-500">{{  number_format($value->daily_sales ,2)}} TZS
                </div>
             </div>

             <div>
                <label for="membership_type"
                    class="block text-sm font-medium text-gray-700">
                    Cost Of Sales Per Month
                </label>
                <div class="text-sm font-medium text-gray-500">{{ number_format($value->cost_of_goods_sold ,2) }} TZS
                </div>
             </div>

             <div>
                <label for="membership_type"
                    class="block text-sm font-medium text-gray-700">
                    Operating Expenses
                </label>
                <div class="text-sm font-medium text-gray-500">{{ number_format($value->operating_expenses ,2)}} TZS
                </div>
             </div>

             <div>
                <label for="membership_type"
                    class="block text-sm font-medium text-gray-700">
                    Taxes Per Month
                </label>
                <div class="text-sm font-medium text-gray-500">{{ number_format($value->monthly_taxes,2) }} TZS
                </div>
             </div>

             <div>
                <label for="membership_type"
                    class="block text-sm font-medium text-gray-700">
                    Other Expenses
                </label>
                <div class="text-sm font-medium text-gray-500">{{ number_format($value->other_expenses,2 ) }}
                </div>
             </div>

            </div>
            @endforeach


            <div class="relative flex py-5 items-center">
                <div class="flex-grow border-t border-gray-400"></div>
                <span class="flex-shrink mx-4 text-gray-400"> Repayment Scedule  </span>
                <div class="flex-grow border-t border-gray-400"></div>
            </div>

            <div class="grid grid-cols-4 border-blue-900 border-2 p-4 sm:grid-cols-4 md:grid-cols-4 gap-6 pb-4 bg-white rounded-xl  mt-4">


                 <div>
                    <label for="membership_type"
                        class="block text-sm font-medium text-gray-700">
                        Installment
                    </label>

                 </div>


                 <div>
                    <label for="membership_type"
                        class="block text-sm font-medium text-gray-700">
                        Interest
                    </label>

                 </div>

                 <div>
                    <label for="membership_type"
                        class="block text-sm font-medium text-gray-700">
                        Principle
                    </label>

                 </div>


                 <div>
                    <label for="membership_type"
                        class="block text-sm font-medium text-gray-700">
                        Balance
                    </label>

                 </div>









                 @foreach ($this->table as $tr )

                 <div>

                    <div class="text-sm font-medium  border-b border-blue-900  text-gray-500">{{number_format($tr['Payment'],2)}}
                    </div>
                 </div>

                 <div>

                    <div class="text-sm font-medium  border-b border-blue-900  text-gray-500">{{number_format($tr['Interest'],2)}}
                    </div>
                 </div>

                 <div>

                    <div class="text-sm font-medium  border-b border-blue-900  text-gray-500">{{number_format($tr['Principle'],2)}}
                    </div>
                 </div>

                 <div>
                    <div class="text-sm font-medium  border-b border-blue-900  text-gray-500">{{number_format($tr['balance'],2)}}
                    </div>
                 </div>





                 @endforeach

            </div>




            <div class="relative flex py-5 items-center">
                <div class="flex-grow border-t border-gray-400"></div>
                <span class="flex-shrink mx-4 text-gray-400"> Management Approval </span>
                <div class="flex-grow border-t border-gray-400"></div>
            </div>






            <div class="grid grid-cols-2 border-blue-900 border-2 sm:grid-cols-2 md:grid-cols-2 gap-6 pb-4 bg-white rounded-xl p-4 mt-4">

                <div>
                  <label for="membership_type"
                      class="block text-sm font-medium text-gray-700">  Full Name  </label>

               </div>

               <div>
                <label for="membership_type"
                    class="block text-sm font-medium text-gray-700"> Action </label>

             </div>



             @foreach ($manager as  $approver)


             <div>

                <div class="text-sm font-medium text-gray-500">{{ $approver->name}}
                </div>
             </div>

             <div>
              <label for="membership_type"
                  class="block text-sm font-medium text-gray-700">

                @if(\App\Services\ManagementApproval::checkApprovalStatus(session()->get('loan_table_id'), $approver->id )=="PENDING")

                @if(auth()->user()->id == $approver->id )

                @error('description')
                    <div class="text-xs text-red-500"> {{ $message }} </div>
                @enderror


                    @if($this->has_reject)
                    <label for="message" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Your Reason </label>
                    <textarea id="message" wire:model="description" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Write your thoughts here..."></textarea>
                    <button wire:click="Reject({{ $approver->id }}, '{{ session()->get('loan_table_id') }}')" type="button" class="text-white mt-2 bg-gradient-to-br from-red-600 to-red-700 hover:bg-gradient-to-bl focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2"> Reject </button>

                    @else
                    <button wire:click="RejectAction()" type="button" class="text-white bg-gradient-to-br from-red-600 to-red-700 hover:bg-gradient-to-bl focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2"> Reject </button>
                    <button  wire:click="Approve({{ $approver->id }}, '{{ session()->get('loan_table_id') }}')"  type="button" class="text-white mt-2  bg-gradient-to-r from-blue-800 to-blue-900 hover:bg-gradient-to-bl focus:ring-4 focus:outline-none focus:ring-cyan-300 dark:focus:ring-cyan-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2"> Approve</button>


                     @endif



                  @else

                  <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                    <span class="text-yellow-800">
                        {{ \App\Services\ManagementApproval::checkApprovalStatus(session()->get('loan_table_id'), $approver->id ) }}
                    </span>
                </div>


                  @endif

                  @elseif(\App\Services\ManagementApproval::checkApprovalStatus(session()->get('loan_table_id'), $approver->id )=="REJECTED")

                  <span class="inline-flex items-center px-3 py-1 text-sm font-medium text-gray-800 bg-gray-100 rounded-full">
                    {{ \App\Services\ManagementApproval::checkApprovalStatus(session()->get('loan_table_id'), $approver->id ) }}
                </span>
                <label for="message" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                    {{-- {{  $approver->description }} --}}

                </label>


                  @elseif(\App\Services\ManagementApproval::checkApprovalStatus(session()->get('loan_table_id'), $approver->id )=="APPROVED")

                  <span class="inline-flex items-center px-3 py-1 text-sm font-medium text-green-800 bg-green-100 rounded-full">
                   {{ \App\Services\ManagementApproval::checkApprovalStatus(session()->get('loan_table_id'), $approver->id ) }}
                </span>

                  @endif


                </label>
              {{-- <div class="text-sm font-medium text-gray-500">{{ $approver->first_name .' '.$approver->middle_name.' '.$approver->last_name}}
              </div> --}}

           </div>
           @endforeach




            </div>






            @else
                <livewire:management.loan-approval />

                @endif


                @break



            @default

        @endswitch
        </div>


            </div>

        </div>




    </div>
</div>
