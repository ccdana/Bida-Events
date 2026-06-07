@extends('layouts.admin')

@section('title', 'Editar Invitación')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-stone-500 hover:text-stone-800">← Volver</a>
        <h1 class="text-2xl font-serif text-stone-900 mt-2">{{ $invitation->title }}</h1>
    </div>
    <a href="{{ route('invitation.show', $invitation->slug) }}" target="_blank" class="text-sm text-amber-800 underline">Ver invitación pública</a>
</div>

<form method="POST" action="{{ route('admin.invitations.update', $invitation) }}" x-data="invitationEditor(@js($modulos))">
    @csrf
    @method('PUT')

    {{-- Datos base --}}
    <section class="rounded-2xl border border-stone-200 bg-white p-6 mb-6">
        <h2 class="text-lg font-medium mb-4">Datos generales</h2>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-stone-600 mb-1">Título</label>
                <input type="text" name="title" value="{{ old('title', $invitation->title) }}" class="w-full rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-500">
            </div>
            <div>
                <label class="block text-sm text-stone-600 mb-1">Slug (URL)</label>
                <input type="text" name="slug" value="{{ old('slug', $invitation->slug) }}" class="w-full rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-500">
            </div>
            <div>
                <label class="block text-sm text-stone-600 mb-1">Plantilla</label>
                <select name="template" class="w-full rounded-lg border-stone-300">
                    @foreach($templates as $value => $label)
                        <option value="{{ $value }}" @selected($invitation->template === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-stone-600 mb-1">Estado</label>
                <select name="status" class="w-full rounded-lg border-stone-300">
                    @foreach(['draft','active','suspended','expired'] as $status)
                        <option value="{{ $status }}" @selected($invitation->status === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-stone-600 mb-1">Fecha del evento</label>
                <input type="datetime-local" name="event_date" value="{{ old('event_date', $invitation->event_date->format('Y-m-d\TH:i')) }}" class="w-full rounded-lg border-stone-300">
            </div>
            <div>
                <label class="block text-sm text-stone-600 mb-1">Expira el</label>
                <input type="date" name="expires_at" value="{{ old('expires_at', $invitation->expires_at->format('Y-m-d')) }}" class="w-full rounded-lg border-stone-300">
            </div>
        </div>
    </section>

    {{-- Estética y módulos --}}
    <section class="rounded-2xl border border-stone-200 bg-white p-6 mb-6">
        <h2 class="text-lg font-medium mb-4">Estética & Módulos</h2>
        <div class="grid sm:grid-cols-3 gap-4 mb-6">
            <template x-for="(color, key) in modules.config.colores" :key="key">
                <div>
                    <label class="block text-xs uppercase tracking-wider text-stone-500 mb-1" x-text="key"></label>
                    <input type="color" x-model="modules.config.colores[key]" class="w-full h-10 rounded cursor-pointer">
                </div>
            </template>
        </div>
        <div class="grid sm:grid-cols-3 gap-4 mb-6">
            <div>
                <label class="block text-sm text-stone-600 mb-1">Fuente títulos</label>
                <input type="text" x-model="modules.config.tipografias.titulos" class="w-full rounded-lg border-stone-300">
            </div>
            <div>
                <label class="block text-sm text-stone-600 mb-1">Fuente cuerpo</label>
                <input type="text" x-model="modules.config.tipografias.cuerpo" class="w-full rounded-lg border-stone-300">
            </div>
            <div>
                <label class="block text-sm text-stone-600 mb-1">Fuente script</label>
                <input type="text" x-model="modules.config.tipografias.script" class="w-full rounded-lg border-stone-300">
            </div>
        </div>
        <div class="grid sm:grid-cols-3 gap-3">
            <template x-for="(enabled, code) in modules.config.modulos" :key="code">
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="checkbox" x-model="modules.config.modulos[code]" class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                    <span x-text="code.replace(/_/g, ' ')"></span>
                </label>
            </template>
        </div>
        <input type="hidden" name="modulos[config]" :value="JSON.stringify(modules.config)">
    </section>

    {{-- Textos bienvenida --}}
    <section class="rounded-2xl border border-stone-200 bg-white p-6 mb-6">
        <h2 class="text-lg font-medium mb-4">Bienvenida</h2>
        <div class="space-y-3">
            <input type="text" x-model="modules.bienvenida.nombre_quinceanera" placeholder="Nombre quinceañera" class="w-full rounded-lg border-stone-300">
            <input type="text" x-model="modules.bienvenida.subtitulo" placeholder="Subtítulo" class="w-full rounded-lg border-stone-300">
            <textarea x-model="modules.bienvenida.mensaje" rows="3" class="w-full rounded-lg border-stone-300"></textarea>
            <input type="text" x-model="modules.bienvenida.fecha_texto" placeholder="Texto fecha" class="w-full rounded-lg border-stone-300">
        </div>
        <input type="hidden" name="modulos[bienvenida]" :value="JSON.stringify(modules.bienvenida)">
    </section>

    {{-- Ubicación --}}
    <section class="rounded-2xl border border-stone-200 bg-white p-6 mb-6">
        <h2 class="text-lg font-medium mb-4">Ubicación</h2>
        <div class="space-y-3">
            <input type="text" x-model="modules.ubicacion.nombre_lugar" class="w-full rounded-lg border-stone-300" placeholder="Nombre del lugar">
            <input type="text" x-model="modules.ubicacion.direccion" class="w-full rounded-lg border-stone-300" placeholder="Dirección">
            <input type="url" x-model="modules.ubicacion.maps_url" class="w-full rounded-lg border-stone-300" placeholder="URL Google Maps">
        </div>
        <input type="hidden" name="modulos[ubicacion]" :value="JSON.stringify(modules.ubicacion)">
    </section>

    {{-- Hidden JSON para módulos no editados inline --}}
    @foreach(['itinerario','dress_code','destacados','galeria','musica','video','playlist','hashtag','encuestas','regalos','post_evento','rsvp'] as $code)
        <input type="hidden" name="modulos[{{ $code }}]" :value="JSON.stringify(modules['{{ $code }}'] || {})">
    @endforeach

    <div class="flex justify-end">
        <button type="submit" class="px-6 py-3 bg-stone-900 text-white rounded-xl hover:bg-stone-800 transition font-medium">Guardar cambios</button>
    </div>
</form>

<script>
function invitationEditor(initial) {
    return { modules: initial };
}
</script>
@endsection
