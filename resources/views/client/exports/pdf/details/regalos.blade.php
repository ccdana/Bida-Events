<div class="block">
    <h3 class="block-title">{{ $gifts['title'] }}</h3>
    @if($gifts['envelopes'])
        <p class="strong">{{ $gifts['envelopes']['title'] }}</p>
        <p>{{ $gifts['envelopes']['address'] }}</p>
    @endif
    @if($gifts['bank'])
        <p class="strong" style="margin-top: 2.5mm;">Transferencia bancaria</p>
        <table class="pairs">
            @foreach($gifts['bank'] as $label => $value)
                <tr>
                    <td class="pair-label">{{ $label }}</td>
                    <td>{{ $value }}</td>
                </tr>
            @endforeach
        </table>
    @endif
    @if($gifts['bankQr'])
        <img class="qr-small" src="{{ $gifts['bankQr'] }}" alt="" style="margin: 2.5mm 0 0;">
        <p class="caption" style="text-align: left;">QR para transferencias</p>
    @endif
    @if($gifts['store'])
        <p style="margin-top: 2.5mm;"><span class="strong">{{ $gifts['store']['label'] }}:</span> {{ $gifts['store']['url'] }}</p>
    @endif
    @foreach($gifts['options'] as $option)
        <p><span class="strong">{{ $option['title'] }}</span>@if($option['description']): {{ $option['description'] }}@endif</p>
    @endforeach
</div>
