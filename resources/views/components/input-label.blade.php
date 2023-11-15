@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-gray-700 leading-6']) }}>
    {{ $value ?? $slot }}
</label>
