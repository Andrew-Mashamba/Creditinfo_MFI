
<div class="bg-white">
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
{{--        @if (Laravel\Fortify\Features::canUpdateProfileInformation())--}}
{{--            <div class="bg-gray-50 p-2 rounded-xl mb-4">--}}
{{--                <div class="bg-white p-4 rounded-xl">--}}
{{--                    @livewire('profile.update-profile-information-form')--}}
{{--                </div>--}}

{{--            </div>--}}


{{--        @endif--}}

        @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                <div class="bg-gray-50 p-2 rounded-xl mb-4">
                    <div class="bg-white p-4 rounded-xl">
                        @livewire('profile.update-password-form')
                    </div>
                </div>

        @endif

            <div class="bg-gray-50 p-2 rounded-xl mb-4">
                <div class="bg-white p-4 rounded-xl">
            @livewire('profile.logout-other-browser-sessions-form')
                </div>
            </div>

    </div>
</div>

