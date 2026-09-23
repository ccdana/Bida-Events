<script>
    /**
     * Copiar al portapapeles desde el panel del cliente. La API del navegador solo existe en
     * páginas seguras (https), así que en el resto se copia con un campo temporal: el portal
     * se usa sobre todo desde el celular para reenviar enlaces por WhatsApp.
     */
    window.bidaCopy = async function (text) {
        try {
            await navigator.clipboard.writeText(text);

            return true;
        } catch (error) {
            const field = document.createElement('textarea');
            field.value = text;
            field.setAttribute('readonly', '');
            field.style.position = 'fixed';
            field.style.opacity = '0';
            document.body.appendChild(field);
            field.select();
            const copied = document.execCommand('copy');
            field.remove();

            return copied;
        }
    };
</script>
