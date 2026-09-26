{{--
    «Álbum de stickers»: la página del álbum con cada dato como un sticker de colección, con su borde
    blanco, la cara de color y la franja con su nombre: el sticker grande de quien cumple, la fecha,
    la hora y el lugar. Los colores salen de la paleta del cliente.
--}}
<style>
    .st-page { padding: 10mm 8mm; border-radius: 6mm; background: {{ $colors['tint'] }}; }
    .st-grid { width: 100%; margin-top: 6mm; border-collapse: separate; border-spacing: 4mm; }
    .st-grid td { padding: 0; border: 1.6mm solid #ffffff; border-radius: 4mm; text-align: center; vertical-align: top; background: #ffffff; }
    .st-face { padding: 5mm 3mm; border-radius: 3mm 3mm 0 0; background: {{ $colors['primary'] }}; color: #ffffff; }
    .st-face.is-soft { background: {{ $colors['soft'] }}; color: {{ $colors['ink'] }}; }
    .st-face.is-ink { background: {{ $colors['ink'] }}; color: #ffffff; }
    .st-face .name { margin: 0; color: inherit; }
    .st-face .big { margin: 0; font-size: 20pt; font-weight: bold; }
    .st-band { padding: 2mm 3mm; border-radius: 0 0 3mm 3mm; background: {{ $colors['accent'] }}; color: {{ $colors['ink'] }}; font-size: 9pt; font-weight: bold; }
</style>

<div class="sheet is-none">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell">
                <div class="st-page center">
                    <table class="st-grid">
                        <tr>
                            <td colspan="2">
                                <div class="st-face"><h1 class="name">{{ $hero['name'] }}</h1></div>
                                @if($hero['subtitle'])
                                    <div class="st-band">{{ $hero['subtitle'] }}</div>
                                @endif
                            </td>
                        </tr>
                        @if($hero['date'] || $hero['time'])
                            <tr>
                                @if($hero['date'])
                                    <td @if(! $hero['time']) colspan="2" @endif>
                                        <div class="st-face is-soft"><p class="value">{{ $hero['date'] }}</p></div>
                                        <div class="st-band">Fecha</div>
                                    </td>
                                @endif
                                @if($hero['time'])
                                    <td @if(! $hero['date']) colspan="2" @endif>
                                        <div class="st-face is-soft"><p class="big">{{ $hero['time'] }}</p></div>
                                        <div class="st-band">Hora</div>
                                    </td>
                                @endif
                            </tr>
                        @endif
                        @if($location)
                            <tr>
                                <td colspan="2">
                                    <div class="st-face is-ink"><p class="value">{{ $location['name'] ?? $location['address'] }}</p></div>
                                    <div class="st-band">Lugar</div>
                                </td>
                            </tr>
                        @endif
                    </table>

                    @if($hero['message'])
                        <p class="message" style="margin-top: 3mm;">{{ $hero['message'] }}</p>
                    @endif
                </div>
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
