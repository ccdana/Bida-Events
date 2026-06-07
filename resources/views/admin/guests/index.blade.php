@extends('layouts.admin')

@section('title', 'Invitados')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.dashboard') }}" class="text-sm text-stone-500">← Dashboard</a>
    <h1 class="text-2xl font-serif mt-2">Invitados — {{ $invitation->title }}</h1>
    <p class="text-sm text-stone-500 mt-1">Enlaces personalizados: /p/{{ $invitation->slug }}/i/{token}</p>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="rounded-2xl border border-stone-200 bg-white overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 text-stone-600">
                    <tr>
                        <th class="text-left px-4 py-3">Nombre</th>
                        <th class="text-left px-4 py-3">Pases</th>
                        <th class="text-left px-4 py-3">Estado</th>
                        <th class="text-left px-4 py-3">Enlace</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach($guests as $guest)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $guest->name }}</td>
                            <td class="px-4 py-3">{{ $guest->passes_confirmed }}/{{ $guest->passes_allocated }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs
                                    @if($guest->status === 'confirmed') bg-emerald-100 text-emerald-800
                                    @elseif($guest->status === 'declined') bg-red-100 text-red-800
                                    @else bg-stone-100 text-stone-600 @endif">
                                    {{ $guest->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('invitation.guest', [$invitation->slug, $guest->qr_code_token]) }}" target="_blank" class="text-amber-700 underline text-xs">Abrir</a>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.guests.destroy', [$invitation, $guest]) }}" onsubmit="return confirm('¿Eliminar?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 text-xs">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <form method="POST" action="{{ route('admin.guests.store', $invitation) }}" class="rounded-2xl border border-stone-200 bg-white p-6 space-y-4">
            @csrf
            <h2 class="font-medium">Agregar invitado</h2>
            <input type="text" name="name" required placeholder="Nombre o familia" class="w-full rounded-lg border-stone-300">
            <input type="text" name="phone" placeholder="Teléfono (opcional)" class="w-full rounded-lg border-stone-300">
            <input type="number" name="passes_allocated" value="1" min="1" max="20" class="w-full rounded-lg border-stone-300">
            <button type="submit" class="w-full py-2 bg-stone-900 text-white rounded-lg">Agregar</button>
        </form>
    </div>
</div>
@endsection
