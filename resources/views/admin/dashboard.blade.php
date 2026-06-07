@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-10">
    <div>
        <h1 class="text-3xl font-serif text-stone-900">Invitaciones</h1>
        <p class="text-stone-500 mt-1">Crea, edita y gestiona eventos con vista previa en tiempo real.</p>
    </div>
    <a href="{{ route('admin.invitations.create') }}"
        class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-stone-900 text-white text-sm font-medium rounded-xl hover:bg-stone-800 transition shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
        Nueva invitación
    </a>
</div>

@if($invitations->isNotEmpty())
<div class="grid sm:grid-cols-3 gap-4 mb-8">
    <div class="rounded-2xl border border-stone-200 bg-white p-5">
        <p class="text-3xl font-serif text-stone-900">{{ $invitations->count() }}</p>
        <p class="text-xs uppercase tracking-wider text-stone-500 mt-1">Total invitaciones</p>
    </div>
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-5">
        <p class="text-3xl font-serif text-emerald-800">{{ $invitations->where('status', 'active')->count() }}</p>
        <p class="text-xs uppercase tracking-wider text-emerald-600 mt-1">Activas</p>
    </div>
    <div class="rounded-2xl border border-amber-200 bg-amber-50/50 p-5">
        <p class="text-3xl font-serif text-amber-900">{{ $invitations->sum(fn ($i) => $i->guests->count()) }}</p>
        <p class="text-xs uppercase tracking-wider text-amber-700 mt-1">Invitados totales</p>
    </div>
</div>
@endif

<div class="space-y-3">
    @forelse($invitations as $invitation)
        <article class="group rounded-2xl border border-stone-200 bg-white p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4 hover:border-stone-300 hover:shadow-sm transition">
            <div class="min-w-0">
                <div class="flex items-center gap-3 flex-wrap">
                    <h2 class="text-lg font-medium text-stone-900 truncate">{{ $invitation->title }}</h2>
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] uppercase tracking-wider font-medium
                        {{ $invitation->status === 'active' ? 'bg-emerald-100 text-emerald-800' : ($invitation->status === 'draft' ? 'bg-stone-100 text-stone-600' : 'bg-amber-100 text-amber-800') }}">
                        {{ $invitation->status }}
                    </span>
                </div>
                <p class="text-sm text-stone-500 mt-1">
                    {{ $invitation->eventType->name }} · {{ $invitation->event_date->format('d/m/Y H:i') }}
                </p>
                <p class="text-xs text-stone-400 mt-2 font-mono">/p/{{ $invitation->slug }} · {{ $invitation->guests->count() }} invitados</p>
            </div>
            <div class="flex flex-wrap gap-2 shrink-0">
                <a href="{{ route('invitation.show', $invitation->slug) }}" target="_blank"
                    class="px-3.5 py-2 text-xs uppercase tracking-wider border border-stone-200 rounded-lg hover:bg-stone-50 transition">Ver</a>
                <a href="{{ route('admin.invitations.edit', $invitation) }}"
                    class="px-3.5 py-2 text-xs uppercase tracking-wider bg-stone-900 text-white rounded-lg hover:bg-stone-800 transition">Editar</a>
                <a href="{{ route('admin.guests.index', $invitation) }}"
                    class="px-3.5 py-2 text-xs uppercase tracking-wider border border-amber-200 text-amber-900 rounded-lg hover:bg-amber-50 transition">Invitados</a>
            </div>
        </article>
    @empty
        <div class="rounded-2xl border-2 border-dashed border-stone-200 bg-white p-16 text-center">
            <p class="text-stone-500 mb-4">Aún no hay invitaciones creadas.</p>
            <a href="{{ route('admin.invitations.create') }}" class="text-amber-800 font-medium hover:underline">Crear la primera invitación</a>
        </div>
    @endforelse
</div>
@endsection
