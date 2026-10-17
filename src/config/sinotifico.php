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
     | Roles de SICONCILIO que pueden entrar a SíNotifico. El notificador usa
     | la app en campo; delegados y administradores la usan para supervisar.
     */
    'roles' => [
        'Notificador',
        'Delegado',
        'Administrador',
        'Super Usuario',
    ],

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

];
