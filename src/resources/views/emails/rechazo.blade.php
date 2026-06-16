<!DOCTYPE html>
<html>
<head>
    <title>Seguimiento a la solicitud {{ $user['id'] }}</title>
</head>
<body>
    <h1>Hola, {{ $user['nombre'] }}.</h1> 
    <br>Tu solicitud ha sido revisada a continuacion debe contestar a este correo lo siguiente: {{ $user['mensaje'] }}.
</body>
</html>