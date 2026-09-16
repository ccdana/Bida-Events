{{-- Paginación de los paneles: anterior, posición y siguiente. Recibe $paginator. --}}
@if($paginator->hasPages())
    <nav class="adm-pagination" aria-label="Paginación">
        @if($paginator->onFirstPage())
            <span class="admin-link-button is-disabled" aria-disabled="true">
                <x-phosphor-arrow-left aria-hidden="true" />
                Anterior
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="admin-link-button">
                <x-phosphor-arrow-left aria-hidden="true" />
                Anterior
            </a>
        @endif

        <p class="adm-pagination__status" aria-live="polite">
            Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}
            <span class="adm-pagination__total">{{ $paginator->total() }} en total</span>
        </p>

        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="admin-link-button">
                Siguiente
                <x-phosphor-arrow-right aria-hidden="true" />
            </a>
        @else
            <span class="admin-link-button is-disabled" aria-disabled="true">
                Siguiente
                <x-phosphor-arrow-right aria-hidden="true" />
            </span>
        @endif
    </nav>
@endif
