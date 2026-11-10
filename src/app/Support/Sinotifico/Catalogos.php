<?php

namespace App\Support\Sinotifico;

/**
 * Opciones del formulario de notificación de SICONCILIO
 * (resources/views/notificaciones/actualizarCitado.blade.php). La app usa
 * exactamente los mismos valores para que lo validado caiga en las mismas
 * columnas de `seer_citados` y genere las mismas razones en PDF.
 */
final class Catalogos
{
    /** Resultado (seer_citados.estatus). "Notificada" se omite: no tiene razón en PDF. */
    public const RESULTADOS = [
        'Finalizado exitosamente',
        'Recibe pero no firma',
        'No notificada',
        'No exitosa se constituye',
        'No exitosa no se constituye',
    ];

    public const SIN_EXITO = ['No exitosa se constituye', 'No exitosa no se constituye'];

    public const QUIEN_ATIENDE = ['CITADO O REPRESENTANTE', 'OTRA PERSONA', 'NADIE', 'EXHORTO'];

    public const ATIENDE_ALGUIEN = ['CITADO O REPRESENTANTE', 'OTRA PERSONA'];

    public const RELACION = ['RESIDE', 'TRABAJA'];

    public const MEDIOS = ['PLACAS OFICIALES', 'NÚMERO VISIBLE', 'NUMERACIÓN CONSISTENTE', 'INFORMES DE VECINOS', 'RÓTULOS VISIBLES'];

    public const FIRMA = ['NO FIRMA', 'FIRMA', 'SELLA', 'FIRMA Y SELLA', 'NO APLICA'];

    public const GENERO = ['MASCULINO', 'FEMENINO'];

    public const IDENTIFICACION = [
        'NO PROPORCIONA', 'NO ATIENDE PRESENCIALMENTE', 'CREDENCIAL PARA VOTAR', 'LICENCIA O PERMISO PARA CONDUCIR',
        'CREDENCIAL DE IDENTIFICACION LABORAL', 'CREDENCIAL DE INSTITUCIÓN DE SALUD', 'CREDENCIAL DE INSTITUCIÓN ESCOLAR',
        'CARTILLA DE SERVICIO MILITAR', 'PASAPORTE', 'CÉDULA PROFESIONAL', 'RFC', 'OTRA IDENTIFICACIÓN',
    ];

    public const SIN_IDENTIFICACION = ['NO PROPORCIONA', 'NO ATIENDE PRESENCIALMENTE'];

    /** Motivo cuando el notificador se constituyó pero no pudo notificar. */
    public const PROBLEMAS_SE_CONSTITUYE = [
        'CERRADO', 'NO ACCESO AL INMUEBLE', 'NO SEÑALA INTERIOR', 'NÚMERO INTERIOR SEÑALADO NO SE LOCALIZÓ EN DOMICILIO',
        'NO LOGRO LOCALIZAR EL NÚMERO', 'NO SE LOCALIZA EL INMUEBLE CON NÚMERO, MANZANA, LOTE, ETC. SEALADOS',
        'NO EXISTE EN COLONIA', 'NO EXISTE EN MUNICIPIO', 'RAZÓN SOCIAL DIVERSA', 'OTROS',
    ];

    /** Motivo cuando ni siquiera pudo constituirse en el domicilio. */
    public const PROBLEMAS_NO_SE_CONSTITUYE = [
        'OMITE NÚMERO', 'OMITE VIALIDAD', 'OMITE COLONIA', 'OMITE MUNICIPIO', 'FUERA DE LA JURISDICCIÓN', 'NO HAY COPIAS SUFICIENTES',
    ];

    public const VIALIDADES = [
        'AMPLIACION', 'ANDADOR', 'AUTOPISTA', 'AVENIDA', 'BOULEVARD', 'CALLE', 'CALLEJÓN', 'CALZADA', 'CARRETERA', 'CERRADA',
        'CIRCUITO', 'CIRCUNVALACIÓN', 'CONTINUACIÓN', 'CORREDOR', 'DIAGONAL', 'EJE VIAL', 'PERIFÉRICO', 'PROLONGACIÓN',
        'PRIVADA', 'RETORNO', 'VIADUCTO',
    ];

    /** Como en SeerController::update_notificador: el exhorto pasa al notificador de exhortos de la sede. */
    public const NOTIFICADOR_EXHORTO_POR_MUNICIPIO = [
        'Morelia' => 57, 'Zitacuaro' => 57, 'Zitácuaro' => 57,
        'Uruapan' => 58, 'Lázaro Cárdenas' => 58,
        'Zamora' => 59, 'Sahuayo' => 59,
    ];

    public static function todos(): array
    {
        return [
            'resultados' => self::RESULTADOS,
            'quien_atiende' => self::QUIEN_ATIENDE,
            'relacion' => self::RELACION,
            'medios' => self::MEDIOS,
            'firma' => self::FIRMA,
            'genero' => self::GENERO,
            'identificacion' => self::IDENTIFICACION,
            'problemas_se_constituye' => self::PROBLEMAS_SE_CONSTITUYE,
            'problemas_no_se_constituye' => self::PROBLEMAS_NO_SE_CONSTITUYE,
            'vialidades' => self::VIALIDADES,
        ];
    }
}
