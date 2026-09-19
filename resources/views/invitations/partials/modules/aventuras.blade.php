{{--
    «Aventuras por vivir» (App\Modules\Card\AdventuresModule) fuera del cuaderno: una lista con su
    título. El cuaderno usa partials/aventura/pages/aventuras. Recibe $data (titulo, lista).
--}}
@php
    $adventureItems = array_values(array_filter(
        array_map(fn ($item) => is_array($item) ? trim((string) ($item['titulo'] ?? '')) : '', (array) ($data['lista'] ?? [])),
        fn (string $text) => $text !== '',
    ));
@endphp

@if($adventureItems !== [])
    <section class="inv-section reveal" id="aventuras">
        <div class="inv-wrap">
            @include('invitations.partials.section-header', ['compact' => true, 'title' => ($data['titulo'] ?? null) ?: 'Aventuras por vivir'])
            <ul class="inv-card-entry">
                @foreach($adventureItems as $adventure)
                    <li>{{ $adventure }}</li>
                @endforeach
            </ul>
        </div>
    </section>
@endif
