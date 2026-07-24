<x-administration-layout>
    <section class="rounded-3xl bg-epf-purple px-6 py-8 text-white shadow-[0_24px_70px_rgba(38,0,82,0.16)] sm:px-9">
        <div class="flex flex-wrap items-end justify-between gap-5">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-200">Type de document</p>
                <h1 class="mt-3 text-3xl font-bold sm:text-4xl">{{ $typeDocument->libelle }}</h1>
                <p class="mt-4 max-w-3xl text-purple-100">Modifiez les formats et la taille acceptés sans changer le code utilisé par les candidatures.</p>
            </div>
            <span class="rounded-full px-4 py-2 text-sm font-bold {{ $typeDocument->actif ? 'bg-green-100 text-green-800' : 'bg-white/10 text-white' }}">{{ $typeDocument->actif ? 'Actif' : 'Inactif' }}</span>
        </div>
    </section>
    @if (session('status'))<div class="mt-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-semibold text-green-800" role="status">{{ session('status') }}</div>@endif
    @if ($errors->any())<div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800" role="alert"><p class="font-bold">Les modifications n’ont pas pu être enregistrées.</p><ul class="mt-2 list-disc pl-5">@foreach ($errors->all() as $erreur)<li>{{ $erreur }}</li>@endforeach</ul></div>@endif
    <section class="mt-8 rounded-3xl border border-purple-100 bg-white p-6 shadow-sm sm:p-8">
        <form method="POST" action="{{ route('administration.documents.update', $typeDocument) }}">@include('back-office.administration.documents._form')</form>
    </section>
</x-administration-layout>
