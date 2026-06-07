@extends('layouts.client')

@section('title', 'Mis Eventos')

@section('content')
<h1 class="text-3xl font-serif text-stone-900 mb-2">Mis Eventos</h1>
<p class="text-stone-500 mb-8">Consulta confirmaciones y descarga reportes.</p>

<div class="space-y-4">
    @foreach($invitations as $invitation)
        <article class="rounded-2xl border border-stone-200 bg-white p-6">
            <h2 class="text-xl font-medium">{{ $invitation->title }}</h2>
            <p class="text-sm text-stone-500 mt-1">{{ $invitation->event_date->format('d/m/Y H:i') }}</p>
            <div class="flex flex-wrap gap-3 mt-4 text-sm">
                <span class="px-3 py-1 bg-emerald-50 text-emerald-800 rounded-full">{{ $invitation->guests->where('status','confirmed')->count() }} confirmados</span>
                <span class="px-3 py-1 bg-stone-100 text-stone-600 rounded-full">{{ $invitation->guests->where('status','pending')->count() }} pendientes</span>
            </div>
            <div class="flex flex-wrap gap-2 mt-4">
                <a href="{{ route('client.invitation.show', $invitation) }}" class="px-4 py-2 bg-stone-900 text-white rounded-lg text-sm">Ver detalle</a>
                <a href="{{ route('client.export.excel', $invitation) }}" class="px-4 py-2 border border-stone-300 rounded-lg text-sm">Excel</a>
                <a href="{{ route('client.export.pdf', $invitation) }}" class="px-4 py-2 border border-stone-300 rounded-lg text-sm">PDF confirmados</a>
                <a href="{{ route('client.export.invitation-pdf', $invitation) }}" class="px-4 py-2 border border-amber-300 text-amber-900 rounded-lg text-sm">PDF invitación</a>
            </div>
        </article>
    @endforeach
</div>
@endsection
