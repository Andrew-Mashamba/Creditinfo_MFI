<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SaccosManagementSystem') }}</title>

    @livewireStyles


    <link rel="stylesheet" href="{{ asset('build/assets/app-0b078e59.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('assets/css/flowbite.min.css') }}" type="text/css">


    <style nonce="{{ csp_nonce() }}">

        @import url(//fonts.googleapis.com/css?family=Lato:300:400);

        .bodyGradient {
            position:relative;
            text-align:center;
            background: linear-gradient(to bottom left, #2D3A89 0%, rgba(84, 58, 183, 1) 50%, rgba(0, 172, 193, 1) 100%);
            color:white;
        }




        @media (max-width: 768px) {
            .waves {
                height:50px;
                min-height:40px;
                width: 50%;
            }
            .content {
                height:30vh;
            }
            h1 {
                font-size:24px;
            }
        }

        label {
            font-weight: inherit;
        }

        input[type="text"], input[type="email"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        button {
            background-color: #40a20e;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        button:hover {
            background-color: #7de70d;
        }
        body {
            font-family: Arial, sans-serif;
        }

        .intro-y{
            margin-left: 80px;
            font-weight: bold;
            margin-bottom: 0;
            float: bottom;
            justify-content: center;
            height: 20px;
        }

        ul li h1{
            text-decoration-color: black;
            text-decoration-line: underline;
            font-size: 24px;
        }
        .bg-set{
            background-size: cover;
            margin-top:-20px ;
        }

    </style>
</head>
<body class="font-inter w-full antialiased bg-black-100 text-black-600">







<main class="bg-gray-50 w-full">

    <div class="relative flex w-1/3">

        <!-- Content -->
        <div class="w-full md:w-1/2">

            <div class="min-h-screen h-full flex flex-col after:flex-1 justify-end">

                <!-- Header -->
                <div class="flex-1">
                    <div class="flex items-center mt-10 justify-between h-16 px-4 sm:px-6 lg:px-8">
                        <!-- Logo -->
                        <a class="block mt-10  flex items-center" href="{{ route('verify-account') }}">
                            <img class="mt-4" src="{{ asset('creditInfoLogor.png ') }}"
                                 height="300" width="300" alt="Authentication decoration" />

                        </a>
                    </div>
                </div>

                <div class="bg-set">
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.dev/svgjs" viewBox="0 0 800 800"><defs><linearGradient x1="50%" y1="0%" x2="50%" y2="100%" id="vvvortex-grad"><stop stop-color="hsl(1.4, 100%, 67%)" stop-opacity="1" offset="0%"></stop><stop stop-color="hsl(353, 98%, 41%)" stop-opacity="1" offset="100%"></stop></linearGradient></defs><g stroke="url(#vvvortex-grad)" fill="none" stroke-linecap="round"><circle r="363" cx="400" cy="400" stroke-width="11" stroke-dasharray="26 42" stroke-dashoffset="25" transform="rotate(258, 400, 400)" opacity="0.05"></circle><circle r="346.5" cx="400" cy="400" stroke-width="11" stroke-dasharray="48 51" stroke-dashoffset="25" transform="rotate(166, 400, 400)" opacity="0.10"></circle><circle r="330" cx="400" cy="400" stroke-width="10" stroke-dasharray="32 32" stroke-dashoffset="25" transform="rotate(185, 400, 400)" opacity="0.14"></circle><circle r="313.5" cx="400" cy="400" stroke-width="10" stroke-dasharray="40 55" stroke-dashoffset="25" transform="rotate(256, 400, 400)" opacity="0.19"></circle><circle r="297" cx="400" cy="400" stroke-width="10" stroke-dasharray="33 31" stroke-dashoffset="25" transform="rotate(194, 400, 400)" opacity="0.23"></circle><circle r="280.5" cx="400" cy="400" stroke-width="10" stroke-dasharray="50 19" stroke-dashoffset="25" transform="rotate(290, 400, 400)" opacity="0.28"></circle><circle r="264" cx="400" cy="400" stroke-width="9" stroke-dasharray="20 27" stroke-dashoffset="25" transform="rotate(246, 400, 400)" opacity="0.32"></circle><circle r="247.5" cx="400" cy="400" stroke-width="9" stroke-dasharray="12 11" stroke-dashoffset="25" transform="rotate(324, 400, 400)" opacity="0.37"></circle><circle r="231" cx="400" cy="400" stroke-width="9" stroke-dasharray="14 44" stroke-dashoffset="25" transform="rotate(338, 400, 400)" opacity="0.41"></circle><circle r="214.5" cx="400" cy="400" stroke-width="8" stroke-dasharray="17 32" stroke-dashoffset="25" transform="rotate(163, 400, 400)" opacity="0.46"></circle><circle r="198" cx="400" cy="400" stroke-width="8" stroke-dasharray="46 42" stroke-dashoffset="25" transform="rotate(172, 400, 400)" opacity="0.50"></circle><circle r="181.5" cx="400" cy="400" stroke-width="8" stroke-dasharray="26 37" stroke-dashoffset="25" transform="rotate(340, 400, 400)" opacity="0.55"></circle><circle r="165" cx="400" cy="400" stroke-width="8" stroke-dasharray="19 42" stroke-dashoffset="25" transform="rotate(81, 400, 400)" opacity="0.59"></circle><circle r="148.5" cx="400" cy="400" stroke-width="7" stroke-dasharray="14 35" stroke-dashoffset="25" transform="rotate(192, 400, 400)" opacity="0.64"></circle><circle r="132" cx="400" cy="400" stroke-width="7" stroke-dasharray="25 50" stroke-dashoffset="25" transform="rotate(243, 400, 400)" opacity="0.68"></circle><circle r="115.5" cx="400" cy="400" stroke-width="7" stroke-dasharray="15 19" stroke-dashoffset="25" transform="rotate(209, 400, 400)" opacity="0.73"></circle><circle r="99" cx="400" cy="400" stroke-width="6" stroke-dasharray="46 33" stroke-dashoffset="25" transform="rotate(275, 400, 400)" opacity="0.77"></circle><circle r="82.5" cx="400" cy="400" stroke-width="6" stroke-dasharray="30 18" stroke-dashoffset="25" transform="rotate(210, 400, 400)" opacity="0.82"></circle><circle r="66" cx="400" cy="400" stroke-width="6" stroke-dasharray="31 50" stroke-dashoffset="25" transform="rotate(147, 400, 400)" opacity="0.86"></circle><circle r="49.5" cx="400" cy="400" stroke-width="6" stroke-dasharray="49 38" stroke-dashoffset="25" transform="rotate(312, 400, 400)" opacity="0.91"></circle><circle r="33" cx="400" cy="400" stroke-width="5" stroke-dasharray="35 44" stroke-dashoffset="25" transform="rotate(135, 400, 400)" opacity="0.95"></circle><circle r="16.5" cx="400" cy="400" stroke-width="5" stroke-dasharray="12 49" stroke-dashoffset="25" transform="rotate(99, 400, 400)" opacity="1.00"></circle></g></svg>                </div>

                <div class="intro-y">




                    <div class="justify-start   flex item-start">
                     <ul>
                         <h1> powered by </h1>
                         <li class="flex items-center">
                             <svg fill="red" class="w-3.5 h-3.5 mr-2 text-black-400 dark:text-green-400 flex-shrink-0" stroke="black" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                 <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                             </svg>
                             CREDIT INFO,
                         </li>
                         <li class="flex items-center">
                             <svg fill="red" class="w-3.5 h-3.5 mr-2 text-black-400 dark:text-green-400 flex-shrink-0" stroke="black" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                 <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                             </svg>
                             ISALE GROUP
                         </li>
                     </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <div class="w-1/3">
            <div class="absolute z-50 top-0 left-0 right-0 bottom-0 flex justify-center items-center">
                <div id="xx" class="max-w-xl px-4 py-8 bg-white self-center rounded-xl shadow-md shadow-gray-200 " >
                    <div class="text-center w-full">
                        <span class="mt-4 mb-4 font-bold text-lg text-red-500 self-center text-center">MICROFINANCE REGISTRATION FORM </span>
                    </div>
                    <div class="mt-4"> </div>
                            <h2 class="p-4 text-center text-red justify-center">COMPANY INFORMATION</h2>




 <form action="{{route('saccossRequestForm')}}" method="post" enctype="multipart/form-data"> @csrf

     @if (session()->has('message'))
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
@if (session()->has('message_fail'))
         <div class="bg-black-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md mb-8" role="alert">
             <div class="flex auto">

                 <div class="py-1"><svg class="fill-current h-6 w-6 text-teal-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                 <div>
                     <p class="font-bold">The process is completed</p>
                     <p class="text-sm">{{ session('message_fail') }} </p>
                 </div>
             </div>
         </div>
     @endif



     @csrf
     <div class="-mx-3 md:flex mb-1">

         <div class="md:w-full px-3">
             <label class="block uppercase tracking-wide text-grey-darker text-xs font-bold mb-2" for="grid-last-name">
                 institution name
             </label>
             <input  name="name" class="appearance-none block w-full bg-grey-lighter text-grey-darker border border-grey-lighter rounded py-3 px-4" id="grid-last-name" type="text" placeholder="institution name">
         </div>
     </div>


     <div class="-mx-3 md:flex mb-1">

                                    <div class="md:w-1/2 px-3 mb-6 md:mb-0">
                                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-bold mb-2" for="grid-first-name">
                                             region
                                        </label>
                                        <input name="region" class="appearance-none block w-full bg-grey-lighter text-grey-darker border border-black rounded py-3 px-4 mb-3" id="grid-first-name" type="text" placeholder="e.g dar es salaam">
                                        <p class="text-black text-xs italic">Please fill out this field.</p>
                                    </div>
                                    <div class="md:w-1/2 px-3">
                                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-bold mb-2" for="grid-last-name">
                                            Wilaya
                                        </label>
                                        <input  name="wilaya" class="appearance-none block w-full bg-grey-lighter text-grey-darker border border-grey-lighter rounded py-3 px-4" id="grid-last-name" type="text" placeholder="wilaya">
                                    </div>
                                </div>
                                <div class="-mx-3 md:flex mb-1">
                                    <div class="md:w-1/2 px-3 mb-6 md:mb-0">
                                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-bold mb-2" for="grid-first-name">
                                             Manager Email
                                        </label>
                                        <input name="manager_email" class="appearance-none block w-full bg-grey-lighter text-grey-darker border border-black rounded py-3 px-4 mb-3" id="grid-first-name" type="email" placeholder="Email">
                                        <p class="text-black text-xs italic">Please fill out this field.</p>
                                    </div>
                                    <div class="md:w-1/2 px-3">
                                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-bold mb-2" for="grid-last-name">
                                            System Admin Email
                                        </label>
                                        <input  name="admin_email" class="appearance-none block w-full bg-grey-lighter text-grey-darker border border-grey-lighter rounded py-3 px-4" id="grid-last-name" type="email" placeholder="Email">
                                    </div>
                                </div>
                                <div class="-mx-3 md:flex mb-1">
                                    <div class="md:w-1/2 px-3">
                                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-bold mb-2" for="grid-password">
                                            Phone number
                                        </label>
                                        <input name="phone_number" class="appearance-none block w-full bg-grey-lighter text-grey-darker border border-grey-lighter rounded py-3 px-4 mb-3" id="grid-password" type="text" placeholder="+255***************">
                                        <p class="text-grey-dark text-xs italic">make sure you provide valid phone number</p>
                                    </div>

                                    <div class="md:w-1/2 px-3">
                                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-bold mb-2" for="grid-password">
                                            tin number
                                        </label>
                                        <input name="tin_number"  class="appearance-none block w-full bg-grey-lighter text-grey-darker border border-grey-lighter rounded py-3 px-4 mb-3" id="grid-password" type="text" placeholder="******************">
                                        <p class="text-grey-dark text-xs italic">this field is required</p>
                                    </div>
                                </div>
                                <div class="-mx-3 md:flex mb-1">
                                    <div class="md:w-1/2 px-3">
                                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-bold mb-2" for="grid-city">
                                            TCDC form
                                        </label>
                                        <input name="tcdc_form" class="appearance-none block w-full bg-grey-lighter text-grey-darker border border-grey-lighter rounded px-4" type="file" >
                                    </div>
                                    <div class="md:w-1/2 px-3">
                                        <label class="block uppercase tracking-wide text-grey-darker text-xs font-bold mb-2" for="grid-zip">
                                            Microfinance form
                                        </label>
                                        <input name="microfinance_license" class="appearance-none block w-full bg-grey-lighter text-grey-darker border border-grey-lighter rounded  px-4" id="grid-zip" type="file" >
                                    </div>
                                </div>

                    <div class=" flex justify-center items-center mt-2">
                        <button class="mt-2 bg-red-500  hover:bg-red-400 text-white font-bold py-2 px-4 rounded">
                            Send request
                        </button>
                    </div>
             </form>

                </div>
                <div class="fixed left-0 right-0 bottom-0 h-10">
                        <div class="footer-light text-center justify-center">
                            <div class="content bg-white">
                            </div>
                        </div>
                </div>
            </div>

        </div>


        <div class="w-1/3">

    </div>












</main>




@livewireScripts
</body>
</html>
