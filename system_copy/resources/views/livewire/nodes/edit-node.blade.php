<div>
    @if (session()->has('message'))

        @if (session('alert-class') == 'alert-success')
            <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md mb-8" role="alert">
                <div class="flex">
                    <div class="py-1"><svg class="fill-current h-6 w-6 text-teal-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
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
                    <div class="py-1"><svg class="fill-current h-6 w-6 text-yellow-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                    <div>
                        <p class="font-bold">Error</p>
                        <p class="text-sm">{{ session('message') }} </p>
                    </div>
                </div>
            </div>
        @endif
    @endif


        <div class="bg-gray-100 rounded-lg shadow-sm p-2">

            <div class="bg-white rounded-lg shadow-sm p-4">
                <div>
            <div class="pr-4 w-1/2 ">
                <div CLASS="mb-4">EDIT A NODE</div>
                <p for="category" class="block mb-1 mt-2 text-sm capitalize text-slate-400 dark:text-white ">SELECT NODE</p>
                <select wire:model="nodeSelected" name="nodeSelected" id="nodeSelected" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option selected value="">Select Type</option>

                    @foreach($this->nodesList as $node)

                        <option selected value="{{$node->ID}}">{{$node->NODE_NAME}}</option>
                    @endforeach

                </select>
                @error('nodeSelected')
                <div class="px-4 py-3 mt-1 text-red-700 bg-red-100 border border-red-400 rounded-b">
                    <p>Branch is mandatory.</p>
                </div>
                @enderror
            </div>




        @if($this->nodeSelected)
                <div class="border border-solid border-1 border-gray-100 mt-6 mb-4"></div>
            <div class="flex w-full gap-4">
                <div class="w-1/2">
                    <p for="nodeType" class="block mb-1 mt-2 text-sm capitalize text-slate-400 dark:text-white ">TYPE OF NODE</p>
                    <select wire:model.bounce="nodeType" name="nodeType" id="nodeType" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option selected value="">Select Type</option>
                        <option selected value="CLIENT_NODE">Client node</option>
                        <option selected value="INTERNAL_NODE">Internal node</option>
                        <option selected value="PROCESSOR_NODE">Processor node</option>

                    </select>
                    @error('nodeType')
                    <div class="px-4 py-3 mt-1 text-red-700 bg-red-100 border border-red-400 rounded-b">
                        <p>TYPE OF NODE is mandatory.</p>
                    </div>
                    @enderror
                </div>


                <div class="w-1/2">
                    <p for="DATA_SOURCE_TYPE" class="block mb-1 mt-2 text-sm capitalize text-slate-400 dark:text-white ml-2">DATA SOURCE</p>
                    <div class="flex justify-between w-full gap-4" id="DATA_SOURCE_TYPE">

                        <div class="flex items-center w-1/3 pl-4 ">
                            <input wire:model='DATA_SOURCE_TYPE' id="bordered-radio-1" type="radio" value="Portal" name="DATA_SOURCE_TYPE" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" checked>
                            <p for="bordered-radio-1" class="w-full py-4 ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Portal</p>
                        </div>
                        <div class="flex items-center w-1/3 pl-4 ">
                            <input checked wire:model='DATA_SOURCE_TYPE' id="bordered-radio-2" type="radio" value="Database" name="DATA_SOURCE_TYPE" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            <p for="bordered-radio-2" class="w-full py-4 ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Database</p>
                        </div>
                        <div class="flex items-center w-1/3 pl-4 ">
                            <input wire:model='DATA_SOURCE_TYPE' id="bordered-radio-1" type="radio" value="API" name="DATA_SOURCE_TYPE" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            <p for="bordered-radio-1" class="w-full py-4 ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">API</p>
                        </div>

                    </div>
                    @error('DATA_SOURCE_TYPE')
                    <div class="px-4 py-3 mt-1 text-red-700 bg-red-100 border border-red-400 rounded-b">
                        <p>DATA_SOURCE_TYPE is mandatory.</p>
                    </div>
                    @enderror
                </div>
            </div>

            <div class="flex w-full gap-4">
                <div class="w-1/2">

                    <p for="NODE_NAME" class="block mb-1 mt-2 text-sm capitalize text-slate-400 dark:text-white ">NODE NAME</p>
                    <div class="" id="NODE_NAME">
                        <input id="NODE_NAME" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model.defer="NODE_NAME" autocomplete="NODE_NAME" />

                    </div>
                    @error('NODE_NAME')
                    <div class="px-4 py-3 mt-1 text-red-700 bg-red-100 border border-red-400 rounded-b">
                        <p>NODE NAME is mandatory.</p>
                    </div>
                    @enderror

                    <p for="DB_TABLE_REFERENCE" class="block mb-1 mt-2 text-sm capitalize text-slate-400 dark:text-white ">REFERENCE NUMBER</p>
                    <div class="" id="DB_TABLE_REFERENCE">
                        <input id="DB_TABLE_REFERENCE" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model.defer="DB_TABLE_REFERENCE" autocomplete="DB_TABLE_REFERENCE" />

                    </div>
                    @error('DB_TABLE_REFERENCE')
                    <div class="px-4 py-3 mt-1 text-red-700 bg-red-100 border border-red-400 rounded-b">
                        <p>REFERENCE NUMBER is mandatory.</p>
                    </div>
                    @enderror

                    <p for="DB_TABLE_SECONDARY_REFERENCE" class="block mb-1 mt-2 text-sm capitalize text-slate-400 dark:text-white ">SECONDARY REFERENCE NUMBER</p>
                    <div class="" id="DB_TABLE_SECONDARY_REFERENCE">
                        <input id="DB_TABLE_SECONDARY_REFERENCE" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model.defer="DB_TABLE_SECONDARY_REFERENCE" autocomplete="DB_TABLE_SECONDARY_REFERENCE" />
                    </div>
                    @error('DB_TABLE_SECONDARY_REFERENCE')
                    <div class="px-4 py-3 mt-1 text-red-700 bg-red-100 border border-red-400 rounded-b">
                        <p>SECONDARY REFERENCE NUMBER is mandatory.</p>
                    </div>
                    @enderror

                    <p for="DB_TABLE_DATE" class="block mb-1 mt-2 text-sm capitalize text-slate-400 dark:text-white ">VALUE DATE</p>
                    <div class="" id="DB_TABLE_DATE">
                        <input id="DB_TABLE_DATE" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model.defer="DB_TABLE_DATE" autocomplete="DB_TABLE_DATE" />

                    </div>
                    @error('DB_TABLE_DATE')
                    <div class="px-4 py-3 mt-1 text-red-700 bg-red-100 border border-red-400 rounded-b">
                        <p>VALUE DATE is mandatory.</p>
                    </div>
                    @enderror

                    <p for="DB_TABLE_AMOUNT" class="block mb-1 mt-2 text-sm capitalize text-slate-400 dark:text-white ">AMOUNT</p>
                    <div class="" id="DB_TABLE_AMOUNT">
                        <input id="DB_TABLE_AMOUNT" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model.defer="DB_TABLE_AMOUNT" autocomplete="DB_TABLE_AMOUNT" />

                    </div>
                    @error('DB_TABLE_AMOUNT')
                    <div class="px-4 py-3 mt-1 text-red-700 bg-red-100 border border-red-400 rounded-b">
                        <p>AMOUNT is mandatory.</p>
                    </div>
                    @enderror

                    <p for="DB_TABLE_SENDER" class="block mb-1 mt-2 text-sm capitalize text-slate-400 dark:text-white ">SENDER</p>
                    <div class="" id="DB_TABLE_SENDER">
                        <input id="DB_TABLE_SENDER" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model.defer="DB_TABLE_SENDER" autocomplete="DB_TABLE_SENDER" />

                    </div>
                    @error('DB_TABLE_SENDER')
                    <div class="px-4 py-3 mt-1 text-red-700 bg-red-100 border border-red-400 rounded-b">
                        <p>SENDER is mandatory.</p>
                    </div>
                    @enderror

                    <p for="DB_TABLE_RECEIVER" class="block mb-1 mt-2 text-sm capitalize text-slate-400 dark:text-white ">RECEIVER</p>
                    <div class="" id="DB_TABLE_RECEIVER">
                        <input id="DB_TABLE_RECEIVER" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model.defer="DB_TABLE_RECEIVER" autocomplete="DB_TABLE_RECEIVER" />

                    </div>
                    @error('DB_TABLE_RECEIVER')
                    <div class="px-4 py-3 mt-1 text-red-700 bg-red-100 border border-red-400 rounded-b">
                        <p>RECEIVER is mandatory.</p>
                    </div>
                    @enderror

                    <p for="DB_TABLE_SENDER" class="block mb-1 mt-2 text-sm capitalize text-slate-400 dark:text-white ">SENDER</p>
                    <div class="" id="DB_TABLE_SENDER">
                        <input id="DB_TABLE_SENDER" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model.defer="DB_TABLE_SENDER" autocomplete="DB_TABLE_SENDER" />

                    </div>
                    @error('DB_TABLE_SENDER')
                    <div class="px-4 py-3 mt-1 text-red-700 bg-red-100 border border-red-400 rounded-b">
                        <p>SENDER is mandatory.</p>
                    </div>
                    @enderror


                    <p for="DB_TABLE_DESCRIPTION" class="block mb-1 mt-2 text-sm capitalize text-slate-400 dark:text-white ">DESCRIPTION</p>
                    <div class="" id="DB_TABLE_DESCRIPTION">
                        <input id="DB_TABLE_DESCRIPTION" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model.defer="DB_TABLE_DESCRIPTION" autocomplete="DB_TABLE_DESCRIPTION" />

                    </div>
                    @error('DB_TABLE_DESCRIPTION')
                    <div class="px-4 py-3 mt-1 text-red-700 bg-red-100 border border-red-400 rounded-b">
                        <p>DESCRIPTION is mandatory.</p>
                    </div>
                    @enderror


                    <p for="DB_TABLE_STATUS" class="block mb-1 mt-2 text-sm capitalize text-slate-400 dark:text-white ">STATUS</p>
                    <div class="" id="DB_TABLE_STATUS">
                        <input id="DB_TABLE_STATUS" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model.defer="DB_TABLE_STATUS" autocomplete="DB_TABLE_STATUS" />

                    </div>
                    @error('DB_TABLE_STATUS')
                    <div class="px-4 py-3 mt-1 text-red-700 bg-red-100 border border-red-400 rounded-b">
                        <p>STATUS is mandatory.</p>
                    </div>
                    @enderror


                    <p for="DB_TABLE_SERVICE_IDENTIFIER" class="block mb-1 mt-2 text-sm capitalize text-slate-400 dark:text-white ">SERVICE IDENTIFIER</p>
                    <div class="" id="DB_TABLE_SERVICE_IDENTIFIER">
                        <input id="DB_TABLE_SERVICE_IDENTIFIER" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model.defer="DB_TABLE_SERVICE_IDENTIFIER" autocomplete="DB_TABLE_SERVICE_IDENTIFIER" />

                    </div>
                    @error('DB_TABLE_SERVICE_IDENTIFIER')
                    <div class="px-4 py-3 mt-1 text-red-700 bg-red-100 border border-red-400 rounded-b">
                        <p>SERVICE IDENTIFIER is mandatory.</p>
                    </div>
                    @enderror


                </div>


                <div class="w-1/2">



                    <p for="DB_TABLE_CLIENT_IDENTIFIER" class="block mb-1 mt-2 text-sm capitalize text-slate-400 dark:text-white ">CLIENT IDENTIFIER</p>
                    <div class="" id="DB_TABLE_CLIENT_IDENTIFIER">
                        <input id="DB_TABLE_CLIENT_IDENTIFIER" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model.defer="DB_TABLE_CLIENT_IDENTIFIER" autocomplete="DB_TABLE_CLIENT_IDENTIFIER" />

                    </div>
                    @error('DB_TABLE_CLIENT_IDENTIFIER')
                    <div class="px-4 py-3 mt-1 text-red-700 bg-red-100 border border-red-400 rounded-b">
                        <p>CLIENT IDENTIFIER is mandatory.</p>
                    </div>
                    @enderror


                    <p for="DB_TABLE_TRANSACTION_TYPE" class="block mb-1 mt-2 text-sm capitalize text-slate-400 dark:text-white ">TRANSACTION TYPE</p>
                    <div class="" id="DB_TABLE_TRANSACTION_TYPE">
                        <input id="DB_TABLE_TRANSACTION_TYPE" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model.defer="DB_TABLE_TRANSACTION_TYPE" autocomplete="DB_TABLE_TRANSACTION_TYPE" />

                    </div>
                    @error('DB_TABLE_TRANSACTION_TYPE')
                    <div class="px-4 py-3 mt-1 text-red-700 bg-red-100 border border-red-400 rounded-b">
                        <p>DB_TABLE_TRANSACTION_TYPE is mandatory.</p>
                    </div>
                    @enderror


                    @if($this->DATA_SOURCE_TYPE == 'API')
                        <p for="DB_TABLE_API_URL" class="block mb-1 mt-2 text-sm capitalize text-slate-400 dark:text-white ">API URL</p>
                        <div class="" id="DB_TABLE_API_URL">
                            <input type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model.defer="DB_TABLE_API_URL" autocomplete="DB_TABLE_API_URL" />

                        </div>
                        @error('DB_TABLE_API_URL')
                        <div class="px-4 py-3 mt-1 text-red-700 bg-red-100 border border-red-400 rounded-b">
                            <p>API URL is mandatory.</p>
                        </div>
                        @enderror

                        <p for="DB_TABLE_API_PASSWORD" class="block mb-1 mt-2 text-sm capitalize text-slate-400 dark:text-white ">API PASSWORD</p>
                        <div id="DB_TABLE_API_PASSWORD">
                            <input  type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model.defer="DB_TABLE_API_PASSWORD" autocomplete="DB_TABLE_API_PASSWORD" />

                        </div>
                        @error('DB_TABLE_API_PASSWORD')
                        <div class="px-4 py-3 mt-1 text-red-700 bg-red-100 border border-red-400 rounded-b">
                            <p>API PASSWORD is mandatory.</p>
                        </div>
                        @enderror

                        <p for="DB_TABLE_API_PRIVATE_KEY" class="block mb-1 mt-2 text-sm capitalize text-slate-400 dark:text-white ">API CERTIFICATE PRIVATE KEY</p>
                        <div id="DB_TABLE_API_PRIVATE_KEY">
                            <input type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model.defer="DB_TABLE_API_PRIVATE_KEY" autocomplete="DB_TABLE_API_PRIVATE_KEY" />

                        </div>
                        @error('DB_TABLE_API_PRIVATE_KEY')
                        <div class="px-4 py-3 mt-1 text-red-700 bg-red-100 border border-red-400 rounded-b">
                            <p>API CERTIFICATE PRIVATE KEY is mandatory.</p>
                        </div>
                        @enderror

                    @endif

                    @if($this->DATA_SOURCE_TYPE == 'Database')
                        <p for="NODE_DB_HOST" class="block mb-1 mt-2 text-sm capitalize text-slate-400 dark:text-white ">NODE DATABASE HOST IP</p>
                        <div id="NODE_DB_HOST">
                            <input  type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model.defer="NODE_DB_HOST" autocomplete="NODE_DB_HOST" />

                        </div>
                        @error('NODE_DB_HOST')
                        <div class="px-4 py-3 mt-1 text-red-700 bg-red-100 border border-red-400 rounded-b">
                            <p>NODE DATABASE HOST IP is mandatory.</p>
                        </div>
                        @enderror

                        <p for="NODE_DB_HOST" class="block mb-1 mt-2 text-sm capitalize text-slate-400 dark:text-white ">NODE DATABASE HOST PORT</p>
                        <div id="NODE_DB_PORT">
                            <input  type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model.defer="NODE_DB_PORT" autocomplete="NODE_DB_PORT" />

                        </div>
                        @error('NODE_DB_PORT')
                        <div class="px-4 py-3 mt-1 text-red-700 bg-red-100 border border-red-400 rounded-b">
                            <p>NODE_DB_PORT is mandatory.</p>
                        </div>
                        @enderror

                        <p for="NODE_DB_DATABASE" class="block mb-1 mt-2 text-sm capitalize text-slate-400 dark:text-white ">NODE DATABASE NAME</p>
                        <div id="NODE_DB_DATABASE">
                            <input  type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model.defer="NODE_DB_DATABASE" autocomplete="NODE_DB_DATABASE" />

                        </div>
                        @error('NODE_DB_DATABASE')
                        <div class="px-4 py-3 mt-1 text-red-700 bg-red-100 border border-red-400 rounded-b">
                            <p>NODE DATABASE NAME is mandatory.</p>
                        </div>
                        @enderror

                        <p for="NODE_DB_USERNAME" class="block mb-1 mt-2 text-sm capitalize text-slate-400 dark:text-white ">NODE DATABASE USERNAME</p>
                        <div id="NODE_DB_USERNAME">
                            <input  type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model.defer="NODE_DB_USERNAME" autocomplete="NODE_DB_USERNAME" />

                        </div>
                        @error('NODE_DB_USERNAME')
                        <div class="px-4 py-3 mt-1 text-red-700 bg-red-100 border border-red-400 rounded-b">
                            <p>NODE DATABASE USERNAME is mandatory.</p>
                        </div>
                        @enderror

                        <p for="NODE_DB_PASSWORD" class="block mb-1 mt-2 text-sm capitalize text-slate-400 dark:text-white ">NODE DATABASE PASSWORD</p>
                        <div id="NODE_DB_PASSWORD">
                            <input  type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model.defer="NODE_DB_PASSWORD" autocomplete="NODE_DB_PASSWORD" />

                        </div>
                        @error('NODE_DB_PASSWORD')
                        <div class="px-4 py-3 mt-1 text-red-700 bg-red-100 border border-red-400 rounded-b">
                            <p>NODE_DB_PASSWORD is mandatory.</p>
                        </div>
                        @enderror


                        <p for="DB_TABLE_NAME" class="block mb-1 mt-2 text-sm capitalize text-slate-400 dark:text-white ">NODE TABLE NAME</p>
                        <div id="DB_TABLE_NAME">
                            <input type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model.defer="DB_TABLE_NAME" autocomplete="DB_TABLE_NAME" />

                        </div>

                        @error('DB_TABLE_NAME')
                        <div class="px-4 py-3 mt-1 text-red-700 bg-red-100 border border-red-400 rounded-b">
                            <p>DB_TABLE_NAME is mandatory.</p>
                        </div>
                        @enderror


                        <p for="query" class="block mt-2 mb-1 mt-2 text-sm capitalize text-slate-400 dark:text-white ">QUERY</p>

                        <div id="query" class="w-full mb-4 border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                            <div class="flex items-center justify-between px-3 py-2 border-b dark:border-gray-600">
                                <div class="flex flex-wrap items-center divide-gray-200 sm:divide-x dark:divide-gray-600">

                                </div>
                                <button type="button" data-tooltip-target="tooltip-fullscreen" class="p-2 text-gray-500 rounded cursor-pointer sm:ml-auto hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600">
                                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h4a1 1 0 010 2H6.414l2.293 2.293a1 1 0 11-1.414 1.414L5 6.414V8a1 1 0 01-2 0V4zm9 1a1 1 0 010-2h4a1 1 0 011 1v4a1 1 0 01-2 0V6.414l-2.293 2.293a1 1 0 11-1.414-1.414L13.586 5H12zm-9 7a1 1 0 012 0v1.586l2.293-2.293a1 1 0 111.414 1.414L6.414 15H8a1 1 0 010 2H4a1 1 0 01-1-1v-4zm13-1a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 010-2h1.586l-2.293-2.293a1 1 0 111.414-1.414L15 13.586V12a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
                                    <span class="sr-only">Full screen</span>
                                </button>
                                <div id="tooltip-fullscreen" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                    Show full screen
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                            </div>
                            <div class="px-4 py-2 bg-white rounded-b-lg dark:bg-gray-800">
                                <label for="editor" class="sr-only">Publish post</label>
                                <textarea wire:model.defer="QUERY" autocomplete="QUERY" id="QUERY" rows="5" class="block w-full px-0 text-sm text-gray-800 bg-white border-0 dark:bg-gray-800 focus:ring-0 dark:text-white dark:placeholder-gray-400" placeholder="" required></textarea>
                            </div>
                            @error('QUERY')
                            <div class="px-4 py-3 mt-2 text-red-700 bg-red-100 border border-red-400 rounded-b">
                                <p>QUERY is mandatory.</p>
                            </div>
                            @enderror
                        </div>

                    @endif




                </div>
            </div>
            @endif


        </div>



                @if($this->nodeSelected)

                    <div class="p-4">

                        <div class="flex justify-end w-auto" >
                            <div wire:loading wire:target="save" >
                                <button class="px-4 py-2 text-sm font-medium text-white bg-blue-400 rounded-lg hover:bg-blue-400 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-400 dark:hover:bg-blue-400 dark:focus:ring-blue-400" disabled>
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 animate-spin stroke-white-800" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />

                                        </svg>
                                        <p>Please wait...</p>
                                    </div>
                                </button>
                            </div>

                        </div>


                        <div class="flex justify-end w-auto" >
                            <div wire:loading.remove wire:target="save" >
                                <button wire:click="save" class="px-4 py-2 text-sm font-medium text-white bg-blue-400 rounded-lg hover:bg-blue-400 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-400 dark:hover:bg-blue-400 dark:focus:ring-blue-400">
                                    Save
                                </button>

                            </div>
                        </div>
                    </div>

                @endif


            </div>

        </div>







</div>
