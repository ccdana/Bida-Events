{{-- Preguntas frecuentes. Recibe $faqs: lista de [pregunta, respuesta]. --}}
<section id="preguntas" class="scroll-mt-20 border-t border-site-line">
    <div class="mx-auto max-w-3xl px-5 py-20 lg:py-28">
        <h2 class="text-3xl font-semibold leading-[1.1] tracking-tight md:text-5xl" data-reveal>Preguntas frecuentes</h2>

        <div class="mt-12 divide-y divide-site-line border-y border-site-line" data-reveal>
            @foreach($faqs as [$question, $answer])
                <details class="site-faq group" name="preguntas">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-6 py-5 text-lg font-medium transition-colors hover:text-site-accent [&::-webkit-details-marker]:hidden">
                        {{ $question }}
                        <span class="grid size-8 shrink-0 place-items-center rounded-full border border-site-line transition-[rotate,background-color] duration-500 group-open:rotate-45 group-open:bg-site-tint">
                            <x-phosphor-plus class="size-4" aria-hidden="true" />
                        </span>
                    </summary>
                    <p class="max-w-[60ch] pb-6 leading-relaxed text-site-muted">{{ $answer }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>
