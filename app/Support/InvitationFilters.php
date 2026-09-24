<?php

namespace App\Support;

use App\Models\EventType;
use App\Modules\Module;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Filtros de la lista de invitaciones de los paneles (administrador y revendedor), los mismos en la
 * barra lateral y en la consulta. Todo viaja en la URL (?q=&tipo=&estado=&origen=&orden=), así un
 * filtro se comparte o se recarga sin perderse, y cada opción muestra cuántas invitaciones tiene.
 *
 * - tipo: «invitation» o «card» (el producto entero) o el slug de un tipo de evento.
 * - estado: publicadas, sin publicar, próximas (el evento todavía no pasó) o ya pasaron.
 * - origen (solo administrador): del equipo o armadas por revendedores.
 * - orden: creadas recientemente o por fecha del evento.
 */
final class InvitationFilters
{
    public const STATES = [
        'activas' => 'Publicadas',
        'inactivas' => 'Sin publicar',
        'proximas' => 'Próximas',
        'pasadas' => 'Ya pasaron',
    ];

    public const ORIGINS = [
        'equipo' => 'Del equipo',
        'revendedores' => 'De revendedores',
    ];

    public const SORTS = [
        'recientes' => 'Creadas hace poco',
        'fecha' => 'Por fecha del evento',
    ];

    public const KINDS = [
        Module::KIND_INVITATION => 'Invitaciones',
        Module::KIND_CARD => 'Tarjetas',
    ];

    /**
     * Lo que pidió la URL, limpio: un valor desconocido se ignora (no esconde nada).
     *
     * @return array{q: string, tipo: string, estado: string, origen: string, orden: string}
     */
    public static function fromRequest(Request $request, bool $withOrigin = true): array
    {
        $type = trim((string) $request->query('tipo', ''));

        if ($type !== '' && ! isset(self::KINDS[$type]) && ! EventType::where('slug', $type)->exists()) {
            $type = '';
        }

        $pick = fn (string $key, array $allowed) => array_key_exists((string) $request->query($key), $allowed) ? (string) $request->query($key) : '';

        return [
            'q' => mb_substr(trim((string) $request->query('q', '')), 0, 100),
            'tipo' => $type,
            'estado' => $pick('estado', self::STATES),
            'origen' => $withOrigin ? $pick('origen', self::ORIGINS) : '',
            'orden' => $pick('orden', self::SORTS),
        ];
    }

    /** Aplica los filtros a una consulta de invitaciones (el alcance, admin o revendedor, ya viene puesto). */
    public static function apply(Builder $query, array $filters, bool $searchClients = true): Builder
    {
        $type = $filters['tipo'] ?? '';
        $search = $filters['q'] ?? '';

        return $query
            ->when(isset(self::KINDS[$type]), fn (Builder $q) => $q->whereHas('eventType', fn ($eventType) => $eventType->where('kind', $type)))
            ->when($type !== '' && ! isset(self::KINDS[$type]), fn (Builder $q) => $q->whereHas('eventType', fn ($eventType) => $eventType->where('slug', $type)))
            ->when($search !== '', fn (Builder $q) => $q->where(fn ($where) => $where
                ->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->when($searchClients, fn ($inner) => $inner->orWhereHas('user', fn ($user) => $user
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")))))
            ->when(($filters['estado'] ?? '') !== '', fn (Builder $q) => self::applyState($q, $filters['estado']))
            ->when(($filters['origen'] ?? '') === 'equipo', fn (Builder $q) => $q->whereNull('reseller_id'))
            ->when(($filters['origen'] ?? '') === 'revendedores', fn (Builder $q) => $q->whereNotNull('reseller_id'))
            ->when(($filters['orden'] ?? '') === 'fecha', fn (Builder $q) => $q->orderBy('event_date'), fn (Builder $q) => $q->latest());
    }

    /**
     * Los grupos de la barra lateral, cada opción con su enlace y cuántas invitaciones tiene dentro
     * del alcance (sin contar los demás filtros, para que el número no cambie al elegir otro).
     *
     * @return array<string, array{label: string, options: list<array{value: string, label: string, count: int|null, url: string, active: bool}>}>
     */
    public static function sidebar(Builder $scope, array $filters, string $route, bool $withOrigin = true): array
    {
        $url = fn (string $key, string $value) => route($route, array_filter([...$filters, $key => $value]));

        $byType = (clone $scope)->reorder()->select('event_type_id', DB::raw('count(*) as total'))->groupBy('event_type_id')->pluck('total', 'event_type_id');
        $types = EventType::whereIn('id', $byType->keys())->orderBy('name')->get(['id', 'slug', 'name', 'kind']);
        $total = (int) $byType->sum();

        $kindCount = fn (string $kind) => (int) $types->where('kind', $kind)->sum(fn (EventType $type) => $byType[$type->id] ?? 0);

        // Estados y origen en una sola consulta: el panel no crece en consultas con cada filtro
        $now = now()->toDateTimeString();
        $counts = (clone $scope)->reorder()->toBase()
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END), 0) AS activas")
            ->selectRaw("COALESCE(SUM(CASE WHEN status <> 'active' THEN 1 ELSE 0 END), 0) AS inactivas")
            ->selectRaw('COALESCE(SUM(CASE WHEN event_date >= ? THEN 1 ELSE 0 END), 0) AS proximas', [$now])
            ->selectRaw('COALESCE(SUM(CASE WHEN event_date < ? THEN 1 ELSE 0 END), 0) AS pasadas', [$now])
            ->selectRaw('COALESCE(SUM(CASE WHEN reseller_id IS NULL THEN 1 ELSE 0 END), 0) AS equipo')
            ->selectRaw('COALESCE(SUM(CASE WHEN reseller_id IS NOT NULL THEN 1 ELSE 0 END), 0) AS revendedores')
            ->first();
        $stateCount = fn (string $state) => (int) ($counts->{$state} ?? 0);

        $option = fn (string $key, string $value, string $label, ?int $count) => [
            'value' => $value,
            'label' => $label,
            'count' => $count,
            'url' => $url($key, $value),
            'active' => ($filters[$key] ?? '') === $value,
        ];

        $groups = [
            'tipo' => [
                'label' => 'Qué es',
                'options' => [
                    $option('tipo', '', 'Todo', $total),
                    ...collect(self::KINDS)
                        ->map(fn (string $label, string $kind) => $option('tipo', $kind, $label, $kindCount($kind)))
                        ->filter(fn (array $item) => $item['count'] > 0)
                        ->values()
                        ->all(),
                    ...$types->map(fn (EventType $type) => $option('tipo', $type->slug, $type->name, (int) ($byType[$type->id] ?? 0)))->all(),
                ],
            ],
            'estado' => [
                'label' => 'Estado',
                'options' => [
                    $option('estado', '', 'Cualquiera', null),
                    ...collect(self::STATES)->map(fn (string $label, string $state) => $option('estado', $state, $label, $stateCount($state)))->values()->all(),
                ],
            ],
        ];

        if ($withOrigin) {
            $groups['origen'] = [
                'label' => 'Quién la armó',
                'options' => [
                    $option('origen', '', 'Todos', null),
                    $option('origen', 'equipo', self::ORIGINS['equipo'], (int) $counts->equipo),
                    $option('origen', 'revendedores', self::ORIGINS['revendedores'], (int) $counts->revendedores),
                ],
            ];
        }

        $groups['orden'] = [
            'label' => 'Ordenar',
            'options' => [
                $option('orden', '', self::SORTS['recientes'], null),
                $option('orden', 'fecha', self::SORTS['fecha'], null),
            ],
        ];

        return $groups;
    }

    /** ¿Hay algún filtro puesto? (para ofrecer «Quitar filtros»). */
    public static function isFiltered(array $filters): bool
    {
        return collect($filters)->except('orden')->filter()->isNotEmpty();
    }

    private static function applyState(Builder $query, string $state): Builder
    {
        return match ($state) {
            'activas' => $query->where('status', 'active'),
            'inactivas' => $query->where('status', '!=', 'active'),
            'proximas' => $query->where('event_date', '>=', now()),
            'pasadas' => $query->where('event_date', '<', now()),
            default => $query,
        };
    }
}
