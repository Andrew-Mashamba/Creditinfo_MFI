<div>
    {{-- Knowing others is intelligence; knowing yourself is true wisdom. --}}
    <div class="w-full p-2" >
        <!-- message container -->
        <div class="bg-gray-100 rounded rounded-lg shadow-sm ">
            <div class="flex gap-2 pt-2 pl-2 pr-2 pb-2">
                <div class="w-1/3 bg-white rounded px-6 rounded-lg shadow-sm   pt-4 pb-4 " >
                    <input type="file" wire:model="loan_document" class=" bg-gray-50 border border-gray-300 text-gray-900 sm:text-xs   rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white p-1 dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <label class="block text-sm font-medium mb-1" for="notes">
                        Descriptions
                    </label>
                    <textarea id="notes" name="notes" wire:model="document_descriptions" rows="5" class="mt-1 block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Your notes..."></textarea>
                    @if($this->loan_document && $this->document_descriptions)
                        <div class=" justify-end flex mt-3 mr-2 mb-2">
                            <button wire:click="save" class="bg-green-400 text-white inline-flex items-center px-4 py-1 border border-solid rounded-md font-semibold transition">
                                Save
                            </button>
                        </div>
                    @endif
                </div>
                <div class="w-2/3 bg-white rounded px-6 rounded-lg shadow-sm  pt-4 pb-4  " >
                    <div class="w-full bg-gray-50 rounded rounded-lg shadow-sm   p-1 ">
                        @foreach($this->loan_document_data as $data)
                            <div class="w-full bg-white rounded rounded-lg h-50 shadow-sm  flex space-x-1  p-2 ">
                                <div class="w-1/2">
                                    @php
                                        $file_extension=pathinfo($data->url,PATHINFO_EXTENSION);
                                            if($file_extension=="pdf"){
                                              $url='/documentImage/img_1.png';
                                            }
                                            if($file_extension=="doc"){
                                                 $url='/documentImage/img.png';

                                            } if($file_extension=="docx"){
                                                 $url='/documentImage/img_3.png';

                                            } if($file_extension=="txt"){
                                                 $url='/documentImage/img_4.png';

                                            } if($file_extension=="ppt"){
                                                 $url='/documentImage/img_5.png';

                                            } if($file_extension=="xls"){
                                                 $url='/documentImage/img_2.png';

                                            }
                                            if($file_extension=="xlsx"){
                                                 $url='/documentImage/img_6.png';

                                            }
                                            else{
                                                $url=$data->url;
                                           }
                                    @endphp

                                   <div class="flex gap-6">
                                       <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-clipboard" viewBox="0 0 16 16">
                                           <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/>
                                           <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/>
                                       </svg>
                                       <div>
                                           {{$data->document_descriptions}}
                                       </div>

                                   </div>
                                </div>
                                <div class="w-1/2">
                                    <div class="justify-end flex">
                                        <div class="flex items-center space-x-4 flex-lg-row">
                                            <button  type="button"  wire:click="download2({{$data->id}})" class="hoverable text-white bg-gray-100 hover:bg-blue-100 hover:text-blue-200 focus:ring-4 focus:outline-none focus:ring-blue-100 font-medium rounded-lg text-sm p-1 text-center inline-flex items-center mr-2 dark:bg-blue-200 dark:hover:bg-blue-200 dark:focus:ring-blue-200">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="blue"  viewBox="0 0 24 24" stroke-width="1.5" stroke="blue" class="w-8 h-8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                                </svg>
                                                <span class="hidden text-blue-800 m-1"> </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>






