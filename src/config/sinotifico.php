<?php

/*
|--------------------------------------------------------------------------
| SíNotifico
|--------------------------------------------------------------------------
| Configuración de la API que consume la aplicación de notificadores.
| Los usuarios son los mismos de SICONCILIO: mismo correo y contraseña.
*/

return [

    /*
     | Quién entra y qué ve no se configura aquí: son los permisos
     | sinotifico_* de Spatie (ver App\Support\Sinotifico\Acceso y la
     | migración 2026_10_20_120000_add_sinotifico_permissions).
     */

    /*
     | Vigencia del token en horas. Una jornada de campo completa, con margen
     | para sincronizar al volver a la oficina. Al vencer, se vuelve a iniciar
     | sesión; lo capturado sin conexión no se pierde porque vive en el teléfono.
     */
    'token_horas' => (int) env('SINOTIFICO_TOKEN_HORAS', 14),

    /*
     | Nombre de la "habilidad" que llevan los tokens emitidos para SíNotifico.
     | Sirve para que un token de esta app no abra otras APIs y viceversa.
     */
    'ability' => 'sinotifico',

    /*
     | Intentos de inicio de sesión por minuto. Por cuenta (correo + IP) y por
     | IP sola, para frenar a quien prueba contraseñas contra muchas cuentas.
     */
    'login_intentos_por_cuenta' => 5,
    'login_intentos_por_ip' => 20,

    /*
     | Estatus de `seer_citados` que significan "falta notificar".
     */
    'estatus_pendientes' => ['Pendiente', 'Sin asignar'],

    /*
     | Valores de `seer_citados.notificacion` en los que el citatorio lo
     | entrega un notificador del Centro (y no el propio solicitante).
     */
    'notifica_el_centro' => ['Centro', 'Ambos'],

    /*
     | Anticipación mínima con la que debe notificarse el citatorio antes de la
     | audiencia (art. 684-E, fracc. IV de la LFT). Se cuenta en días hábiles
     | de la sede.
     */
    'dias_habiles_anticipacion' => (int) env('SINOTIFICO_DIAS_ANTICIPACION', 5),

    /*
     | Id de Michoacán en la tabla `estados`. Domicilios de otro estado se
     | marcan en la app porque normalmente van por exhorto.
     */
    'estado_id' => 16,

    /*
     | Claves de sede, las mismas que se usan en expedientes y folios.
     */
    'sedes' => [
        'Morelia' => 'MOR',
        'Uruapan' => 'URU',
        'Zamora' => 'ZAM',
        'Sahuayo' => 'SAH',
        'Lázaro Cárdenas' => 'LAZ',
        'Zitácuaro' => 'ZIT',
    ],

    /*
     | Punto de salida de las rutas: la oficina de cada sede. Son coordenadas
     | aproximadas del centro de cada ciudad; ajústalas a la dirección real.
     */
    'oficinas' => [
        'Morelia' => ['lat' => 19.7027, 'lng' => -101.1923],
        'Uruapan' => ['lat' => 19.4116, 'lng' => -102.0517],
        'Zamora' => ['lat' => 19.9855, 'lng' => -102.2840],
        'Sahuayo' => ['lat' => 20.0573, 'lng' => -102.7193],
        'Lázaro Cárdenas' => ['lat' => 17.9585, 'lng' => -102.1943],
        'Zitácuaro' => ['lat' => 19.4359, 'lng' => -100.3576],
    ],

    /*
     | Rutas del día (Módulo 4). Máximo de paradas que se proponen y velocidad
     | promedio para estimar el tiempo (más 15 minutos por diligencia).
     */
    'ruta_max_paradas' => (int) env('SINOTIFICO_RUTA_MAX_PARADAS', 20),
    'ruta_kmh' => 25,
    'ruta_minutos_por_parada' => 15,

    /*
     | Ubicación de domicilios. Se usa Nominatim (OpenStreetMap), que pide no
     | más de una consulta por segundo y un User-Agent que identifique la app.
     | Con 'geocodificador' => 'ninguno' sólo se usan las ubicaciones ya
     | guardadas o las que se fijan a mano en el mapa.
     */
    'geocodificador' => env('SINOTIFICO_GEOCODIFICADOR', 'nominatim'),
    'nominatim_url' => env('SINOTIFICO_NOMINATIM_URL', 'https://nominatim.openstreetmap.org/search'),
    'nominatim_agente' => env('SINOTIFICO_NOMINATIM_AGENTE', 'SiNotifico/1.0 (CCL Michoacan; sistemas)'),
    'geocodificar_por_vez' => 15,

];
