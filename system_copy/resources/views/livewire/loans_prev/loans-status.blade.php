


<div>

@if($this->account_delete_modal)
    <!-- Delete Modal -->
<div>
    <div class="fixed flex-auto animate-[fade-in_0.3s_both]  mt-n5  inset-0 flex items-top mt-10 justify-center z-50">

        <div class="fixed bg-white rounded-lg p-6 h-2/5 w-1/2 max-w-lg mx-auto  shadow-lg mt-10 ">
            <div class="card-header">
                <h2 class="text-lg font-medium text-gray-800 mb-4 text-lg-center">Are you sure you want to delete this account?</h2>
            </div>
            <div class="text-gray-600 border-top  ">
                <div class="fw-bold">
                    <strong> Client Selected</strong>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <p > {{App\Models\ClientsModel::where('client_number',Session::get('currentloanClientDeleteModal'))->value('first_name').' '.App\Models\ClientsModel::where('client_number',Session::get('currentloanClientDeleteModal'))->value('middle_name').' '.App\Models\ClientsModel::where('client_number',Session::get('currentloanClientDeleteModal'))->value('last_name')}}</p>

                </div>
            </div>

            <footer class="py-4 bg-gradient-to-b fixed-bottom mt-10">
                <div class="flex justify-end mb-0 mt-10 border-top mt-10">
                    <button  wire:click="deleteLoanModalDisplay" type="button" class="mr-2 px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-100 focus:outline-none">Cancel</button>
                    <button wire:click="deleteLoan"  type="button"  class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 focus:outline-none">Delete</button>
                </div>
            </footer>
        </div>
    </div>
</div>
  @endif


@if(Session::get('currentloanClient'))

        <div class="w-full" >
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
                                                 $urlValue=  \App\Models\ClientsModel::where('client_number',Session::get('currentloanClient'))->value('profile_photo_path');
                                                ?>
                                               @if( $urlValue)
                                                    <img src="{{$urlValue}}">
                                                @else
                                                    <img class="h-8 w-8 rounded-full" src="{{asset('/images/download-1.png')}}" alt="">
                                                  @endif

                                            </button>

                                        </div>
                                        <p class="font-semibold ml-3 text-slate-600 text-green-900">{{App\Models\ClientsModel::where('client_number',Session::get('currentloanClient'))->value('first_name').' '.App\Models\ClientsModel::where('client_number',Session::get('currentloanClient'))->value('middle_name').' '.App\Models\ClientsModel::where('client_number',Session::get('currentloanClient'))->value('last_name')}}</p>

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
                            <div class="flex space-x-4 pr-2">
                                <!-- Current: "bg-gray-900 text-white", Default:"text-gray-300 hover:bg-gray-700 hover:text-white" -->
                                <a href="#" wire:click="showTab('client')" class=" @if($this->activeTab == 'client') bg-red-500 text-white  @endif hover:bg-red-500 guarantor  hover:text-white  rounded-md px-3 hover:text-white py-2 text-sm font-medium" aria-current="page"> Client</a>
                                <a href="#" wire:click="showTab('addDocument')" class=" @if($this->activeTab == 'addDocument') bg-red-500  text-white @endif   text-dark-900 hover:bg-red-600 hover:text-white   rounded-md px-3 py-2 text-sm font-medium">Add Document</a>
                                <a href="#" wire:click="showTab('creditInfo')" class=" @if($this->activeTab == 'creditInfo') bg-red-500  text-white @endif   text-dark-900 hover:bg-red-600  hover:text-white  rounded-md px-3 py-2 text-sm font-medium">Credit Info</a>
                                <a href="#" wire:click="showTab('guarantor')" class=" @if($this->activeTab == 'guarantor') bg-red-500  text-white @endif   text-dark-900 hover:bg-red-600 hover:text-white  rounded-md px-3 py-2 text-sm font-medium">Guarantors</a>
                                 @if(session()->get('loan_type')=="UNSECURED")

                                @else
                                <a href="#" wire:click="showTab('business')" class=" @if($this->activeTab == 'business') bg-red-500 text-white @endif  text-blue-800 hover:bg-red-600 text-green-900  hover:text-white rounded-md px-3 py-2 text-sm font-medium">Business Information</a>
                                <a href="#" wire:click="showTab('collateral')" class=" @if($this->activeTab == 'collateral')bg-red-500 text-white @endif  text-dark-900
                                hover:bg-red-500 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Collateral Information</a>
                                @endif
                                    <a href="#" wire:click="showTab('assessment')" class=" @if($this->activeTab == 'assessment') bg-red-500  text-white @endif  text-dark-900  hover:bg-red-500 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Assessment</a>
                            </div>

                            <button type="button" class="rounded-full bg-white p-1 space-x-2 text-gray-400 hover:text-blue-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                                <svg wire:click="close" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 " fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>

                        </div>
                    </div>

                </div>

        </nav>








        <div class="w-full rounded-lg pl-1 pr-1 pt-1 pb-1 m-1">


            <div class="bg-white">
                    @if ($activeTab === 'client')
                        <livewire:loans.client-info/>
                    @endif
                        @if ($activeTab === 'addDocument')
                            <livewire:loans.add-document/>
                        @endif

                        @if ($activeTab === 'creditInfo')
                            <livewire:loans.credit-info/>
                        @endif
                    @if ($activeTab === 'guarantor')
                        <livewire:loans.guarantor-info/>
                     @endif
                     @if ($activeTab === 'business')
                        <livewire:loans.business-data/>
                     @endif
                     @if ($activeTab === 'collateral')
                        <livewire:loans.collateral-info/>
                     @endif
                     @if ($activeTab === 'assessment')
                        <livewire:loans.assessment/>
                    @endif


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
