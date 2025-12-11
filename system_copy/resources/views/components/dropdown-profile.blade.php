@props([
    'align' => 'right'
])

<div>
    <div class="relative inline-block text-left">

            <button  id="dropdown-btn"
                     class="inline-flex justify-center items-center group"
                    aria-haspopup="true"
                    @click.prevent="open = !open"
                    :aria-expanded="open"
            >
                <img class="w-8 h-8 rounded-full" src="{{ asset('images/avatar.png')  }}" width="32" height="32" alt="{{ Auth::user()->name }}" />
                <div class="flex items-center truncate">
                    <span class="truncate ml-2 text-sm font-medium group-hover:text-slate-800">{{ Auth::user()->name }}</span>
                    <svg class="w-3 h-3 shrink-0 ml-1 fill-current text-slate-400" viewBox="0 0 12 12">
                        <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                    </svg>
                </div>
            </button>

        <div id="dropdown-menu" class="hidden absolute z-50  bg-white border border-gray-300 rounded shadow-lg w-96">
            <div class="pt-0.5 pb-2 px-3 mb-1 border-b border-slate-200">
                <div class="font-medium text-slate-800">{{ Auth::user()->name }}</div>
                <div class="text-xs text-slate-500 italic">Authorized User</div>
            </div>

            <li>
                <form method="POST" action="{{ route('logout') }}" x-data >
                    @csrf

                    <a class="font-medium text-sm text-indigo-500 hover:text-indigo-600 flex items-center py-1 mb-2 px-3"
                       href="{{ route('logout') }}"
                       @click.prevent="$root.submit();"
                       @focus="open = true"
                       @focusout="open = false"
                    >
                        {{ __('Sign Out') }}
                    </a>
                </form>
            </li>

             </div>
    </div>

    <script nonce="{{ csp_nonce() }}">
        const dropdownBtn = document.getElementById('dropdown-btn');
        const dropdownMenu = document.getElementById('dropdown-menu');

        dropdownBtn.addEventListener('click', () => {
            dropdownMenu.classList.toggle('hidden');
        });

        document.addEventListener('click', (event) => {
            if (!dropdownBtn.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.classList.add('hidden');
            }
        });
    </script>
</div>

