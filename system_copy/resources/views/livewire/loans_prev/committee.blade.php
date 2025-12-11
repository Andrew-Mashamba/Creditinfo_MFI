
<div>
    @if(Session::get('currentloanClient'))


        <div class="w-full" >


            <div class="h-16 border-b flex justify-between items-center w-full px-5 py-2 shadow-sm">
                <div class="flex items-center ">
                    <div wire:loading wire:target="menuItemClicked" >
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin  h-5 w-5 mr-2 stroke-gray-400" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />

                            </svg>
                            <p>Please wait...</p>
                        </div>

                    </div>
                    <div wire:loading.remove wire:target="menuItemClicked">


                        <p class="font-semibold ml-3 text-slate-600">{{App\Models\Clients::where('client_number',Session::get('currentloanClient'))->value('first_name').' '.App\Models\Clients::where('client_number',Session::get('currentloanClient'))->value('middle_name').' '.App\Models\Clients::where('client_number',Session::get('currentloanClient'))->value('last_name')}}</p>

                    </div>

                </div>
                <div class="flex items-center space-x-5">

                    <svg wire:click="close" xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 bg-slate-50 rounded-full stroke-red-400 p-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>

                </div>
            </div>


            <div class="bg-white w-full bg-gray-200 rounded-lg pl-1 pr-1 pt-1 pb-1 m-1">
                <ul class="bg-white nav nav-tabs nav-justified flex flex-col md:flex-row flex-wrap list-none border-b-0 pl-0 mb-1" id="tabs-tabJustify" role="tablist">
                    <li class="nav-item flex-grow text-center" role="presentation" wire:click="showTab('member')">
                        <a href="#" class="nav-link w-full block font-medium text-xs leading-tight uppercase border-x-0 border-t-0 border-b-2 border-transparent px-6 py-3 my-2 hover:border-transparent hover:bg-gray-100 focus:border-transparent active">
                            <p>Client</p>
                        </a>
                    </li>
                    <li class="nav-item flex-grow text-center" role="presentation" wire:click="showTab('guarantor')">
                        <a href="#" class="nav-link w-full block font-medium text-xs leading-tight uppercase border-x-0 border-t-0 border-b-2 border-transparent px-6 py-3 my-2 hover:border-transparent hover:bg-gray-100 focus:border-transparent">
                            <p>Guarantor</p>
                        </a>
                    </li>
                    <li class="nav-item flex-grow text-center" role="presentation" wire:click="showTab('business')">
                        <a href="#" class="nav-link w-full block font-medium text-xs leading-tight uppercase border-x-0 border-t-0 border-b-2 border-transparent px-6 py-3 my-2 hover:border-transparent hover:bg-gray-100 focus:border-transparent">
                            <p>Business</p>
                        </a>
                    </li>
                    <li class="nav-item flex-grow text-center" role="presentation" wire:click="showTab('collateral')">
                        <a href="#" class="nav-link w-full block font-medium text-xs leading-tight uppercase border-x-0 border-t-0 border-b-2 border-transparent px-6 py-3 my-2 hover:border-transparent hover:bg-gray-100 focus:border-transparent">
                            <p>Collateral</p>
                        </a>
                    </li>
                    <li class="nav-item flex-grow text-center" role="presentation" wire:click="showTab('assessment')">
                        <a href="#" class="nav-link w-full block font-medium text-xs leading-tight uppercase border-x-0 border-t-0 border-b-2 border-transparent px-6 py-3 my-2 hover:border-transparent hover:bg-gray-100 focus:border-transparent">
                            <p>Assessment</p>
                        </a>
                    </li>
                </ul>
                <div class="bg-white tab-content" id="tabs-tabContentJustify">
                    <div class="tab-pane fade show active" id="tabs-homeJustify" role="tabpanel" aria-labelledby="tabs-home-tabJustify">
                        @if ($activeTab === 'member')
                            <livewire:loans.member-info/>
                        @endif
                    </div>
                    <div class="tab-pane fade" id="tabs-homeJustifyx" role="tabpanel" aria-labelledby="tabs-home-tabJustifyx">
                        @if ($activeTab === 'guarantor')
                            <livewire:loans.guarantor-info/>
                        @endif
                    </div>
                    <div class="tab-pane fade" id="tabs-profileJustify" role="tabpanel" aria-labelledby="tabs-profile-tabJustify">
                        @if ($activeTab === 'business')
                            <livewire:loans.business-data/>
                        @endif
                    </div>
                    <div class="tab-pane fade" id="tabs-messagesJustify" role="tabpanel" aria-labelledby="tabs-messages-tabJustify">
                        @if ($activeTab === 'collateral')
                            <livewire:loans.collateral-info/>
                        @endif
                    </div>
                    <div class="tab-pane fade" id="tabs-messagesJustifyx" role="tabpanel" aria-labelledby="tabs-messages-tabJustifyx">
                        @if ($activeTab === 'assessment')
                            <livewire:loans.assessment/>
                        @endif
                    </div>
                </div>
            </div>




        </div>


    @else


        <div class="w-full" >
            <div class="w-fit bg-gray-200 rounded-lg pl-1 pr-1 pt-1 pb-1 m-1">
                <livewire:loans.loans-table/>
            </div>
        </div>

    @endif




    <script nonce="{{ csp_nonce() }}">
        const tabLinks = document.querySelectorAll('.tab-link');
        const tabContents = document.querySelectorAll('.tab-pane');

        tabLinks.forEach((tabLink) => {
            tabLink.addEventListener('click', (event) => {
                event.preventDefault();
                const target = event.target.getAttribute('href').substring(1);
                tabLinks.forEach((link) => link.classList.remove('active'));
                tabContents.forEach((content) => content.classList.remove('show', 'active'));
                event.target.classList.add('active');
                document.getElementById(target).classList.add('show', 'active');
            });
        });
    </script>


</div>
