{{--
    Una esfera de «A la misma hora»: la caja de latón, la esfera de marfil con su guilloché, las marcas de
    los minutos y las horas, los números romanos y las agujas. Las agujas empiezan en --h0/--m0 y, si la
    apertura les da cuerda, giran hasta --h1/--m1. Parámetros: class, name (va escrito en la esfera),
    style (las variables de las agujas).
--}}
<div class="rl-watch {{ $class ?? '' }}" style="{{ $style ?? '' }}">
    <span class="rl-watch__dial" aria-hidden="true">
        <span class="rl-watch__numeral rl-watch__numeral--12">XII</span>
        <span class="rl-watch__numeral rl-watch__numeral--3">III</span>
        <span class="rl-watch__numeral rl-watch__numeral--6">VI</span>
        <span class="rl-watch__numeral rl-watch__numeral--9">IX</span>
    </span>
    @if(!empty($name))
        <span class="rl-watch__name">{{ $name }}</span>
    @endif
    <span class="rl-hand rl-hand--hour" aria-hidden="true"></span>
    <span class="rl-hand rl-hand--minute" aria-hidden="true"></span>
    <span class="rl-hand rl-hand--second" aria-hidden="true"></span>
    <span class="rl-watch__cap" aria-hidden="true"></span>
    <span class="rl-watch__crown" aria-hidden="true"></span>
</div>
