{{--
    La respuesta de quien recibe el libro, en una hoja del cuaderno. Reutiliza la vista del módulo
    (partials/modules/respuesta), con su envío, límites y celebración.
--}}
<article class="nb-page nb-page--lined nb-reply-page" data-nb-page>
    <div class="nb-page__inner" data-nb-nodrag>
        @include('invitations.partials.modules.respuesta', ['data' => $data])
        @include('invitations.partials.aventura.flower', ['kind' => 'girasol', 'class' => 'nb-corner nb-corner--br nb-corner--small'])
    </div>
    <span class="nb-folio"><!--nb-folio--></span>
</article>
