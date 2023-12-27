<x-profile-layout>
    <x-slot name="asideTitle">
        {{ __('Profile') }}
    </x-slot>
    <div class="">
        <div class="mx-auto text-center">
            <h4 class="font-semibold text-lg text-gray-800 leading-tight">{{$user->name}}</h4>
            <p class="text-gray-500 text-sm mt-2">{{$user->email}}</p>
        </div>
    </div>
</x-profile-layout>
