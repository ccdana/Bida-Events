<div class="invitation-lottie-framed {{ $wrapperClass ?? '' }}">
    <div class="invitation-lottie-framed__rule" aria-hidden="true"></div>
    @include('invitations.partials.lottie-icon', [
        'name' => $name,
        'class' => trim(($class ?? '') . ' invitation-lottie-framed__icon'),
    ])
    <div class="invitation-lottie-framed__rule" aria-hidden="true"></div>
</div>
