<div>
    <div class="p-4">

        <!-- Welcome banner -->
        <div class="relative bg-white p-4 sm:p-6 overflow-hidden mb-2 rounded-2xl shadow-md shadow-gray-200"  style="height: 116px;" id="getheight">

                <div class="flex justify-between w-full ">

                    <div>
                        RECONCILIATION DATE : {{ $this->startDate }}
                        <div class="flex items-center text-sm font-semibold text-red-600 spacing-sm">
                        RECONCILIATION STATUS : {{$this->processStatus}}
                        </div>

                        <div class="">

                            <div class="flex items-center text-sm font-semibold text-red-600 spacing-sm">
                                {{$sessionDateStatusFiles}}
                            </div>
                            <div class="flex items-center text-sm font-semibold text-red-600 spacing-sm">
                                {{$sessionDateStatusDbs}}
                            </div>

                        </div>

                    </div>

                        <div>


                            <div class="relative ">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                                </div>
                                <input datepicker datepicker-autohide wire:model="startDate" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Select date">
                            </div>

                            <script nonce="{{ csp_nonce() }}">
                                flatpickr('[datepicker]', {
                                    dateFormat: "d-m-Y",
                                    autoHide: true,
                                    allowInput: true,
                                    minDate: "01-01-2000",
                                    maxDate: "today"
                                });
                            </script>
                        </div>


                </div>

        </div>



        <!-- Dashboard actions -->
        <div class="flex w-full mb-4 gap-2">

        <!-- Left: Avatars -->
        <div class="bg-white rounded-2xl shadow-md shadow-gray-200 w-full p-2 flex  items-center">

            <div class="border border-gray-200 rounded-2xl m-auto flex gap-1 w-full">
                @foreach($this->nodes as $key => $node)
                    <div class="w-1/5  @if($key !== $this->nodes->keys()->last()) border-r-2 border-gray-200 @endif mr-4 pr-4 p-4">
                        <div class="flex justify-between items-center">
                            <p class="text-sm font-semibold capitalize text-slate-400 dark:text-white " style="text-transform: uppercase;">
                                {{$node->NODE_NAME}} FILE
                            </p>
                            <p>
                                @php
                                    $timestamp = strtotime($this->startDate);
                                    $formatted_date = date('Y-m-d', $timestamp);
                                    $count = DB::table($node->NODE_NAME)
                                    ->whereDate('DB_TABLE_DATE',$formatted_date)
                                    ->count();

                                @endphp

                                @if($count > 0)
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                         stroke-width="1.5" stroke="green" class="w-4 h-4 stroke-green">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                         stroke-width="1.5" stroke="orange" class="w-4 h-4 stroke-orange">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                @endif
                            </p>
                        </div>
                    </div>

                @endforeach
            </div>


        </div>


            @if (in_array(1, session()->get('permissions')))
                <!-- Right: Actions -->
                <div class="bg-white rounded-2xl shadow-md shadow-gray-200 h-20  flex items-center justify-center p-2 whitespace-nowrap lg:p-5">

                    <!-- Add view button -->
                    <button wire:click="run" type="button" class="inline-flex items-center py-2 px-3 text-lg font-medium text-center text-white bg-gradient-to-br @if($this->disableProcess) bg-gray-400 @else bg-blue-400 @endif  rounded-lg shadow-md shadow-gray-300 hover:scale-[1.02] transition-transform"  >

                        <div wire:loading wire:target="run">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 spin">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div wire:loading.remove wire:target="run">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>

                        @if($this->rerunProcess) RERUN RECONCILIATION @else RUN RECONCILIATION @endif

                    </button>




                </div>

            @else

            @endif


    </div>

        @if (in_array(2, session()->get('permissions')))
            <!-- Right: Actions -->
        <div class="bg-white p-4 sm:p-6 overflow-hidden mb-2 rounded-2xl shadow-md shadow-gray-200">

            <div class="w-full border-b border-gray-200 dark:border-gray-700">
                <ul
                    class="flex items-stretch w-full text-sm font-medium text-center text-gray-500 nav nav-tabs dark:text-gray-400">
                    <li class="mr-2 nav-item grow cursor-pointer" role="presentation">
                        <a wire:click="changeTab(1)" id="tabs-home-tabJustify" data-bs-toggle="pill"
                           data-bs-target="#tabs-homeJustify" role="tab" aria-controls="tabs-homeJustify"
                           {{$this->oneIsSetClicked ? "aria-selected='true'" : "aria-selected='false'" }}
                           wire:click="changeTab(1)"
                           class="w-full self-center
                        {{$this->oneIsSetClicked ? "text-dark-shade border-darker-shade active dark:text-blue-500
                        dark:border-blue-500 " : "border-transparent hover:text-gray-600 hover:border-gray-300
                        dark:hover:text-gray-300 "}}
                        inline-flex p-4
                        rounded-t-lg
                        border-b-2
                        group"
                           {{$this->oneIsSetClicked ? "aria-current='page'" : "" }}
                           style="display: flex; justify-content: center; align-items: center;"
                        >
                            <svg class="w-5 h-5 mr-4 {{$this->oneIsSetClicked  ? " text-dark-shade dark:text-blue-500"
                            : "text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300"
                            }}" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                                </path>
                            </svg>
                            MARCHING TRANSACTIONS
                        </a>
                    </li>
                    <li class="mr-2 nav-item grow cursor-pointer" role="presentation">
                        <a wire:click="changeTab(2)" id="tabs-profile-tabJustify" data-bs-toggle="pill"
                           data-bs-target="#tabs-profileJustify" role="tab" aria-controls="tabs-profileJustify"
                           {{$this->twoIsSetClicked ? "aria-selected='true'" : "aria-selected='false'" }}
                           wire:click="changeTab(2)"
                           class="w-full self-center
                        {{$this->twoIsSetClicked ? "text-dark-shade border-darker-shade active dark:text-blue-500
                        dark:border-blue-500 " : "border-transparent hover:text-gray-600 hover:border-gray-300
                        dark:hover:text-gray-300 "}}
                        inline-flex p-4
                        rounded-t-lg
                        border-b-2
                        group"
                           {{$this->twoIsSetClicked ? "aria-current='page'" : "" }}
                           style="display: flex; justify-content: center; align-items: center;"
                        >
                            <svg class="mr-2 w-5 h-5 {{$this->twoIsSetClicked  ? " text-dark-shade dark:text-blue-500"
                            : "text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300"
                            }}" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                                </path>
                            </svg>
                            NON MATCHING TRANSACTIONS
                        </a>
                    </li>
                

                </ul>
            </div>


            <div class="w-full flex items-center justify-center p-4">
                <div wire:loading wire:target="setView">
                    <div class="h-96 m-auto flex items-center justify-center">
                        <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-blue-500"></div>
                    </div>
                </div>
            </div>
            <div wire:loading.remove wire:target="setView">
               
                @switch($this->selected)
                    @case('1')
                        <livewire:process.matching-table />
                        @break
                    @case('2')
                        <livewire:process.non-matching-table />
                        @break
                    @case('3')
                        <livewire:process.third-part-non-matching-table />
                        @break
                    @default
                        <livewire:process.matching-table />
                @endswitch
            </div>



        </div>
        @else

        @endif

</div>


</div>
