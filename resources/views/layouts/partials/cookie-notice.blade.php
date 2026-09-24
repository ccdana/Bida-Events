{{--
    Aviso de cookies del sitio público. Solo usamos cookies necesarias y la de campaña (ver
    App\Support\LegalPages, «cookies»), así que es un aviso informativo con un «Entendido» que se
    recuerda en el navegador. Sin JavaScript no se muestra. Estilos: site.css («Aviso de cookies»).
--}}
<div class="site-cookies" data-cookie-notice hidden role="region" aria-label="Aviso de cookies">
    <p>
        Usamos solo las cookies necesarias para que el sitio funcione y una para saber desde qué anuncio llegaste. Nada de publicidad.
        <a href="{{ route('legal', 'cookies') }}">Ver la política de cookies</a>
    </p>
    <button type="button" class="site-btn site-cookies__ok" data-cookie-accept>Entendido</button>
</div>
<script>
    (function () {
        const key = 'bida-cookies-ok';
        const box = document.querySelector('[data-cookie-notice]');
        let seen = false;

        try {
            seen = localStorage.getItem(key) === '1';
        } catch (error) {
            // Sin almacenamiento (modo privado estricto): el aviso se muestra en cada visita
        }

        if (seen || !box) {
            return;
        }

        box.hidden = false;
        document.documentElement.classList.add('has-cookie-notice');

        box.querySelector('[data-cookie-accept]').addEventListener('click', function () {
            try {
                localStorage.setItem(key, '1');
            } catch (error) {
                // Se cierra igual; volverá a aparecer en la próxima visita
            }

            box.hidden = true;
            document.documentElement.classList.remove('has-cookie-notice');
        });
    })();
</script>
