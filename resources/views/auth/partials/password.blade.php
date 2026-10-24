{{--
    Campo de contraseña con «mostrar» y aviso de Bloq Mayús. El formulario que lo incluye declara
    showPassword y capsLock en su x-data. Recibe $index (orden de entrada), y opcionales $label,
    $autocomplete (current-password o new-password) y $hint (una línea bajo la línea del campo).
--}}
@php($autocomplete ??= 'current-password')
<div class="site-enter grid gap-1" style="--enter-index: {{ $index }}">
    <label for="password" class="site-label">{{ $label ?? __('Contraseña') }}</label>
    <div class="site-field">
        <x-phosphor-lock-key-light class="site-field__icon" aria-hidden="true" />
        <input id="password" type="password" :type="showPassword ? 'text' : 'password'" name="password" required
            autocomplete="{{ $autocomplete }}" @if($autocomplete === 'new-password') minlength="8" maxlength="72" @endif
            @keydown="capsLock = $event.getModifierState(@js('CapsLock'))" @keyup="capsLock = $event.getModifierState(@js('CapsLock'))"
            @class(['site-input pr-10', 'is-invalid' => $errors->has('password')])
            @if($errors->has('password')) aria-invalid="true" aria-describedby="password-error"
            @elseif(! empty($hint)) aria-describedby="password-hint" @endif>
        <button type="button" class="absolute inset-y-0 right-0 grid w-10 place-items-center text-site-muted transition-colors hover:text-site-ink"
            @click="showPassword = !showPassword" aria-controls="password"
            :aria-label="showPassword ? @js(__('Ocultar contraseña')) : @js(__('Mostrar contraseña'))" aria-label="{{ __('Mostrar contraseña') }}">
            <x-phosphor-eye-light class="site-swap is-on" x-bind:class="{ 'is-on': !showPassword }" aria-hidden="true" />
            <x-phosphor-eye-slash-light class="site-swap" x-bind:class="{ 'is-on': showPassword }" aria-hidden="true" />
        </button>
    </div>
    <p class="flex items-center gap-2 text-sm text-site-muted" x-show="capsLock" x-cloak x-transition.opacity aria-live="polite">
        <x-phosphor-arrow-fat-line-up class="size-4 shrink-0" aria-hidden="true" />
        {{ __('Bloq Mayús está activado') }}
    </p>
    @error('password')
        <p id="password-error" class="site-enter text-sm text-site-danger">{{ $message }}</p>
    @else
        @if(! empty($hint))
            <p id="password-hint" class="text-sm text-site-muted">{{ $hint }}</p>
        @endif
    @enderror
</div>