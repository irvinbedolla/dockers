<script src="{{ asset('assets/js/mapa.js') }}"></script>
<script>
(function () {
    // Los id de los <path> del mapa (mapa.js) no llevan acentos y usan
    // camelCase para Lázaro Cárdenas; aquí se traducen al nombre de
    // 'delegacion' tal como está en la base de datos.
    const SEDE_POR_ID = {
        'Morelia':         'Morelia',
        'Zitacuaro':       'Zitácuaro',
        'Uruapan':         'Uruapan',
        'LazaroCardenas':  'Lázaro Cárdenas',
        'Sahuayo':         'Sahuayo',
        'Zamora':          'Zamora',
    };

    const resumenPorSede = @json($resumenSedes);

    const formatoEntero = new Intl.NumberFormat('en-US');
    const formatoMoneda = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'MXN', maximumFractionDigits: 0 });

    function mostrarTarjetaSede(sede) {
        const datos = resumenPorSede[sede] ?? {
            solicitudes_atendidas: 0,
            audiencias_celebradas: 0,
            tasa_conciliacion: 0,
            montos_convenios: 0,
        };

        document.getElementById('sede-nombre').textContent = sede;
        document.getElementById('sede-solicitudes').textContent = formatoEntero.format(datos.solicitudes_atendidas);
        document.getElementById('sede-audiencias').textContent = formatoEntero.format(datos.audiencias_celebradas);
        document.getElementById('sede-tasa').textContent = datos.tasa_conciliacion + '%';
        document.getElementById('sede-montos').textContent = formatoMoneda.format(datos.montos_convenios);

        document.getElementById('carta-info').classList.remove('oculto');
    }

    // mapa.js solo agrega el evento click (con abrirCarta) a las 6 sedes
    // -son las únicas con "info" en datosMunicipios-, así que sobreescribir
    // abrirCarta aquí basta: ningún otro municipio la dispara.
    window.abrirCarta = function (idMunicipio) {
        const sede = SEDE_POR_ID[idMunicipio];
        if (sede) mostrarTarjetaSede(sede);
    };

    // Las estrellas/pines (<g class="marcador">) que marcan cada sede son
    // elementos aparte de los <path> de los municipios: mapa.js nunca les
    // puso listener de click, así que no abrían nada. El nombre de la sede
    // ya viene en su <text>, así que no hace falta mapear ids.
    document.querySelectorAll('#mapa-michoacan .marcador').forEach((marcador) => {
        const texto = marcador.querySelector('text');
        const sede = texto ? texto.textContent.trim() : null;
        if (sede) {
            marcador.addEventListener('click', () => mostrarTarjetaSede(sede));
        }
    });
})();
</script>
