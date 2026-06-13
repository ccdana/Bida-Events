{{--
    Selección de medios con vista previa local. Se sube a Cloudinary al guardar la invitación.
--}}
@props([
    'label',
    'accept' => 'image/*',
    'type' => 'image',
    'context' => 'general',
    'previewExpr',
    'clearExpr' => null,
])
@php $clear = $clearExpr ?? ($previewExpr.' = null'); @endphp

<div class="space-y-2">
    <label class="admin-label">{{ $label }}</label>

    <div class="flex gap-3 items-start">
        <div x-show="{{ $previewExpr }}" x-cloak
            class="shrink-0 w-20 h-20 rounded-xl overflow-hidden border border-stone-200 bg-stone-50 flex items-center justify-center">
            @if($type === 'image')
                <img :src="{{ $previewExpr }}" alt="" class="w-full h-full object-cover">
            @elseif($type === 'video')
                <video :src="{{ $previewExpr }}" class="w-full h-full object-cover" muted playsinline></video>
            @else
                <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 18V5l12-2v13M6 18a3 3 0 100-6 3 3 0 000 6zm12-2a3 3 0 100-6 3 3 0 000 6z"/></svg>
            @endif
        </div>

        <div class="flex-1 space-y-2">
            <label class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl border-2 border-dashed border-stone-200 hover:border-amber-400 hover:bg-amber-50/30 cursor-pointer transition text-sm text-stone-600"
                :class="mediaUploading ? 'opacity-60 pointer-events-none' : ''">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5-5 5 5M12 5v12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span x-text="mediaUploading ? 'Subiendo archivos...' : 'Elegir archivo'"></span>
                <input type="file" accept="{{ $accept }}" class="hidden"
                    @change="pickLocalFileReplace($event, '{{ $type }}', '{{ $context }}', () => {{ $previewExpr }}, url => {{ $previewExpr }} = url)">
            </label>
            <button type="button" x-show="{{ $previewExpr }}" x-cloak
                @click="clearMediaUrl({{ $previewExpr }}); {{ $clear }}; schedulePreview()"
                class="text-xs text-red-600 hover:text-red-800">Quitar</button>
        </div>
    </div>
</div>
