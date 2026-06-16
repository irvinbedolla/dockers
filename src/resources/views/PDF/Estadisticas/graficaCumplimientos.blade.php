<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div style="width: 85%; margin: 30px auto; padding: 20px; background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
    <canvas id="graficaCumplimientos"></canvas>
</div>

<div style="width: 85%; margin: 30px auto; padding: 20px; background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
    <canvas id="graficaCumplimientosMonto"></canvas>
</div>

<script>
    // Colores corporativos basados en tu reporte anterior
    const colorPrimario = '#869b9c'; // Turquesa Grisáceo
    const colorSecundario = '#5a6a6b'; // Gris Oscuro Profesional
    const colorFondoPrimario = 'rgba(134, 155, 156, 0.7)';
    const colorFondoSecundario = 'rgba(90, 106, 107, 0.7)';

    const datosRatificacion = @json($ratificacionesData);
    const datosAudiencia = @json($audienciasData);

    // Configuración común para ambas gráficas
    const opcionesComunes = {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    font: { size: 12, family: 'Helvetica' },
                    usePointStyle: true,
                    padding: 20
                }
            },
            tooltip: {
                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                titleColor: '#333',
                bodyColor: '#666',
                borderColor: '#ddd',
                borderWidth: 1,
                padding: 12,
                displayColors: true,
                callbacks: {
                    // Formateo de moneda en el tooltip si es la gráfica de montos
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) label += ': ';
                        if (context.chart.canvas.id === 'graficaCumplimientosMonto') {
                            label += new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(context.parsed.y);
                        } else {
                            label += context.parsed.y;
                        }
                        return label;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { display: true, drawBorder: false, color: '#f0f0f0' },
                ticks: {
                    callback: function(value, index, values) {
                        // Si es la gráfica de montos, añadir el signo de $
                        if (this.chart.canvas.id === 'graficaCumplimientosMonto') {
                            return '$' + value.toLocaleString();
                        }
                        return value;
                    }
                }
            },
            x: {
                grid: { display: false }
            }
        },
        // Animación suave
        animation: { duration: 1500, easing: 'easeInOutQuart' }
    };

    // 1. Gráfica de Cantidades
    new Chart(document.getElementById('graficaCumplimientos'), {
        type: 'bar',
        data: {
            labels: ['Total', 'Pagados', 'Pendientes'],
            datasets: [
                {
                    label: 'Ratificaciones',
                    data: [datosRatificacion.total_count, datosRatificacion.pagado_count, datosRatificacion.pendiente_count],
                    backgroundColor: colorFondoPrimario,
                    borderColor: colorPrimario,
                    borderWidth: 1,
                    borderRadius: 6 // Barras redondeadas
                },
                {
                    label: 'Audiencias',
                    data: [datosAudiencia.total_count, datosAudiencia.pagado_count, datosAudiencia.pendiente_count],
                    backgroundColor: colorFondoSecundario,
                    borderColor: colorSecundario,
                    borderWidth: 1,
                    borderRadius: 6
                }
            ]
        },
        options: {
            ...opcionesComunes,
            plugins: {
                ...opcionesComunes.plugins,
                title: {
                    display: true,
                    text: 'CUMPLIMIENTOS POR CANTIDAD',
                    font: { size: 16, weight: 'bold' },
                    padding: { bottom: 20 }
                }
            }
        }
    });

    // 2. Gráfica de Montos ($)
    new Chart(document.getElementById('graficaCumplimientosMonto'), {
        type: 'bar',
        data: {
            labels: ['Total', 'Pagados', 'Pendientes'],
            datasets: [
                {
                    label: 'Ratificaciones ($)',
                    data: [datosRatificacion.total_monto, datosRatificacion.pagado_monto, datosRatificacion.pendiente_monto],
                    backgroundColor: colorFondoPrimario,
                    borderColor: colorPrimario,
                    borderWidth: 1,
                    borderRadius: 6
                },
                {
                    label: 'Audiencias ($)',
                    data: [datosAudiencia.total_monto, datosAudiencia.pagado_monto, datosAudiencia.pendiente_monto],
                    backgroundColor: colorFondoSecundario,
                    borderColor: colorSecundario,
                    borderWidth: 1,
                    borderRadius: 6
                }
            ]
        },
        options: {
            ...opcionesComunes,
            plugins: {
                ...opcionesComunes.plugins,
                title: {
                    display: true,
                    text: 'CUMPLIMIENTOS POR MONTO ECONÓMICO',
                    font: { size: 16, weight: 'bold' },
                    padding: { bottom: 20 }
                }
            }
        }
    });
</script>