@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'mt-2 block min-h-12 w-full rounded-xl border-gray-300 bg-white px-4 text-epf-purple shadow-sm transition placeholder:text-gray-400 focus:border-epf-purple focus:ring-4 focus:ring-purple-100']) }}>
