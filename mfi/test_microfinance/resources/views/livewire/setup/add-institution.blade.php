<div class="w-full p-2" >
    <!-- message container -->
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
        @endif
    </div>


    <div class="bg-gray-100 rounded rounded-lg shadow-sm ">







        <div class="flex gap-2 pt-2 pl-2 pr-2 pb-2">

            <div class="w-1/3 bg-white rounded px-6 rounded-lg shadow-sm   pt-4 pb-4 " >
                <p for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Institution Number</p>
                <p for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">{{$this->currentInstitutionID}}</p>

                <div class="mt-2"></div>

                <p for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Institution Name</p>
                <input type="text" id="name" name="name"  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm
                    rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700
                    dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500"
                       wire:model.lazy="name"  required>
                @error('name')
                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                    <p>The name is mandatory and should be more than two characters.</p>
                </div>
                @enderror
                <div class="mt-2"></div>

                <p for="region" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Region</p>
                <input type="text" id="region" name="region"  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm
                    rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700
                    dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500"
                       wire:model.lazy="region"  required>
                @error('region')
                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                    <p>The business name is mandatory and should be more than two characters.</p>
                </div>
                @enderror
                <div class="mt-2"></div>

                <p for="wilaya" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">wilaya</p>
                <input type="text" id="wilaya" name="wilaya"  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm
                    rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700
                    dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500"
                       wire:model.lazy="wilaya"  required>
                @error('wilaya')
                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                    <p>The business name is mandatory and should be more than two characters.</p>
                </div>
                @enderror
                <div class="mt-2"></div>

                <p for="phone_number" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">phone_number</p>
                <input type="number" id="phone_number" name="phone_number"  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm
                    rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700
                    dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500"
                       wire:model.lazy="phone_number"  required>
                @error('phone_number')
                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                    <p>The business name is mandatory and should be more than two characters.</p>
                </div>
                @enderror
                <div class="mt-2"></div>

                <p for="aname" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Admin name</p>
                <input type="aname" id="aname" name="aname"  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm
                    rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700
                    dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500"
                       wire:model.lazy="aname"  required>
                @error('aname')
                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                    <p>The business name is mandatory and should be more than two characters.</p>
                </div>
                @enderror
                <div class="mt-2"></div>

                <p for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Admin  email</p>
                <input type="email" id="email" name="email"  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm
                    rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5 dark:bg-gray-700
                    dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500"
                       wire:model.lazy="email"  required>
                @error('email')
                <div class="border border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mt-1">
                    <p>The business name is mandatory and should be more than two characters.</p>
                </div>
                @enderror
                <div class="mt-2"></div>


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
                            <p class="text-white">Save Institution</p>
                        </button>

                    </div>
                </div>


            </div>


            <div class="w-2/3 bg-white rounded px-6 rounded-lg shadow-sm  pt-4 pb-4  " >

                <form wire:submit.prevent="save" @disabled(Session::get('disableInputs'))>


                    <div class="flex justify-center m-8">
                        <div class="max-w-2xl rounded-lg shadow-sm bg-gray-50">
                            <div class="m-4">

                                <section class="bg-white-300 flex flex-col items-center justify-center">
                                    @if ($photo)
                                        <img class="object-fill w-3/5 " src="{{ $photo->temporaryUrl() }}">
                                    @endif
                                </section>




                                <div class="flex items-center justify-center w-full">



                                    <label class="flex flex-col w-full h-19 hover:bg-gray-100 hover:border-gray-300">
                                        <div class="flex flex-col items-center justify-center pt-7">

                                            <div wire:loading wire:target="photo" class="" >

                                                <svg style="width: 50%; margin: 0 auto;" xmlns="http://www.w3.org/2000/svg" class="animate-spin  w-8 h-8 text-gray-400 group-hover:text-gray-600" fill="white" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />

                                                </svg>
                                                <p class="pt-1 text-sm tracking-wider text-gray-400 group-hover:text-gray-600">Please wait...</p>

                                            </div>

                                            <div wire:loading.remove wire:target="photo" class="flex flex-col items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-400 group-hover:text-gray-600"
                                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                </svg>
                                                <p class="pt-1 text-sm tracking-wider text-gray-400 group-hover:text-gray-600">
                                                    Attach member image</p>

                                            </div>

                                        </div>
                                        <input type="file" class="opacity-0" wire:model="photo" @disabled(Session::get('disableInputs'))/>
                                    </label>


                                </div>

                                @if($this->photo)
                                    <div class="flex justify-end px-4 pt-4">
                                        <a wire:click="saveImage" class="ml-2 inline-flex items-center py-2 px-4 text-sm font-medium text-center text-gray-900
                            bg-white rounded-lg border border-gray-300 hover:bg-gray-100 focus:ring-4
                            focus:outline-none focus:ring-gray-200 dark:bg-gray-800 dark:text-white dark:border-gray-600
                            dark:hover:bg-gray-700 dark:hover:border-gray-700 dark:focus:ring-gray-700" style="cursor: pointer" >Set Image</a>
                                    </div>
                                @endif

                            </div>

                        </div>
                    </div>



                    @error('photo') <span class="error">{{ $message }}</span> @enderror


                </form>


                <style nonce="{{ csp_nonce() }}">
                    .gridx {
                        display: grid;

                        grid-gap: 0.5rem;
                        grid-template-columns: repeat(12, 1fr);
                    }
                    .gridx a img {
                        transition: all .2s ease-in;
                        filter: grayscale(10%);
                    }

                    .gridx a:hover img {
                        filter: grayscale(0);
                    }

                    @media screen and (min-width: 768px) {
                        .gridx {
                            grid-template-columns: repeat(2, 1fr);
                        }
                    }

                    @media screen and (min-width: 992px) {
                        .gridx {
                            grid-template-columns: repeat(3, 1fr);
                        }
                    }
                </style>

                <div class="w-full">


                    <div class="gridx max-w-4xl mx-auto">

                        @foreach(App\Models\loan_images::where('loan_id',App\Models\LoansModel::where('id',Session::get('currentloanID'))->value('loan_id'))->where('category','business')->get() as $image)

                            <div  class="bg-white rounded h-full text-grey-darkest no-underline shadow-md w-64">
                                <div class="w-full justify-items-right">
                                    <svg wire:click="close" xmlns="http://www.w3.org/2000/svg" class=" object-right h-12 w-12 bg-slate-50 rounded-full stroke-red-400 p-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <img class="block rounded-b w-64" src="http://zima.services/pamojacbs/storage/app/public/{{$image->url}}" alt="">
                            </div>
                        @endforeach

                    </div>

                </div>


            </div>



        </div>




    </div>


</div>
