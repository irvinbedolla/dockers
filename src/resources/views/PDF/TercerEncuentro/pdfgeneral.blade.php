<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>PDF Cuadriculado</title>
    <style>
        /* 1. Estilos para la PÁGINA */
        @page {
            /* Puedes ajustar los márgenes según tu necesidad */
            margin: 0.5cm;
        }

        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            /* Alto de la vista para asegurar que el contenido ocupe la página */
            height: 100vh; 
        }

        /* 2. Estilos para la CUADRÍCULA (Tabla) */
        .page-grid {
            width: 100%;
            /* Ajusta esta altura para que ocupe el espacio de contenido restante */
            height: calc(29.7cm - 1cm); /* A4 Alto (29.7cm) - Margen superior e inferior (2*0.5cm) */
            /* Quita el espacio extra que puedan añadir algunos navegadores */
            border-collapse: collapse; 
        }

        .page-grid td {
            /* Asegura la división en 50% para que sean 4 zonas iguales */
            width: 50%;
            height: 50%;
            
            /* Para que veas la división, puedes quitar el borde al final */
            border: 1px solid #000; 
            padding: 10px;
            box-sizing: border-box; /* Incluye padding y borde dentro del 50% */
            vertical-align: top;
        }

        /* 3. Estilo para el SALTO DE PÁGINA */
        .break-page {
            /* Fuerza un salto de página después de este elemento */
            page-break-after: always;
        }
    </style>
</head>
<body>

    <table class="page-grid">
        <tr>
            <td>
                ### ZONA 1: ARRIBA IZQUIERDA
                <p>{{ $data_page_1['zona_1'] ?? 'Contenido 1' }}</p>
            </td>
            <td>
                ### ZONA 2: ARRIBA DERECHA
                <p>{{ $data_page_1['zona_2'] ?? 'Contenido 2' }}</p>
            </td>
        </tr>
        <tr>
            <td>
                ### ZONA 3: ABAJO IZQUIERDA
                <p>{{ $data_page_1['zona_3'] ?? 'Contenido 3' }}</p>
            </td>
            <td>
                ### ZONA 4: ABAJO DERECHA
                <p>{{ $data_page_1['zona_4'] ?? 'Contenido 4' }}</p>
            </td>
        </tr>
    </table>
    </body>
</html>