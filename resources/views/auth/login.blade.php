<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión — Bida Events</title>
    @vite(['resources/css/app.css'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
</head>
</body>
<body class="min-h-screen font-sans text-stone-900" style="font-family: 'Montserrat', sans-serif;">
    <div class="min-h-screen flex flex-col md:flex-row auth-split">
        <!-- Left panel -->
        <div class="relative w-full md:w-5/12 bg-stone-950 text-stone-50 overflow-hidden auth-left">
            <div class="p-8 md:p-12">
                <h1 class="text-3xl md:text-4xl font-serif text-amber-200" style="font-family: 'Playfair Display', serif;">Bida Events</h1>
                <p class="text-stone-400 text-xs mt-2 tracking-widest uppercase">Invitaciones de lujo</p>
            </div>

            <div class="absolute left-0 bottom-0 p-8 md:p-12 max-w-[60%] auth-caption">
                <p class="font-serif text-2xl md:text-3xl opacity-90 text-stone-200">Cada evento, <span class="italic text-3xl md:text-4xl text-stone-100">una obra única.</span></p>
                <p class="text-stone-500 text-sm mt-4 max-w-xs">Crea, personaliza y gestiona invitaciones digitales de alta calidad para eventos que merecen ser recordados.</p>
            </div>
        </div>

        <!-- Right panel -->
        <div class="w-full md:w-7/12 bg-[#f3efe8] flex items-center justify-center px-6 py-12 auth-right">
            <div class="w-full max-w-lg">
                <p class="text-xs text-stone-500 uppercase tracking-widest mb-4">Panel administrativo</p>
                <h1 class="text-4xl font-serif mb-6" style="font-family: 'Playfair Display', serif;">Iniciar sesión</h1>

                <form method="POST" action="{{ route('login') }}" class="space-y-6 text-stone-700">
                    @csrf
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-stone-500 mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full bg-transparent border-b border-stone-300 px-0 py-3 text-stone-800 focus:outline-none focus:border-stone-500">
                        @error('email')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs uppercase tracking-wider text-stone-500 mb-2">Contraseña</label>
                        <div class="relative">
                            <input id="password" type="password" name="password" required
                                class="w-full bg-transparent border-b border-stone-300 px-0 py-3 text-stone-800 focus:outline-none focus:border-stone-500">
                            <!-- eye icon (elegante SVG) -->
                            <button type="button" class="absolute right-0 text-stone-400 eye-toggle" aria-hidden="true" tabindex="-1" data-target="#password">
                                <!-- eye (visible) -->
                                <svg class="eye-visible" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M2.03 12.29C3.5 7.5 7.5 4 12 4c4.52 0 8.52 3.5 9.97 8.29a1 1 0 010 .42C20.52 18.5 16.52 22 12 22c-4.5 0-8.5-3.5-9.97-8.29a1 1 0 010-.42z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <!-- eye-off (hidden by default) - improved path to avoid clipping -->
                                <svg class="eye-hidden" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:none">
                                    <path d="M2.03 12.29C3.5 7.5 7.5 4 12 4c4.52 0 8.52 3.5 9.97 8.29a1 1 0 010 .42C20.52 18.5 16.52 22 12 22c-4.5 0-8.5-3.5-9.97-8.29a1 1 0 010-.42z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                    <path d="M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                        @error('password')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-center justify-between text-sm text-stone-600">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="remember" class="custom-checkbox">
                            Recordarme
                        </label>
                        {{-- Enlace deshabilitado: la ruta `password.request` no está definida actualmente
                            <a href="{{ route('password.request') }}" class="text-stone-600 hover:underline">¿Olvidaste tu contraseña?</a>
                        --}}
                    </div>

                    <button type="submit" class="w-full py-3 bg-stone-900 text-white font-medium tracking-wide flex items-center justify-center gap-2">
                        Entrar <span class="ml-2">→</span>
                    </button>
                </form>

                <p class="text-xs text-stone-500 mt-6">Demo — admin@test.com / password · cliente@test.com / password</p>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var toggle = document.querySelector('.eye-toggle');
            if (!toggle) return;
            var targetSelector = toggle.getAttribute('data-target');
            var input = document.querySelector(targetSelector);
            var eyeVisible = toggle.querySelector('.eye-visible');
            var eyeHidden = toggle.querySelector('.eye-hidden');
            // initial visibility
            if (eyeVisible) eyeVisible.style.display = 'block';
            if (eyeHidden) eyeHidden.style.display = 'none';
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                if (!input) return;
                if (input.type === 'password') {
                    input.type = 'text';
                    if (eyeVisible) eyeVisible.style.display = 'none';
                    if (eyeHidden) eyeHidden.style.display = 'block';
                } else {
                    input.type = 'password';
                    if (eyeVisible) eyeVisible.style.display = 'block';
                    if (eyeHidden) eyeHidden.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
