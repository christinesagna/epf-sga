<x-administration-layout>
    <section class="rounded-3xl bg-epf-purple px-6 py-8 text-white shadow-[0_24px_70px_rgba(38,0,82,0.16)] sm:px-9">
        <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-200">Nouveau document</p>
        <h1 class="mt-3 text-3xl font-bold sm:text-4xl">Créer un type de document</h1>
        <p class="mt-4 max-w-3xl leading-7 text-purple-100">Le code sera généré automatiquement. Le document sera créé inactif avant sa mise à disposition.</p>
    </section>
    @if ($errors->any())
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800" role="alert"><p class="font-bold">Le type de document n’a pas pu être créé.</p><ul class="mt-2 list-disc pl-5">@foreach ($errors->all() as $erreur)<li>{{ $erreur }}</li>@endforeach</ul></div>
    @endif
    <section class="mt-8 rounded-3xl border border-purple-100 bg-white p-6 shadow-sm sm:p-8">
        <form method="POST" action="{{ route('administration.documents.store') }}">@include('back-office.administration.documents._form')</form>
    </section>
</x-administration-layout>
