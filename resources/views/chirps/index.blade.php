<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            反馈
        </h2>
    </x-slot>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-12">
        <div class="p-5 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="max-w-xl">
                <form method="POST" action="{{ route('chirps.store') }}">
                    @csrf
                    <textarea
                        name="message"
                        placeholder="{{ __('What\'s on your mind?') }}"
                        class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                    >{{ old('message') }}</textarea>
                    <x-input-error :messages="$errors->get('message')" class="mt-2"/>
                    <x-primary-button class="mt-4">{{ __('Chirp') }}</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
