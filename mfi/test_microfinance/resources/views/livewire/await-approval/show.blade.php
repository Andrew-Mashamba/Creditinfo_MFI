<x-guest-layout  >

    <div class="text-xl">Your registration is awaiting approval..</div>





    <form method="POST" action="{{ route('logout') }}" x-data>
        @csrf

        <input value="Ok" type="submit" @click.prevent="$root.submit();" class="mt-8 py-[10px] sm:py-3 px-[12px] sm:px-6 inline-flex items-center justify-center
                            font-semibold border border-primary text-center text-white text-base bg-primary transition-all hover:bg-primary
                            hover:text-white hover:border-primary rounded-full cursor-pointer">



    </form>


</x-guest-layout>



