{{-- Cuenta con el correo sin confirmar: se recuerda en cada página del panel, con el reenvío a mano --}}
@php($noticeUser = auth()->user())
@if($noticeUser && filled($noticeUser->email) && ! $noticeUser->hasVerifiedEmail())
    <div class="site-enter mb-6 flex flex-wrap items-start gap-x-3 gap-y-2 rounded-[12px] border border-site-line bg-site-tint px-4 py-3 text-sm" role="status">
        <x-phosphor-envelope-simple class="mt-0.5 size-5 shrink-0 text-site-accent" aria-hidden="true" />
        <p class="min-w-0 flex-1 leading-relaxed">
            @if(session('status'))
                {{ session('status') }}
            @else
                Confirma tu correo: te enviamos un enlace a <span class="font-medium">{{ $noticeUser->email }}</span>.
            @endif
        </p>
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="font-medium underline underline-offset-4 transition-colors hover:text-site-accent">Enviar otro enlace</button>
        </form>
    </div>
@endif
