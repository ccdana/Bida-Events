<div class="block">
    <h3 class="block-title">{{ $copy['court_title'] ?? 'Padrinos y cortejo' }}</h3>
    @if($honor['godparents'])
        <table class="pairs">
            @foreach($honor['godparents'] as $godparent)
                <tr>
                    <td class="pair-label">{{ $godparent['role'] ?? 'Padrinos' }}</td>
                    <td class="strong">{{ $godparent['names'] }}</td>
                </tr>
            @endforeach
        </table>
    @endif
    @if($honor['chambelanes'])
        <p style="margin-top: 2.5mm;"><span class="strong">{{ $copy['court_men'] ?? 'Chambelanes' }}:</span> {{ implode(', ', $honor['chambelanes']) }}</p>
    @endif
    @if($honor['damitas'])
        <p><span class="strong">{{ $copy['court_women'] ?? 'Damitas' }}:</span> {{ implode(', ', $honor['damitas']) }}</p>
    @endif
</div>
