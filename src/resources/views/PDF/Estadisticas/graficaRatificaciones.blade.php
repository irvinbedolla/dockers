<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

<div style="width: 90%; margin: 30px auto; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 1px solid #f0f0f0;">
    <canvas id="graficaUsuarios"></canvas>
</div>

<script>
    const ctx = document.getElementById('graficaUsuarios').getContext('2d');
    
    // Datos de Laravel
    const etiquetas = @json($nombres);
    const valores = @json($totales);

    // Creamos un degradado profesional para las barras
    const gradient = ctx.createLinearGradient(0, 0, 400, 0);
    gradient.addColorStop(0, 'rgba(134, 155, 156, 0.8)'); // El color turquesa de tu reporte
    gradient.addColorStop(1, 'rgba(134, 155, 156, 1)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: etiquetas,
            datasets: [{
                label: 'Total de Ratificaciones',
                data: valores,
                backgroundColor: gradient,
                borderColor: '#6b7d7e',
                borderWidth: 1,
                borderRadius: 5, // Barras redondeadas modernas
                barThickness: 25, // Grosor elegante de la barra
            }]
        },
        plugins: [ChartDataLabels], // Plugin para ver los números sobre la barra
        options: {
            indexAxis: 'y', // Convertimos a barras horizontales
            responsive: true,
            layout: {
                padding: { right: 40 } // Espacio para que el número al final no se corte
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { display: false, drawBorder: false },
                    ticks: { display: false } // Ocultamos los números de abajo para una vista limpia
                },
                y: {
                    grid: { display: false, drawBorder: false },
                    ticks: {
                        font: { size: 12, family: 'Helvetica', weight: 'bold' },
                        color: '#444'
                    }
                }
            },
            plugins: {
                legend: { display: false },
                title: {
                    display: true,
                    text: 'RENDIMIENTO DE RATIFICACIONES POR AUXILIAR',
                    align: 'start',
                    color: '#5a6a6b',
                    font: { size: 16, weight: 'bold', family: 'Helvetica' },
                    padding: { bottom: 20 }
                },
                datalabels: {
                    anchor: 'end',
                    align: 'right',
                    color: '#869b9c',
                    font: { weight: 'bold', size: 12 },
                    formatter: (value) => value // Muestra el número exacto al final
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#333',
                    bodyColor: '#666',
                    borderColor: '#869b9c',
                    borderWidth: 1,
                    padding: 10,
                    displayColors: false
                }
            }
        }
    });
</script>