<div>
    {{-- Success is as dangerous as failure. --}}

    <div>
        <div class="w-full p-2" >
            <!-- message container -->
            <div class="bg-gray-100 rounded rounded-lg shadow-sm ">
                <div class="flex gap-2 pt-2 pl-2 pr-2 pb-2">
                    <div class="w-1/5 bg-white rounded px-1 rounded-lg shadow-sm   pt-4 pb-4 " >


                        <div class="md:flex">
                            <ul class="flex-column space-y w-full space-y-4 text-sm font-medium text-gray-500 dark:text-gray-400 md:me-4 mb-4 md:mb-0">

                                @foreach($this->responseXml as $key=>$value)
                                    <li class="cursor-pointer"   wire:click="CustomerInfo({{json_encode([$key,$value])}})">

                                        <a   class="inline-flex items-center  @if($this->selectedMenu == $key) bg-red-600 text-white @endif px-4 py-3 rounded-lg hover:text-white bg-gray-50 hover:bg-red-500 w-full dark:bg-gray-800 dark:hover:bg-red-500 dark:hover:text-white">
                                            <svg class="w-4 h-4 me-2 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 18"><path d="M6.143 0H1.857A1.857 1.857 0 0 0 0 1.857v4.286C0 7.169.831 8 1.857 8h4.286A1.857 1.857 0 0 0 8 6.143V1.857A1.857 1.857 0 0 0 6.143 0Zm10 0h-4.286A1.857 1.857 0 0 0 10 1.857v4.286C10 7.169 10.831 8 11.857 8h4.286A1.857 1.857 0 0 0 18 6.143V1.857A1.857 1.857 0 0 0 16.143 0Zm-10 10H1.857A1.857 1.857 0 0 0 0 11.857v4.286C0 17.169.831 18 1.857 18h4.286A1.857 1.857 0 0 0 8 16.143v-4.286A1.857 1.857 0 0 0 6.143 10Zm10 0h-4.286A1.857 1.857 0 0 0 10 11.857v4.286c0 1.026.831 1.857 1.857 1.857h4.286A1.857 1.857 0 0 0 18 16.143v-4.286A1.857 1.857 0 0 0 16.143 10Z"/></svg>
                                            {{$key}}
                                        </a>

                                    </li>
                                @endforeach
                            </ul>

                        </div>



                    </div>

                    <div class="w-4/5 bg-white rounded px-6 rounded-lg shadow-sm  pt-4 pb-4  " >
                        <div class="w-full bg-gray-50 rounded rounded-lg shadow-sm   p-1 ">


                            <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                                <table class="w-full border text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">

                                    @if( is_array($this->table_headers))
                                        <thead class="text-xs text-gray-700  uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                        <tr>

                                            @foreach($this->table_headers  as $key=>$value1)

                                                <th wire:click="secondCustomerInfo({{json_encode([ $key,$value1])}})" scope="col" class="px-6 py-3 border-r   @if($this->selectedHeader== $key) bg-red-600 text-white @endif   border-right cursor-pointer">
                                                    @if(is_numeric($key))

                                                    @else
                                                        {{$key}}
                                                    @endif
                                                </th>
                                            @endforeach
                                        </tr>
                                        </thead>

                                    @endif




                                    @if(is_array($this->customerBodyInfo) && !empty($this->customerBodyInfo))

                                        <tbody>
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700  ">

                                        @foreach($this->customerBodyInfo as $key2 => $value2)

                                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700  ">

                                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">

                                                    @if(is_numeric($key2))

                                                    @else
                                                        {{ htmlspecialchars($key2) }}
                                                    @endif



                                                </th>

                                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">


                                                    @if(!empty($value2) && is_array($value2))
                                                        <div class="relative overflow-x-auto ">
                                                            <table class="w-full text-sm border text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                                                <tr>
                                                                    @foreach($value2 as $key3 => $value3)

                                                                        <th scope="col" class="px-6 py-3">
                                                                            @if(is_numeric($key3))

                                                                            @else
                                                                                {{$key3}}
                                                                            @endif

                                                                        </th>
                                                                    @endforeach
                                                                </tr>
                                                                </thead>

                                                                <tbody>
                                                                @foreach($value2 as $key3 => $value3)

                                                                    <tr class="bg-white   dark:bg-gray-800 d">
                                                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">

                                                                            @if(!empty($value3) && is_array($value3))

                                                                                <div class="relative overflow-x-auto ">
                                                                                    <table class="w-full text-sm border text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                                                                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                                                                        <tr>
                                                                                            @foreach($value3 as $key4 => $value4)
                                                                                                <th scope="col" class="px-6 py-3">

                                                                                                    @if(is_numeric($key4))

                                                                                                    @else
                                                                                                        {{$key4}}
                                                                                                    @endif
                                                                                                </th>
                                                                                            @endforeach
                                                                                        </tr>
                                                                                        </thead>

                                                                                        <tbody>

                                                                                        <tr class="bg-white   dark:bg-gray-800 dark:border-gray-700 ">
                                                                                            @foreach($value3 as $key4 => $value4)
                                                                                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">

                                                                                                    @if(!empty($value4) && is_array($value4))





                                                                                                        <div class="relative overflow-x-auto ">
                                                                                                            <table class="w-full border text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                                                                                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                                                                                                <tr>
                                                                                                                    @foreach($value4 as $key5 => $value5)
                                                                                                                        <th scope="col" class="px-6 py-3">
                                                                                                                            @if(is_numeric($key5))
                                                                                                                            @else
                                                                                                                                {{$key5}}
                                                                                                                            @endif
                                                                                                                        </th>
                                                                                                                    @endforeach

                                                                                                                </tr>
                                                                                                                </thead>
                                                                                                                <tbody>
                                                                                                                <tr class="bg-white   dark:bg-gray-800 dark:border-gray-700 ">

                                                                                                                    @foreach($value4 as $key5 => $value5)

                                                                                                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                                                                                            @if(!empty($value5) && is_array($value5))





                                                                                                                                <div class="relative overflow-x-auto">
                                                                                                                                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                                                                                                                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                                                                                                                        <tr>
                                                                                                                                            @foreach($value5 as $key6 => $value6)
                                                                                                                                                <th scope="col" class="px-6 py-3">

                                                                                                                                                    @if(is_numeric($key6))

                                                                                                                                                    @else
                                                                                                                                                        {{$key6}}
                                                                                                                                                    @endif
                                                                                                                                                </th>
                                                                                                                                            @endforeach
                                                                                                                                        </tr>
                                                                                                                                        </thead>
                                                                                                                                        <tbody>
                                                                                                                                        <tr class="bg-white   dark:bg-gray-800 dark:border-gray-700 ">

                                                                                                                                            @foreach($value5 as $key6 => $value6)
                                                                                                                                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">

                                                                                                                                                    @if(!empty($value6) && is_array($value6))




                                                                                                                                                        <div class="relative overflow-x-auto ">
                                                                                                                                                            <table class="w-full border  text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                                                                                                                                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                                                                                                                                                <tr>
                                                                                                                                                                    @foreach($value6 as $key7 => $value7)
                                                                                                                                                                        <th scope="col" class="px-6 py-3">


                                                                                                                                                                            @if(is_numeric($key7))

                                                                                                                                                                            @else
                                                                                                                                                                                {{$key7}}
                                                                                                                                                                            @endif
                                                                                                                                                                        </th>
                                                                                                                                                                    @endforeach

                                                                                                                                                                </tr>
                                                                                                                                                                </thead>
                                                                                                                                                                <tbody>

                                                                                                                                                                <tr class="bg-white   dark:bg-gray-800 dark:border-gray-700 ">
                                                                                                                                                                    @foreach($value6 as $key7 => $value7)
                                                                                                                                                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                                                                                                                                            @if(!empty($value7) && is_array($value7))
                                                                                                                                                                                more table





                                                                                                                                                                            @else
                                                                                                                                                                                {{$value7}}
                                                                                                                                                                            @endif
                                                                                                                                                                        </th>
                                                                                                                                                                    @endforeach

                                                                                                                                                                </tr>

                                                                                                                                        </tr>
                                                                                                                                        </tbody>
                                                                                                                                    </table>
                                                                                                                                </div>




                                                                                                                            @else
                                                                                                                                {{$value6}}
                                                                                                                            @endif
                                                                                                                        </th>
                                                                                                                    @endforeach
                                                                                                                </tr>
                                                                                                                </tbody>
                                                                                                            </table>
                                                                                                        </div>




                                                                                                    @else

                                                                                                        {{$value5}}

                                                                                                    @endif
                                                                                                </th>

                                                                                            @endforeach

                                                                                        </tr>
                                                                                        </tbody>
                                                                                    </table>
                                                                                </div>






                                                                            @else

                                                                                {{$value4}}

                                                                            @endif
                                                                        </th>
                                                                        @endforeach
                                                                    </tr>


                                                                </tbody>
                                                            </table>
                                                        </div>







                                                    @else


                                                        {{ $value3 }}

                                                    @endif
                                                </th>

                                            </tr>
                                        @endforeach

                                        </tbody>
                                </table>
                            </div>


                            @else
                                {{ htmlspecialchars($value2) }}
                            @endif




                            </th>

                            </tr>



                            @endforeach




                            </tr>


                            </tbody>
                            @else
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ htmlspecialchars($this->customerBodyInfo) }}
                                </th>

                                @endif
                                </table>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>


</div>
