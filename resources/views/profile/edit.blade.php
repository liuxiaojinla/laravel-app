<x-profile-layout>
    <x-slot name="asideTitle">
        {{ __('Profile') }}
    </x-slot>

    <div class="max-w-xl">
        @include('profile.partials.update-profile-information-form')
    </div>
</x-profile-layout>
