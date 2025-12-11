<div>
    <div class="p-4">

        <!-- Welcome banner -->
        <div class="relative bg-white p-4 sm:p-6 overflow-hidden mb-2 rounded-2xl shadow-md shadow-gray-200" style="height: 116px;">

            <!-- Content -->
            <div class="">
                <div class="flex items-center mb-2 space-x-2 text-sm font-semibold spacing-sm text-slate-600 h-auto">

                    <div>
                        RECONCILIATION FILES STATUS

                        @if($this->processedTransactions > -1)
                            <div class="flex items-center text-sm font-semibold text-red-600 spacing-sm">
                                {{$nodeName}} TRANSACTIONS </br>
                                PROCESSED - {{$this->processedTransactions}} </br>
                                SKIPPED - {{count($this->importErrors)}} (Exported)
                            </div>
                        @endif

                        <p></p>
                    </div>

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



        </div>


        <div class="bg-white p-4 sm:p-6 overflow-hidden mb-2 rounded-2xl shadow-md shadow-gray-200">

            <div class="w-full p-2" >
                <!-- message container -->
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
                                    <p class="font-bold">The process is completed with Errors</p>
                                    <p class="text-sm">{{ session('message') }} </p>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                </div>


                <div class="bg-gray-100 rounded rounded-lg shadow-sm ">

                    <div class="flex gap-2 pt-2 pl-2 pr-2 pb-2">

                        <div class="w-1/3 bg-white rounded px-6 rounded-lg shadow-sm   pt-4 pb-4 " >


                         
                               

                            <div class="form-group row">

                                <p for="nodeName" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Select Node</p>
                                <select id="nodeName" wire:model="nodeName" class="block mt-1 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm sm:px-3 sm:text-sm h-10" required>
                                    <option value="" selected>SELECT NODE</option>
                                    @foreach($this->nodes as $node)
                                        <option value="{{$node->NODE_NAME}}" >{{$node->NODE_NAME}}</option>
                                    @endforeach

                                </select>
                                @error('nodeName')
                                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                    <p>Please select a node.</p>
                                </div>
                                @enderror
                            </div>

                            <div class="mt-2"></div>

                            <p for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Select File</p>
                            <input wire:model="excelFile" class="form-control block w-full px-2 py-1 text-sm font-normal
                                                         text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition
                                                         ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-red-600 focus:outline-none"
                                   id="formFileCb" type="file" accept="application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv" >


                            @error('excelFile')
                            <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                <p>Please select a file.</p>
                            </div>
                            @enderror
                            <div wire:loading wire:target="excelFile" >
                                Please wait...
                            </div>

                           
                    
                           

                            <hr class="border-b-0 my-6"/>

                            <div class="flex justify-end w-auto" >
                                <div wire:loading wire:target="save" >
                                    <button class="text-white bg-red-400 hover:bg-red-400 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-red-400 dark:hover:bg-red-400 dark:focus:ring-red-400" disabled>
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
                                <div wire:loading.remove wire:target="save" >
                                    <button wire:click="save" class="text-white bg-red-400 hover:bg-red-400 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-red-400 dark:hover:bg-red-400 dark:focus:ring-red-400">
                                        <p class="text-white">Save File</p>
                                    </button>

                                </div>
                            </div>


                        </div>


                        <div class="w-2/3 bg-white rounded-lg shadow-sm p-4  " >

     

                            <div class="p-3" >
                                <div class="w-full flex justify-between">
                                    <div class="mb-4">
                                        <p class="">TRANSACTION NUMBERS</p>
                                    </div>

                                   
                                    <div>

                                        <div class="relative ">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                                <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                                            </div>
                                            <input datepicker datepicker-autohide wire:model="startDate" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full pl-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500" placeholder="Select date">
                                        </div>
                                        @error('startDate')
                                        <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                                            <p>Please select a date.</p>
                                        </div>
                                        @enderror
                                        <script nonce="{{ csp_nonce() }}">
                                            flatpickr('[datepicker]', {
                                                dateFormat: "Y-m-d",
                                                autoHide: true,
                                                allowInput: true,
                                                minDate: "2000-01-01",
                                                maxDate: new Date().fp_incr(14)
                                            });
                                        </script>


                                        <div class="mt-4"></div>
                                    </div>
                                </div>

                                <table>

                                    @foreach($this->nodes as $node)
                                        <tr>
                                            <td class="mt-2 text-sm font-semibold  text-slate-400 dark:text-white capitalize  ">
                                                {{$node->NODE_NAME}} FILE</td>
                                            <td class="pl-2 mt-2 text-sm font-semibold spacing-sm  text-slate-400  dark:text-white  text-right">

                                                <div class="flex justify-end" style="width: 200px">

                                                    @php


                                                        $timestamp = strtotime($this->startDate);
                                                        $formatted_date = date('Y-m-d', $timestamp);
                                                        $count = DB::table($node->NODE_NAME)
                                                        ->whereDate('DB_TABLE_DATE',$formatted_date)
                                                        ->count();


                                                    @endphp

                                                    @if($count > 0)
                                                        {{number_format($count)}}
                                                    @else
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                             stroke="orange" class="h-4 w-4 stroke-orange">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                                        </svg>
                                                    @endif





                                                </div>
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


    <script nonce="{{ csp_nonce() }}">
            window.addEventListener('clearFileInput', event => {
                var fileInput = document.getElementById('formFileCb');
            fileInput.value = null;
        });
    </script>

</div>








