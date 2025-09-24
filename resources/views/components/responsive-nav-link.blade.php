@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-black text-start text-base font-medium text-black bg-gray-100 focus:outline-none focus:text-black focus:bg-gray-100 focus:border-black transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-black hover:text-black hover:bg-gray-50 hover:border-black focus:outline-none focus:text-black focus:bg-gray-50 focus:border-black transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
