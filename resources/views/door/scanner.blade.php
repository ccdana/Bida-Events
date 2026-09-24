@extends('door.layout')

{{--
    Lector de pases. Con la cámara del navegador (BarcodeDetector, Chrome en Android) lee el QR sin
    salir de la página; donde no existe (iPhone), la cámara del teléfono abre el enlace del QR y llega
    igual a esta puerta. Siempre queda escribir el código corto a mano.
--}}
@section('title', $invitation->title)

@section('content')
    <section class="door-scan" x-data="doorScanner(@js(url('/entrada/')))">
        <h1 class="door-title">Escanea el pase</h1>
        <p class="door-lead">
            Pide al invitado su pase con código QR. Cuando lo leas, verás si puede pasar y cuántas personas entran con él.
        </p>

        @if(session('door_error'))
            <p class="door-alert is-error" role="alert">{{ session('door_error') }}</p>
        @endif

        <div class="door-camera" x-show="supported" x-cloak>
            <video x-ref="video" playsinline muted x-show="scanning" class="door-camera__video"></video>
            <span class="door-camera__frame" x-show="scanning" aria-hidden="true"></span>
            <button type="button" class="site-btn site-btn--lg door-camera__start" x-show="!scanning" @click="start()">
                <x-phosphor-qr-code aria-hidden="true" />
                Abrir la cámara
            </button>
            <p class="door-hint" x-show="error" x-text="error"></p>
        </div>

        <p class="door-hint" x-show="!supported" x-cloak>
            Abre la cámara de tu teléfono y apunta al QR del pase: el enlace se abre aquí mismo.
        </p>

        <form method="POST" action="{{ route('door.lookup', $doorToken) }}" class="door-code">
            @csrf
            <label for="door-code" class="door-code__label">¿No se puede leer? Escribe el código del pase</label>
            <div class="door-code__row">
                <input id="door-code" name="code" type="text" inputmode="text" autocomplete="off" autocapitalize="characters"
                    maxlength="64" required placeholder="Ej. A1B2C3D4" class="door-code__input">
                <button type="submit" class="site-btn site-btn--lg">Buscar</button>
            </div>
        </form>

        <dl class="door-stats">
            <div>
                <dt>Ingresaron</dt>
                <dd>{{ $stats['arrived'] }}</dd>
            </div>
            <div>
                <dt>Esperados</dt>
                <dd>{{ $stats['expected'] }}</dd>
            </div>
            <div>
                <dt>Pases usados</dt>
                <dd>{{ $stats['passesUsed'] }}</dd>
            </div>
        </dl>
    </section>

    <script>
        /* Lector de QR con la cámara del navegador; si el QR trae un enlace de entrada, lo abre */
        function doorScanner(entryBase) {
            return {
                supported: 'BarcodeDetector' in window && !!navigator.mediaDevices?.getUserMedia,
                scanning: false,
                error: '',
                stream: null,

                async start() {
                    this.error = '';

                    try {
                        const detector = new BarcodeDetector({ formats: ['qr_code'] });
                        this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                        this.$refs.video.srcObject = this.stream;
                        await this.$refs.video.play();
                        this.scanning = true;

                        const tick = async () => {
                            if (!this.scanning) return;

                            const codes = await detector.detect(this.$refs.video).catch(() => []);
                            const value = codes[0]?.rawValue;

                            if (value) {
                                this.stop();
                                this.open(value);
                                return;
                            }

                            requestAnimationFrame(tick);
                        };

                        requestAnimationFrame(tick);
                    } catch (error) {
                        this.error = 'No se pudo abrir la cámara. Revisa el permiso o escribe el código del pase.';
                        this.stop();
                    }
                },

                open(value) {
                    // Un QR de pase lleva el enlace de entrada; un pase viejo, solo el código
                    if (value.startsWith(entryBase)) {
                        window.location.href = value;
                        return;
                    }

                    const input = document.getElementById('door-code');
                    input.value = value;
                    input.form.submit();
                },

                stop() {
                    this.scanning = false;
                    this.stream?.getTracks().forEach((track) => track.stop());
                    this.stream = null;
                },

                destroy() {
                    this.stop();
                },
            };
        }
    </script>
@endsection
