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
    'afterExpr' => 'schedulePreview()',
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
                <x-phosphor-music-notes class="w-8 h-8 text-amber-600" aria-hidden="true" />
            @endif
        </div>

        <div class="flex-1 space-y-2">
            <label class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl border-2 border-dashed border-stone-200 hover:border-amber-400 hover:bg-amber-50/30 cursor-pointer transition text-sm text-stone-600"
                :class="mediaUploading ? 'opacity-60 pointer-events-none' : ''">
                <x-phosphor-upload-simple class="w-4 h-4 shrink-0" aria-hidden="true" />
                <span x-text="mediaUploading ? 'Subiendo archivos...' : 'Elegir archivo'"></span>
                <input type="file" accept="{{ $accept }}" class="hidden"
                    @change="pickLocalFileReplace($event, '{{ $type }}', '{{ $context }}', () => {{ $previewExpr }}, url => { {{ $previewExpr }} = url; {{ $afterExpr }} })">
            </label>
            @if($type === 'image')
                <button type="button"
                    x-show="{{ $previewExpr }}"
                    x-cloak
                    @click="openImageCropper({{ $previewExpr }}, '{{ $context }}')"
                    class="text-[11px] text-stone-600 hover:text-stone-900">
                    Recortar imagen según tarjeta
                </button>
            @endif
            <button type="button" x-show="{{ $previewExpr }}" x-cloak
                @click="clearMediaUrl({{ $previewExpr }}); {{ $clear }}; schedulePreview()"
                class="text-xs text-red-600 hover:text-red-800">Quitar</button>
        </div>
    </div>
</div>
