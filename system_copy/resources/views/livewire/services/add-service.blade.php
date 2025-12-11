    <div>
    @if (session()->has('message'))
        @if (session('alert-class') == 'alert-success')
            <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md mb-8" role="alert">
                <div class="flex">
                    <div class="py-1">
                        <svg class="fill-current h-6 w-6 text-teal-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold">The process is completed</p>
                        <p class="text-sm">{{ session('message') }} </p>
                    </div>
                </div>
            </div>
        @endif
        @if (session('alert-class') == 'alert-warning')
            <div class="bg-yellow-100 border-t-4 border-yellow-500 rounded-b text-yellow-900 px-4 py-3 shadow-md mb-8" role="alert">
                <div class="flex">
                    <div class="py-1">
                        <svg class="fill-current h-6 w-6 text-yellow-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold">Error</p>
                        <p class="text-sm">{{ session('message') }} </p>
                    </div>
                </div>
            </div>
        @endif
    @endif

            <div class="bg-gray-100 rounded-lg shadow-sm ">
                <div class="flex gap-2 pt-2 pl-2 pr-2 pb-2">
                    <div class="w-1/3 bg-white rounded px-6 rounded-lg shadow-sm   pt-4 pb-4 " >
                        <p for="name" class="block mb-2 text-sm font-medium text-gray-900 ds:text-gray-400">SERVICE NAME</p>


                        @php
                        $services = [
                            ['id' => 1, 'label' => 'Salary Slip'],
                            ['id' => 2, 'label' => 'Employee Loans'],
                            ['id' => 3, 'label' => 'Benefits & Contribution'],
                            ['id' => 4, 'label' => 'Job Applications'],
                            ['id' => 5, 'label' => 'Edit Employee Info'],


                        ];

                        //    ['id' => 2, 'label' => ' Manage Sessions'],
                        //      ['id' => 3, 'label' => ' Reports'],

                    @endphp


                        @foreach ($services as $service )


                        <button wire:click="selectedMenu({{ $service['id'] }})"  class=" bg-gray-50 border border-gray-300 text-gray-900 text-sm
                        rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 ds:bg-gray-700
                        ds:border-gray-600 ds:placeholder-gray-400 ds:text-white ds:focus:ring-blue-500 ds:focus:border-blue-500  @if($service['id']==$this->service_id) bg-blue-800 text-white @endif ">

                         {{ $service['label'] }}
                        </button>

                        <div class="mt-2"></div>
                        @endforeach


                        <div class="mt-2"></div>
                        <hr class="border-b-0 my-6"/>
                        <div class="flex justify-end w-auto" >
                            <div wire:loading wire:target="save" >
                                <button class="text-white bg-blue-400 hover:bg-blue-400 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 ds:bg-blue-400 ds:hover:bg-blue-400 ds:focus:ring-blue-400" disabled>
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin  h-5 w-5 mr-2 stroke-white-800" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        <p>Please wait...</p>
                                    </div>
                                </button>
                            </div>
                        </div>

                    </div>


                    <div class="w-2/3 bg-white rounded px-6 rounded-lg shadow-sm  pt-4 pb-4  " >

                        <p for="name" class="block mb-2 text-sm font-medium text-gray-900 ds:text-gray-400"> Details </p>

                        @switch($this->service_id)
                            @case('1')
                             <livewire:services.salary-slip />
                             @break
                             @case('2')
                             <livewire:services.employee-loan />
                             @break
                             @case('3')
                                 <livewire:services.contribution-benefit />
                             @break

                             @case('4')
                                  No applications
                             @break
                             @case('5')
                                  <livewire:services.edit-information  />
                             @break
                        @default
                        @endswitch


                    </div>

                </div>


            </div>
            
</div>
