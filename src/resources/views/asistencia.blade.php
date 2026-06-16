<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistencia</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #f3f4f6; font-family: sans-serif; }
    </style>
</head>
<body>

    <script>
        Swal.fire({
            icon: '{{ $status }}', // 'success' o 'warning'
            title: '{{ $titulo }}',
            text: '{{ $mensaje }}',
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#3085d6',
            timer: 5000, // Se cierra solo en 5 segundos
            timerProgressBar: true
        }).then((result) => {
            window.close();

            // Si window.close() falla (porque el navegador lo bloquea), 
            // redirigimos a una página en blanco o neutra para que no se quede la info ahí
            setTimeout(function() {
                window.location.href = "about:blank";
            }, 500);
        });
    </script>

</body>
</html>