<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Sí Concilio - Estadísticas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        @page { margin: 0px; }
        body { padding-top: 50px; background-color: #f4f7f7; font-family: 'Helvetica', sans-serif; }
        main { margin: 20px 50px; }
        
        .chart-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e0e6e6;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #f0f4f4;
            padding-bottom: 10px;
        }

        .chart-title {
            color: #5a6a6b;
            font-weight: bold;
            font-size: 16px;
            text-transform: uppercase;
            margin: 0;
        }

        .btn-export {
            background-color: #869b9c;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            transition: 0.3s;
        }

        .btn-export:hover { background-color: #5a6a6b; color: white; }
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body>

    <main>
        <div class="chart-card" id="container-efectividad">
            <div class="chart-header">
                <h2 class="chart-title">Efectividad por Conciliador</h2>
                <button class="btn-export" onclick="exportToPDF('container-efectividad', 'efectividad')">Descargar Reporte</button>
            </div>
            <div id="chart-efectividad"></div>
        </div>

        <div class="chart-card" id="container-cantidades">
            <div class="chart-header">
                <h2 class="chart-title">Cumplimientos Cantidad</h2>
                <button class="btn-export" onclick="exportToPDF('container-cantidades', 'cantidades')">Descargar Reporte</button>
            </div>
            <div id="chart-cantidades"></div>
        </div>

        <div class="chart-card" id="container-montos">
            <div class="chart-header">
                <h2 class="chart-title">Cumplimientos por Monto Económico</h2>
                <button class="btn-export" onclick="exportToPDF('container-montos', 'montos')">Descargar Reporte</button>
            </div>
            <div id="chart-montos"></div>
        </div>

        <div class="chart-card" id="container-auxiliares">
            <div class="chart-header">
                <h2 class="chart-title">Ratificaciones por Auxiliar</h2>
                <button class="btn-export" onclick="exportToPDF('container-auxiliares', 'auxiliares')">Descargar Reporte</button>
            </div>
            <div id="chart-auxiliares"></div>
        </div>

        <div class="chart-card" id="container-productividad">
            <div class="chart-header">
                <h2 class="chart-title">Solictud por Auxiliar</h2>
                <button class="btn-export" onclick="exportToPDF('container-productividad', 'productividad')">Descargar Reporte</button>
            </div>
            <div id="chart-productividad"></div>
        </div>

        <div class="chart-card" id="container-pastel">
            <div class="chart-header">
                <h2 class="chart-title">Solicitudes por Sede</h2>
                <button class="btn-export" onclick="exportToPDF('container-auxiliares', 'pastel')">Descargar Reporte</button>
            </div>
            <div id="chart-pastel"></div>
        </div>

        <div class="chart-card" id="container-pastelRati">
            <div class="chart-header">
                <h2 class="chart-title">Ratificaciones por Sede</h2>
                <button class="btn-export" onclick="exportToPDF('container-auxiliares', 'pastelRati')">Descargar Reporte</button>
            </div>
            <div id="chart-pastelRati"></div>
        </div>

        <div class="chart-card" id="container-pastelAudiencia">
            <div class="chart-header">
                <h2 class="chart-title">Audiencias por Sede</h2>
                <button class="btn-export" onclick="exportToPDF('container-auxiliares', 'pastelAudiencia')">Descargar Reporte</button>
            </div>
            <div id="chart-pastelAudiencia"></div>
        </div>

        <div class="chart-card" id="container-pastelNotificacion">
            <div class="chart-header">
                <h2 class="chart-title">Notificaciones por Sede</h2>
                <button class="btn-export" onclick="exportToPDF('container-auxiliares', 'pastelNotificacion')">Descargar Reporte</button>
            </div>
            <div id="chart-pastelNotificacion"></div>
        </div>

        <div class="chart-card" id="container-resumen-general">
            <div class="chart-header">
                <h2 class="chart-title">Total de Actuaciones por Sede</h2>
                <p style="font-size: 11px; color: #888;">(Suma de Solicitudes, Ratificaciones, Audiencias y Notificaciones)</p>
            </div>
            <div id="chart-resumen-general"></div>
        </div>

        <script>
            const colorPrimario = '#869b9c';
            const colorSecundario = '#5a6a6b';

            // Datos desde Blade
            const datosEfectividad              = { data: @json($data), labels: @json($labels) };
            const datosRat                      = @json($ratificacionesData);
            const datosAud                      = @json($audienciasData);
            const etiquetasAux                  = @json($nombres_rati);
            const valoresAux                    = @json($totales_rati);
            const etiquetasSedes                = @json($sedes_labels);
            const valoresSedes                  = @json($sedes_valores);
            const etiquetasSedesRati            = @json($sedes_rati_labels);
            const valoresSedesRati              = @json($sedes_rati_valores);
            const etiquetasSedesAudiencia       = @json($sedes_audiencias_labels);
            const valoresSedesAudiencia         = @json($sedes_audiencias_valores);
            const etiquetasSedesNotificacion    = @json($sedes_notificaciones_labels);
            const valoresSedesNotificacion      = @json($sedes_notificaciones_valores);
            const etiquetasResumen              = @json($labels_resumen);
            const valoresResumen                = @json($valores_resumen);

            new ApexCharts(document.querySelector("#chart-efectividad"), {
                chart: { type: 'bar', height: 350, toolbar: { show: false } },
                colors: [colorPrimario],
                series: [{ name: 'Efectividad', data: datosEfectividad.data }],
                xaxis: { categories: datosEfectividad.labels }
            }).render();

            new ApexCharts(document.querySelector("#chart-cantidades"), {
                chart: { type: 'bar', height: 350, toolbar: { show: false } },
                colors: [colorPrimario, colorSecundario],
                series: [
                    { name: 'Ratificaciones', data: [datosRat.total_count, datosRat.pagado_count, datosRat.pendiente_count] },
                    { name: 'Audiencias', data: [datosAud.total_count, datosAud.pagado_count, datosAud.pendiente_count] }
                ],
                xaxis: { categories: ['Total', 'Pagados', 'Pendientes'] }
            }).render();

            new ApexCharts(document.querySelector("#chart-montos"), {
                chart: { type: 'bar', height: 350, toolbar: { show: false } },
                colors: [colorPrimario, colorSecundario],
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        dataLabels: {
                            position: 'top', // Pone el número arriba de la barra
                        },
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        // Esto quita los decimales infinitos y pone el signo $
                        return "$" + Number(val).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '10px',
                        colors: ["#304758"]
                    }
                },
                series: [
                    { 
                        name: 'Ratificaciones ($)', 
                        data: [
                            parseFloat(datosRat.total_monto || 0), 
                            parseFloat(datosRat.pagado_monto || 0), 
                            parseFloat(datosRat.pendiente_monto || 0)
                        ] 
                    },
                    { 
                        name: 'Audiencias ($)', 
                        data: [
                            parseFloat(datosAud.total_monto || 0), 
                            parseFloat(datosAud.pagado_monto || 0), 
                            parseFloat(datosAud.pendiente_monto || 0)
                        ] 
                    }
                ],
                xaxis: { 
                    categories: ['Total', 'Pagados', 'Pendientes'] 
                },
                yaxis: {
                    labels: {
                        formatter: function (val) {
                            // Formato para el eje lateral (Eje Y)
                            return "$" + Number(val).toLocaleString('es-MX');
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return "$" + Number(val).toLocaleString('es-MX', { minimumFractionDigits: 2 });
                        }
                    }
                }
            }).render();

            new ApexCharts(document.querySelector("#chart-auxiliares"), {
                chart: { type: 'bar', height: 400, toolbar: { show: false } },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: '60%',
                        borderRadius: 4,
                        dataLabels: { position: 'top' }
                    }
                },
                colors: [colorPrimario],
                series: [{ name: 'Total Ratificaciones', data: valoresAux }],
                xaxis: { categories: etiquetasAux },
                dataLabels: {
                    enabled: true,
                    offsetX: 30,
                    style: { fontSize: '12px', colors: [colorSecundario] }
                },
                title: {
                    text: '',
                    align: 'left',
                    style: { color: colorSecundario, fontSize: '14px' }
                }
            }).render();

            function exportToPDF(containerId, fileName) {
                const element = document.getElementById(containerId);
                html2canvas(element, {
                    scale: 2,
                    useCORS: true,
                    backgroundColor: '#ffffff'
                }).then(canvas => {
                    const imgData = canvas.toDataURL('image/png');
                    const { jsPDF } = window.jspdf;
                    const pdf = new jsPDF('l', 'mm', 'a4');
                    const pdfWidth = pdf.internal.pageSize.getWidth();
                    const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
                    pdf.addImage(imgData, 'PNG', 0, 10, pdfWidth, pdfHeight);
                    pdf.save(`reporte-${fileName}.pdf`);
                });
            }

            new ApexCharts(document.querySelector("#chart-productividad"), {
                chart: { 
                    type: 'bar', 
                    height: 400, 
                    toolbar: { show: false } 
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: '60%',
                        borderRadius: 4,
                        dataLabels: { position: 'top' }
                    }
                },
                colors: [colorPrimario],
                series: [{ 
                    name: 'Solicitudes Procesadas', 
                    data: @json($totales) 
                }],
                xaxis: { 
                    categories: @json($nombres) 
                },
                dataLabels: {
                    enabled: true,
                    offsetX: 30,
                    style: { 
                        fontSize: '12px', 
                        colors: [colorSecundario] 
                    }
                },
                title: {
                    text: '',
                    align: 'left',
                    style: { 
                        color: colorSecundario, 
                        fontSize: '14px',
                        fontWeight: 'bold'
                    }
                }
            }).render();

            var optionsPie = {
                chart: {
                    type: 'pie', // Cambia a 'donut' si prefieres ese estilo
                    height: 380,
                },
                colors: ['#869b9c', '#5a6a6b', '#3f4d4e', '#a5b7b8', '#ced9d9'], // Paleta acorde a tus colores
                labels: etiquetasSedes, // Ejemplo: ["Morelia", "Uruapan", ...]
                series: valoresSedes,  // Ejemplo: [150, 80, ...]
                legend: {
                    position: 'bottom'
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val, opts) {
                        // Muestra el número real además del porcentaje
                        return opts.w.config.series[opts.seriesIndex];
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val + " solicitudes";
                        }
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            }
            var chartPie = new ApexCharts(document.querySelector("#chart-pastel"), optionsPie);
            chartPie.render();

            var optionsPieRati = {
                chart: {
                    type: 'pie', // Cambia a 'donut' si prefieres ese estilo
                    height: 380,
                },
                colors: ['#869b9c', '#5a6a6b', '#3f4d4e', '#a5b7b8', '#ced9d9'], // Paleta acorde a tus colores
                labels: etiquetasSedesRati, // Ejemplo: ["Morelia", "Uruapan", ...]
                series: valoresSedesRati,  // Ejemplo: [150, 80, ...]
                legend: {
                    position: 'bottom'
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val, opts) {
                        // Muestra el número real además del porcentaje
                        return opts.w.config.series[opts.seriesIndex];
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val + " dataTurnos";
                        }
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            }
            var pastelRati = new ApexCharts(document.querySelector("#chart-pastelRati"), optionsPieRati);
            pastelRati.render();

            var optionsPieAudiencia = {
                chart: {
                    type: 'pie', // Cambia a 'donut' si prefieres ese estilo
                    height: 380,
                },
                colors: ['#869b9c', '#5a6a6b', '#3f4d4e', '#a5b7b8', '#ced9d9'], // Paleta acorde a tus colores
                labels: etiquetasSedesAudiencia, // Ejemplo: ["Morelia", "Uruapan", ...]
                series: valoresSedesAudiencia,  // Ejemplo: [150, 80, ...]
                legend: {
                    position: 'bottom'
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val, opts) {
                        // Muestra el número real además del porcentaje
                        return opts.w.config.series[opts.seriesIndex];
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val + " audiencias";
                        }
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            }
            var pastelAudiencia = new ApexCharts(document.querySelector("#chart-pastelAudiencia"), optionsPieAudiencia);
            pastelAudiencia.render();

            var optionsPieNotificacion = {
                chart: {
                    type: 'pie', // Cambia a 'donut' si prefieres ese estilo
                    height: 380,
                },
                colors: ['#869b9c', '#5a6a6b', '#3f4d4e', '#a5b7b8', '#ced9d9'], // Paleta acorde a tus colores
                labels: etiquetasSedesNotificacion, // Ejemplo: ["Morelia", "Uruapan", ...]
                series: valoresSedesNotificacion,  // Ejemplo: [150, 80, ...]
                legend: {
                    position: 'bottom'
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val, opts) {
                        // Muestra el número real además del porcentaje
                        return opts.w.config.series[opts.seriesIndex];
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val + " notificaciones";
                        }
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            }
            var pastelNotificacion = new ApexCharts(document.querySelector("#chart-pastelNotificacion"), optionsPieNotificacion);
            pastelNotificacion.render();

            var optionsResumen = {
                chart: {
                    type: 'donut', // 'donut' se ve muy bien para resúmenes generales
                    height: 400
                },
                colors: ['#869b9c', '#5a6a6b', '#3f4d4e', '#a5b7b8', '#ced9d9', '#d16d6a'], 
                labels: etiquetasResumen,
                series: valoresResumen,
                legend: {
                    position: 'bottom'
                },
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'TOTAL',
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val, opts) {
                        return opts.w.config.series[opts.seriesIndex];
                    }
                },
                tooltip: {
                    y: {
                        formatter: (val) => val + " Actuaciones"
                    }
                }
            };

            new ApexCharts(document.querySelector("#chart-resumen-general"), optionsResumen).render();

        </script>
    </main>
</body>
</html>