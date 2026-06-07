@extends('layouts.admin')

@section('title', 'Invitados')

@section('content')
<div class="mb-8">
    <a href="{{ route('admin.dashboard') }}" class="text-xs uppercase tracking-wider text-stone-500 hover:text-stone-800">Dashboard</a>
    <h1 class="text-2xl font-serif text-stone-900 mt-2">{{ $invitation->title }}</h1>
    <p class="text-sm text-stone-500 mt-1 font-mono">Enlaces: /p/{{ $invitation->slug }}/i/{token}</p>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 admin-card !p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 text-stone-500 text-xs uppercase tracking-wider">
                <tr>
                    <th class="text-left px-5 py-3">Nombre</th>
                    <th class="text-left px-5 py-3">Pases</th>
                    <th class="text-left px-5 py-3">Estado</th>
                    <th class="text-left px-5 py-3">Enlace</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse($guests as $guest)
                    <tr class="hover:bg-stone-50/50">
                        <td class="px-5 py-3.5 font-medium">{{ $guest->name }}</td>
                        <td class="px-5 py-3.5 tabular-nums">{{ $guest->passes_confirmed }}/{{ $guest->passes_allocated }}</td>
                        <td class="px-5 py-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] uppercase tracking-wider font-medium
                                @if($guest->status === 'confirmed') bg-emerald-100 text-emerald-800
                                @elseif($guest->status === 'declined') bg-red-100 text-red-800
                                @else bg-stone-100 text-stone-600 @endif">{{ $guest->status }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <a href="{{ route('invitation.guest', [$invitation->slug, $guest->qr_code_token]) }}" target="_blank" class="text-xs text-amber-800 hover:underline">Abrir</a>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <form method="POST" action="{{ route('admin.guests.destroy', [$invitation, $guest]) }}" onsubmit="return confirm('Eliminar invitado?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-600 hover:text-red-800">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-12 text-center text-stone-400">Sin invitados aún</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="admin-card h-fit">
        <h2 class="font-medium text-stone-900 mb-4">Agregar invitado</h2>
        <form method="POST" action="{{ route('admin.guests.store', $invitation) }}" class="space-y-3">
            @csrf
            <div>
                <label class="admin-label">Nombre o familia</label>
                <input type="text" name="name" required class="admin-input">
            </div>
            <div>
                <label class="admin-label">Teléfono</label>
                <input type="text" name="phone" class="admin-input">
            </div>
            <div>
                <label class="admin-label">Pases asignados</label>
                <input type="number" name="passes_allocated" value="1" min="1" max="20" class="admin-input">
            </div>
            <button type="submit" class="w-full py-2.5 bg-stone-900 text-white text-sm rounded-xl hover:bg-stone-800 transition">Agregar</button>
        </form>
    </div>
</div>
@endsection
