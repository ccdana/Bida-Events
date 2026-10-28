{{--
    Un evento en la lista del inicio: una fila con filete (punto de estado, nombre, fecha, tres números sueltos y los
    íconos de acción), sin tarjeta. Recibe $row (App\ViewModels\Client\DashboardViewData).
--}}
@php($invitation = $row['invitation'])
@php($status = $row['isPast'] ? ['past', 'Ya celebrado'] : ($row['publicUrl'] ? ['live', 'En línea'] : ($row['isDraft'] ? ['draft', 'Borrador'] : ['draft', 'Sin publicar'])))

<li @class(['ev-row', 'is-past' => $row['isPast']])>
    <span class="ev-dot is-{{ $status[0] }}" aria-hidden="true"></span>
    <div class="ev-row__main">
        <div class="ev-row__head">
            <h3 class="ev-row__name">
                {{ $invitation->title }}
                <span class="sr-only"> · {{ $status[1] }}</span>
            </h3>
            @if($row['dateLabel'])
                <span class="ev-row__date">{{ $row['dateLabel'] }}</span>
            @endif
        </div>

        <p class="ev-meta">
            @if($row['whenLabel'])<span>{{ $row['whenLabel'] }}</span>@endif
            <span>{{ $row['typeLabel'] }}</span>
            @if($row['ownerLabel'])<span class="text-site-ink">{{ $row['ownerLabel'] }}</span>@endif
            @if($row['tierLabel'])<span>Nivel {{ $row['tierLabel'] }}</span>@endif
        </p>

        @include('client.partials.event-numbers', ['row' => $row])

        {{-- Lo que falta, en una línea: un borrador con lo de ejemplo por cambiar, o por qué todavía no se puede abrir --}}
        @if($row['isDraft'])
            <p class="ev-hint">
                <x-phosphor-pencil-simple-line-light aria-hidden="true" />
                Borrador{{ $row['draftLeft'] ? ' · '.$row['draftLeft'].($row['draftLeft'] === 1 ? ' cosa de ejemplo por cambiar' : ' cosas de ejemplo por cambiar') : '' }}
            </p>
        @elseif(! $row['publicUrl'] && ! $row['isPast'])
            <p class="ev-hint">
                <x-phosphor-info-light aria-hidden="true" />
                {{ $row['unavailableReason'] }}
            </p>
        @endif

        @include('client.partials.event-actions', ['row' => $row])
    </div>
</li>
