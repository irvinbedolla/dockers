<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div style="width: 80%; margin: auto;">
    <canvas id="graficaProductividad"></canvas>
</div>

<script>
    const ctx = document.getElementById('graficaProductividad').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($nombres),
            datasets: [{
                label: 'Total de Solicitudes Procesadas',
                data: @json($totales),
                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y', // <-- Esto hace que las barras sean horizontales
            responsive: true,
            plugins: {
                legend: { display: false },
                title: {
                    display: true,
                    text: 'Productividad por Auxiliar (Detalle de Registros)'
                }
            }
        }
    });
</script>