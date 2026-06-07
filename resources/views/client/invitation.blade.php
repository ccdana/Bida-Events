@extends('layouts.client')

@section('title', $invitation->title)

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <a href="{{ route('client.dashboard') }}" class="text-sm text-stone-500">← Mis eventos</a>
        <h1 class="text-2xl font-serif mt-2">{{ $invitation->title }}</h1>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('client.export.excel', $invitation) }}" class="px-4 py-2 text-sm border rounded-lg">Descargar Excel</a>
        <a href="{{ route('client.export.pdf', $invitation) }}" class="px-4 py-2 text-sm border rounded-lg">Descargar PDF</a>
    </div>
</div>

<div class="grid sm:grid-cols-3 gap-4 mb-8">
    <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-center">
        <p class="text-3xl font-serif text-emerald-800">{{ $confirmed->count() }}</p>
        <p class="text-sm text-emerald-600">Confirmados</p>
    </div>
    <div class="rounded-xl bg-amber-50 border border-amber-200 p-4 text-center">
        <p class="text-3xl font-serif text-amber-800">{{ $totalPasses }}</p>
        <p class="text-sm text-amber-600">Pases totales</p>
    </div>
    <div class="rounded-xl bg-stone-100 border border-stone-200 p-4 text-center">
        <p class="text-3xl font-serif text-stone-700">{{ $pending->count() }}</p>
        <p class="text-sm text-stone-500">Pendientes</p>
    </div>
</div>

<div class="rounded-2xl border border-stone-200 bg-white overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-stone-50">
            <tr>
                <th class="text-left px-4 py-3">Invitado</th>
                <th class="text-left px-4 py-3">Estado</th>
                <th class="text-left px-4 py-3">Pases</th>
                <th class="text-left px-4 py-3">Alimentación</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @foreach($guests as $guest)
                <tr>
                    <td class="px-4 py-3">{{ $guest->name }}</td>
                    <td class="px-4 py-3">{{ $guest->status }}</td>
                    <td class="px-4 py-3">{{ $guest->passes_confirmed }}/{{ $guest->passes_allocated }}</td>
                    <td class="px-4 py-3 text-stone-500">{{ $guest->dietary_restrictions ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
