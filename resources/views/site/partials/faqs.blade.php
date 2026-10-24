{{-- Preguntas frecuentes. Recibe $faqs: lista de [pregunta, respuesta]. --}}
<section id="preguntas" class="scroll-mt-20 border-t border-site-line">
    <div class="mx-auto max-w-3xl px-5 py-10 lg:py-16">
        <h2 class="site-display site-display--md" data-reveal>{{ __('Preguntas frecuentes') }}</h2>

        <div class="mt-6 divide-y divide-site-line border-y border-site-line lg:mt-8" data-reveal>
            @foreach($faqs as [$question, $answer])
                <details class="site-faq group" name="preguntas">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-6 py-4 text-lg font-medium transition-colors hover:text-site-accent [&::-webkit-details-marker]:hidden">
                        {{ $question }}
                        <x-phosphor-plus class="site-faq__sign size-5 shrink-0 text-site-accent transition-[rotate] duration-500 group-open:rotate-45" aria-hidden="true" />
                    </summary>
                    <p class="max-w-[60ch] pb-5 leading-relaxed text-site-muted">{{ $answer }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>
