{{-- Tema claro u oscuro compartido por el sitio y los paneles. Sin preferencia guardada sigue al sistema. --}}
<script>
    (function () {
        try {
            var theme = localStorage.getItem('bida-theme') || localStorage.getItem('admin-theme');
            if (theme === 'light' || theme === 'dark') {
                document.documentElement.dataset.theme = theme;
            }
        } catch (error) {}
    })();

    function toggleTheme() {
        var root = document.documentElement;
        var current = root.dataset.theme || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        var next = current === 'dark' ? 'light' : 'dark';

        // Los colores se animan solo durante el cambio
        root.classList.add('theme-changing');
        root.dataset.theme = next;
        try { localStorage.setItem('bida-theme', next); } catch (error) {}
        window.setTimeout(function () { root.classList.remove('theme-changing'); }, 450);
    }
</script>
