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
<body class="min-h-screen flex items-center justify-center bg-stone-950 text-stone-100" style="font-family: 'Montserrat', sans-serif;">
    <div class="w-full max-w-md px-6">
        <div class="text-center mb-10">
            <h1 class="text-4xl font-serif text-amber-200" style="font-family: 'Playfair Display', serif;">Bida Events</h1>
            <p class="text-stone-400 text-sm mt-2 tracking-widest uppercase">Invitaciones de lujo</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs uppercase tracking-wider text-stone-500 mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full bg-stone-900 border border-stone-700 rounded-xl px-4 py-3 text-stone-100 focus:border-amber-600 focus:ring-amber-600">
                @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs uppercase tracking-wider text-stone-500 mb-2">Contraseña</label>
                <input type="password" name="password" required
                    class="w-full bg-stone-900 border border-stone-700 rounded-xl px-4 py-3 text-stone-100 focus:border-amber-600 focus:ring-amber-600">
            </div>
            <label class="flex items-center gap-2 text-sm text-stone-400">
                <input type="checkbox" name="remember" class="rounded border-stone-600">
                Recordarme
            </label>
            <button type="submit" class="w-full py-3 bg-amber-700 hover:bg-amber-600 text-white rounded-xl transition font-medium tracking-wide">
                Entrar
            </button>
        </form>

        <p class="text-center text-xs text-stone-600 mt-8">
            Demo: admin@test.com / password · cliente@test.com / password
        </p>
    </div>
</body>
</html>
