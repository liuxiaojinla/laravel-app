<button {{ $attributes->merge(['type' => 'submit', 'class' => 'flex w-full justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold leading-6 text-white shadow-sm border border-transparent uppercase tracking-widest hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
