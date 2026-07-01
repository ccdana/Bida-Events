@php
    $heroTitle = $bienvenida['nombre_quinceanera'] ?? $invitation->title;
    $heroEyebrow = $bienvenida['subtitulo'] ?? 'Mis XV Años';
    $heroMessage = $bienvenida['mensaje'] ?? '';
    $heroDate = $bienvenida['fecha_texto'] ?? $invitation->event_date->format('d \\d\\e F, Y');
    $showGuestCard = isset($guest) && $guest;
@endphp

<header id="inicio" class="hero-premium relative min-h-[100svh] overflow-hidden">
    <div class="hero-premium__backdrop {{ $hasHeroImage ? '' : 'hero-premium__backdrop--fallback' }}" aria-hidden="true">
        @if($hasHeroImage)
            <img
                src="{{ $heroImage }}"
                alt=""
                class="hero-premium__image"
                loading="eager"
                fetchpriority="high"
                decoding="async"
            >
        @endif
    </div>

    <div class="hero-premium__veil" aria-hidden="true"></div>
    <div class="hero-premium__glow hero-premium__glow--left" aria-hidden="true"></div>
    <div class="hero-premium__glow hero-premium__glow--right" aria-hidden="true"></div>

    <div class="hero-premium__particles" aria-hidden="true">
        <span></span><span></span><span></span><span></span><span></span>
    </div>

    <div class="hero-premium__content mx-auto flex min-h-[100svh] w-full max-w-6xl flex-col items-center justify-center px-6 py-16 text-center sm:px-8 lg:px-12">
        <p class="hero-premium__eyebrow animate-fade-up">
            {{ $heroEyebrow }}
        </p>

        <h1 class="hero-premium__title animate-fade-up animate-fade-up-delay-1">
            {{ $heroTitle }}
        </h1>

        <div class="hero-premium__rule animate-fade-up animate-fade-up-delay-2" aria-hidden="true"></div>

        @if(!empty($heroMessage))
            <p class="hero-premium__message animate-fade-up animate-fade-up-delay-2">
                {{ $heroMessage }}
            </p>
        @endif

        <p class="hero-premium__date animate-fade-up animate-fade-up-delay-3">
            {{ $heroDate }}
        </p>

        @if($showGuestCard)
            <div class="hero-premium__guest animate-fade-up animate-fade-up-delay-4">
                <p class="hero-premium__guest-label">Invitación personal</p>
                <p class="hero-premium__guest-name">{{ $guest->name }}</p>
                @if($guest->status === 'pending')
                    <p class="hero-premium__guest-meta">
                        Tienes <strong>{{ $guest->passes_allocated }}</strong>
                        {{ $guest->passes_allocated === 1 ? 'pase disponible' : 'pases disponibles' }}
                    </p>
                @endif
            </div>
        @endif
    </div>

    <div class="hero-premium__scroll {{ $hasHeroImage ? 'text-white/60' : 'text-primary/50' }}" aria-hidden="true">
        @include('invitations.partials.icon', ['name' => 'chevron-down', 'class' => 'w-6 h-6', 'animated' => false])
    </div>
</header>
