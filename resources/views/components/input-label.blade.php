@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-semibold text-epf-purple']) }}>
    {{ $value ?? $slot }}
</label>
