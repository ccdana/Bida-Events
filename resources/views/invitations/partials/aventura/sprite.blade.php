{{--
    Dibujos del «Libro de aventuras», definidos una sola vez y reutilizados con
    <svg><use href="#nb-girasol"/></svg> (ver partials/aventura/flower).
    Girasol, margarita amarilla, ramita con capullos, globo y sello de corazón. Todo ilustración propia.
--}}
<svg class="nb-sprite" width="0" height="0" aria-hidden="true" focusable="false">
    <defs>
        {{-- Girasol: dos coronas de pétalos y el centro con semillas --}}
        <symbol id="nb-girasol" viewBox="-50 -50 100 100">
            <g fill="#E0A10E">
                @for($i = 0; $i < 16; $i++)
                    <ellipse cx="0" cy="-31" rx="7.5" ry="17" transform="rotate({{ $i * 22.5 + 11.25 }})"/>
                @endfor
            </g>
            <g fill="#F6C933" stroke="#D6960B" stroke-width="0.8">
                @for($i = 0; $i < 16; $i++)
                    <ellipse cx="0" cy="-29" rx="6.5" ry="16" transform="rotate({{ $i * 22.5 }})"/>
                @endfor
            </g>
            <circle r="17" fill="#6B3E1A"/>
            <circle r="13" fill="#57300F"/>
            <g fill="#8A5527">
                @foreach([[-6, -6], [0, -8], [6, -6], [-8, 0], [0, 0], [8, 0], [-6, 6], [0, 8], [6, 6], [-3, -3], [3, 3], [3, -3], [-3, 3]] as [$x, $y])
                    <circle cx="{{ $x }}" cy="{{ $y }}" r="1.4"/>
                @endforeach
            </g>
        </symbol>

        {{-- Margarita amarilla: pétalos redondeados y centro naranja --}}
        <symbol id="nb-margarita" viewBox="-50 -50 100 100">
            <g fill="#F8D548" stroke="#D9A514" stroke-width="1">
                @for($i = 0; $i < 12; $i++)
                    <ellipse cx="0" cy="-26" rx="9" ry="20" transform="rotate({{ $i * 30 }})"/>
                @endfor
            </g>
            <circle r="12" fill="#E58A1F"/>
            <circle r="12" fill="none" stroke="#B8650F" stroke-width="1.5" stroke-dasharray="2 3"/>
        </symbol>

        {{-- Ramita con hojas y tres capullos amarillos --}}
        <symbol id="nb-ramita" viewBox="0 0 120 60">
            <path d="M4 52 C30 44 58 30 112 8" fill="none" stroke="#6F8F3A" stroke-width="2.4" stroke-linecap="round"/>
            <g fill="#7FA046">
                <path d="M30 44 C26 34 34 28 40 30 C40 38 36 42 30 44Z"/>
                <path d="M34 42 C42 48 50 46 52 40 C46 36 38 38 34 42Z"/>
                <path d="M66 26 C62 16 70 10 76 12 C76 20 72 24 66 26Z"/>
                <path d="M70 24 C78 30 86 28 88 22 C82 18 74 20 70 24Z"/>
            </g>
            <g fill="#F4C430" stroke="#CF9510" stroke-width="1">
                <circle cx="112" cy="8" r="6"/>
                <circle cx="54" cy="34" r="4.5"/>
                <circle cx="92" cy="18" r="5"/>
            </g>
        </symbol>

        {{-- Globo de aire (la línea punteada de la historia lo lleva de página en página) --}}
        <symbol id="nb-globo" viewBox="0 0 40 60">
            <path d="M20 2 C31 2 38 10 38 20 C38 32 26 40 20 42 C14 40 2 32 2 20 C2 10 9 2 20 2Z" fill="#F2C230" stroke="#B98710" stroke-width="1.4"/>
            <path d="M13 8 C9 12 8 17 9 21" fill="none" stroke="#FFF3C4" stroke-width="2.4" stroke-linecap="round"/>
            <path d="M18 42 L22 42 L20 46Z" fill="#B98710"/>
            <path d="M20 46 C16 50 24 53 20 58" fill="none" stroke="#6B4A2A" stroke-width="1.2"/>
        </symbol>

        {{-- Corazón con borde de sello --}}
        <symbol id="nb-corazon" viewBox="0 0 100 92">
            <path d="M50 88 C20 66 4 48 4 30 C4 16 15 6 28 6 C38 6 46 12 50 20 C54 12 62 6 72 6 C85 6 96 16 96 30 C96 48 80 66 50 88Z"/>
        </symbol>
    </defs>
</svg>
