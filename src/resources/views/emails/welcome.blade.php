<!DOCTYPE html>
<html>
<head>
    <title>Bienvenida</title>
</head>
<body>
    <h1>Hola, {{ $userData['nombre'] }} {{ $userData['primer_apellido'] }} {{ $userData['segundo_apellido'] }}!</h1>
    Gracias por registrarte al Tercer Encuentro Nacional de Justicia Laboral: Una Mirada Internacional con Perspectiva en los Derechos Humanos y Acceso a la Justicia Laboral.
    <h3>Te registrarte en:</h3>
    {{ $userData['convesatorio1'] }}<br>
    {{ $userData['convesatorio2'] }}<br>
    {{ $userData['convesatorio3'] }}<br>
    {{ $userData['convesatorio4'] }}<br>
    {{ $userData['convesatorio5'] }}<br>
    {{ $userData['convesatorio6'] }}<br>
    {{ $userData['convesatorio7'] }}<br>
    {{ $userData['convesatorio8'] }}<br>
    {{ $userData['convesatorio9'] }}<br>    

    Se informa que se otorgara constancia de participación por actividad(Conferencias, conversatorios y presentaciones de libro). Así mismo para obtener la constancia general de participación con un valor curricular será necesario contar con al menos el 80% de las asistencias.<br>
    <b>https://cclmichoacan.gob.mx/tercer_encuentro/</b>
</body>
</html>