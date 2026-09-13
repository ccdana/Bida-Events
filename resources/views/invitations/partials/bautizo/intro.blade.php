{{-- Apertura del bautizo: las nubes se abren solas y aparece la invitación; no bloquea la página --}}
<div class="inv-bautizo-intro" aria-hidden="true">
    @include('invitations.partials.bautizo.cloud', ['class' => 'inv-bautizo-intro__cloud inv-bautizo-intro__cloud--left'])
    @include('invitations.partials.bautizo.cloud', ['class' => 'inv-bautizo-intro__cloud inv-bautizo-intro__cloud--right'])
    @include('invitations.partials.bautizo.cloud', ['class' => 'inv-bautizo-intro__cloud inv-bautizo-intro__cloud--low'])

    <div class="inv-bautizo-intro__center">
        @include('invitations.partials.bautizo.dove', ['class' => 'inv-bautizo-intro__dove'])
        <p class="inv-bautizo-intro__name">{{ $page->displayName }}</p>
    </div>
</div>
