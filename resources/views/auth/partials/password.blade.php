{{--
    Campo de contraseña con «mostrar» y aviso de Bloq Mayús. El formulario que lo incluye declara
    showPassword y capsLock en su x-data. Recibe $index (orden de entrada), y opcionales $label,
    $autocomplete (current-password o new-password), $hint y $forgot (enlace «¿La olvidaste?»).
--}}
@php($autocomplete ??= 'current-password')
<div class="site-enter grid gap-2" style="--enter-index: {{ $index }}">
    <div class="flex items-baseline justify-between gap-4">
        <label for="password" class="text-[0.95rem] font-medium">{{ $label ?? 'Contraseña' }}</label>
        @if($forgot ?? false)
            <a href="{{ route('password.request') }}" class="text-sm text-site-muted underline underline-offset-4 transition-colors hover:text-site-ink">¿La olvidaste?</a>
        @endif
    </div>
    <div class="site-field">
        <x-phosphor-lock-key class="site-field__icon" aria-hidden="true" />
        <input id="password" type="password" :type="showPassword ? 'text' : 'password'" name="password" required
            autocomplete="{{ $autocomplete }}" @if($autocomplete === 'new-password') minlength="8" @endif
            @keydown="capsLock = $event.getModifierState('CapsLock')" @keyup="capsLock = $event.getModifierState('CapsLock')"
            @class(['site-input pr-12', 'is-invalid' => $errors->has('password')])
            @if($errors->has('password')) aria-invalid="true" aria-describedby="password-error"
            @elseif(! empty($hint)) aria-describedby="password-hint" @endif>
        <button type="button" class="absolute inset-y-0 right-0 grid w-12 place-items-center rounded-r-[12px] text-site-muted transition-colors hover:text-site-ink"
            @click="showPassword = !showPassword" aria-controls="password"
            :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'" aria-label="Mostrar contraseña">
            <x-phosphor-eye class="site-swap is-on" x-bind:class="{ 'is-on': !showPassword }" aria-hidden="true" />
            <x-phosphor-eye-slash class="site-swap" x-bind:class="{ 'is-on': showPassword }" aria-hidden="true" />
        </button>
    </div>
    <p class="flex items-center gap-2 text-sm text-site-muted" x-show="capsLock" x-cloak x-transition.opacity aria-live="polite">
        <x-phosphor-arrow-fat-line-up class="size-4 shrink-0" aria-hidden="true" />
        Bloq Mayús está activado
    </p>
    @error('password')
        <p id="password-error" class="site-enter text-sm text-site-danger">{{ $message }}</p>
    @else
        @if(! empty($hint))
            <p id="password-hint" class="text-sm text-site-muted">{{ $hint }}</p>
        @endif
    @enderror
</div>
