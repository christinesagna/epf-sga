<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex min-h-12 items-center justify-center rounded-xl border border-transparent bg-epf-red px-6 py-3 text-sm font-bold uppercase tracking-wide text-white shadow-sm transition hover:bg-epf-red-dark focus:outline-none focus:ring-4 focus:ring-red-200 disabled:cursor-not-allowed disabled:opacity-60']) }}>
    {{ $slot }}
</button>
