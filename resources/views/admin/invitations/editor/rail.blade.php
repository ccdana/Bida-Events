{{--
    Computadora: un riel angosto de íconos a la izquierda. «Partes» pliega o muestra la lista de partes (editor/modules);
    «Datos» y «Colores» abren su formulario en el panel de la derecha. En el celular lo mismo está en la barra de abajo
    (editor/preview), así que el riel no se dibuja.
--}}
<nav class="ed-rail" aria-label="Herramientas del editor">
    <button type="button" class="ed-rail__btn" :class="{ 'is-on': partsOpen }" @click="togglePartsColumn()"
        :aria-pressed="partsOpen.toString()" aria-label="Partes de la invitación" title="Partes">
        <x-phosphor-squares-four-light aria-hidden="true" />
    </button>
    <button type="button" class="ed-rail__btn" :class="{ 'is-on': panelMode === 'full' && activeTab === 'general' }" @click="toggleFull('general')"
        aria-label="Datos del evento" title="Datos del evento">
        <x-phosphor-calendar-blank-light aria-hidden="true" />
    </button>
    <button type="button" class="ed-rail__btn" :class="{ 'is-on': panelMode === 'full' && activeTab === 'estetica' }" @click="toggleFull('estetica')"
        aria-label="Colores y letras" title="Colores y letras">
        <x-phosphor-palette-light aria-hidden="true" />
    </button>
</nav>
