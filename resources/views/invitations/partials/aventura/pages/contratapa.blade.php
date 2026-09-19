{{-- Contratapa de cuero con una margarita y la fecha. --}}
<article class="nb-page nb-cover nb-cover--back" data-nb-page data-density="hard">
    <div class="nb-cover__inner">
        <span class="nb-cover__stitch" aria-hidden="true"></span>
        @include('invitations.partials.aventura.flower', ['kind' => 'margarita', 'class' => 'nb-cover__flower nb-cover__flower--center'])
        <p class="nb-cover__end">Fin… o apenas el comienzo</p>
        <p class="nb-cover__date">{{ \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F \d\e Y')) }}</p>
    </div>
</article>
