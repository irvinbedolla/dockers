<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    // Orden fijo y color fijo por sede: la identidad de un color no debe
    // cambiar entre las tres gráficas ni cuando a una sede le toque 0.
    // Paleta real del proyecto (header/sidebar, logo y estado activo del
    // menú): teals #354647/#496163/#869B9C, dorado #CEA845 y grises
    // #5A6A6B/#8A959E. Como son tonos afines (poco contraste de matiz
    // entre sí), la leyenda y la tabla de valores bajo cada gráfica van
    // siempre visibles: la identidad de cada sede no depende solo del color.
    const COLOR_POR_SEDE = {
        'Morelia':          '#354647', // teal oscuro (header/sidebar)
        'Zitácuaro':        '#CEA845', // dorado (logo / activo del menú)
        'Uruapan':          '#869B9C', // teal claro / sage
        'Lázaro Cárdenas':  '#8A959E', // gris medio
        'Zamora':           '#496163', // teal (texto / marca)
        'Sahuayo':          '#5A6A6B', // gris-teal
    };
    const ORDEN_SEDES = Object.keys(COLOR_POR_SEDE);

    function renderGrafica(id, porSede) {
        const sedes = ORDEN_SEDES.filter((sede) => (porSede[sede] ?? 0) > 0);
        const totales = sedes.map((sede) => porSede[sede]);
        const colores = sedes.map((sede) => COLOR_POR_SEDE[sede]);
        const total = totales.reduce((a, b) => a + b, 0);

        new Chart(document.getElementById('grafica-' + id + '-sede').getContext('2d'), {
            type: 'pie',
            data: {
                labels: sedes,
                datasets: [{ data: totales, backgroundColor: colores }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const pct = total ? ((ctx.raw / total) * 100).toFixed(1) : '0.0';
                                return `${ctx.label}: ${ctx.raw} (${pct}%)`;
                            },
                        },
                    },
                },
            },
        });

        // Relief: el amarillo, el aqua y el magenta no llegan a 3:1 de
        // contraste sobre blanco, así que el valor va en texto además de en
        // el color de la gráfica.
        const filas = sedes.map((sede) => {
            const pct = total ? ((porSede[sede] / total) * 100).toFixed(1) : '0.0';
            return `<tr>
                <td><span class="swatch" style="background:${COLOR_POR_SEDE[sede]}"></span>${sede}</td>
                <td class="text-end">${porSede[sede]}</td>
                <td class="text-end text-muted">${pct}%</td>
            </tr>`;
        }).join('');
        document.getElementById('tabla-' + id + '-sede').innerHTML = filas;
    }

    renderGrafica('solicitudes', @json($resumen['solicitudes']['porSede']));
    renderGrafica('audiencias', @json($resumen['audiencias']['porSede']));
    renderGrafica('ratificaciones', @json($resumen['ratificaciones']['porSede']));
})();
</script>
