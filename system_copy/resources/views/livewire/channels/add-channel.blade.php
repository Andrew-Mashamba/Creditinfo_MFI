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
                <p for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">CHANNEL NAME</p>
                <input type="text" id="name" name="name"  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm
                     rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700
                     dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                       wire:model.lazy="name"  required>
                @error('name')
                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                    <p>The name is mandatory and should be more than two characters.</p>
                </div>
                @enderror
                <div class="mt-2"></div>


                <select wire:model.bounce="NewService" name="NewService" id="NewService" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option selected value="">ADD A SERVICE </option>
                    @foreach(\App\Models\servicesModel::get() as $service)
                        <option selected value="{{$service->ID}}">{{$service->NAME}}</option>
                    @endforeach
                </select>
                @error('servicesList')
                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                    <p>Add at least one service.</p>
                </div>
                @enderror

                <div class="mt-2"></div>
                <hr class="border-b-0 my-6"/>
                <div class="flex justify-end w-auto" >
                    <div wire:loading wire:target="save" >
                        <button class="text-white bg-blue-400 hover:bg-blue-400 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-400 dark:hover:bg-blue-400 dark:focus:ring-blue-400" disabled>
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
                        <button wire:click="save" class="text-white bg-blue-400 hover:bg-blue-400 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-400 dark:hover:bg-blue-400 dark:focus:ring-blue-400">
                            <p class="text-white">Save Service</p>
                        </button>
                    </div>
                </div>
            </div>
            <div class="w-2/3 bg-white rounded px-6 rounded-lg shadow-sm  pt-4 pb-4  " >

                <p for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">CHANNEL SERVICES</p>

                <table class="w-full">
                    <tbody>

                    @foreach($this->servicesList as $service)



                        <tr class=" flex border-b border-gray-200 dark:border-gray-900 w-full">

                            <td class="py-4 pl-4 sm:pl-6 w-1/4 ">
                                <div class="flex ">
                                    <div aria-label="briefcase icon" role="img" tabindex="0" class="flex items-center justify-center w-8 h-8 bg-blue-100 rounded focus:outline-none">
                                        <button type="button" class="relative inline-flex items-center p-1 text-sm font-medium text-center text-white  rounded-full bg-blue-100 hover:bg-blue-900 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-900 dark:hover:bg-blue-900 dark:focus:ring-blue-800">

                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                 viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 9.563C9 9.252 9.252 9 9.563 9h4.874c.311 0 .563.252.563.563v4.874c0 .311-.252.563-.563.563H9.564A.562.562 0 019 14.437V9.564z" />
                                            </svg>


                                        </button>
                                    </div>
                                    <div class="pl-4 ml-4 justify-start">
                                        <p tabindex="0" class="pb-2 text-sm leading-none text-gray-800 focus:outline-none dark:text-gray-100">
                                            {{App\Models\servicesModel::where('ID',$service['service_id'])->value('NAME')}}
                                        </p>

                                    </div>
                                </div>
                            </td>
                            <td class="py-4 pl-4 sm:pl-6 w-1/4">
                                <p tabindex="0" class="text-xs leading-3 text-gray-500 focus:outline-none dark:text-gray-400">
                                    {{App\Models\servicesModel::where('ID',$service['service_id'])->value('STATUS')}}
                                </p>
                            </td>

                            <td class="px-4 py-4 sm:px-6 w-1/4 text-center">
                                <svg wire:click="removeService({{$service['service_id']}})" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                     stroke-width="1.5" stroke="currentColor" class="p-2 rounded-full cursor-pointer h-9 bg-red-100 stroke-slate-400 block mx-auto">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </td>

                        </tr>

                    @endforeach


                    </tbody>

                </table>

            </div>
        </div>
    </div>

</div>





