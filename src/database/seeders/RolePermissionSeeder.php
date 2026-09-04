<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Resetear la caché de permisos de Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Lista tomada de tu Excel
        $permisosExcel = [
            'bloqueo_sede_crear',
            'bloqueo_sede_ver',
            'retroceso_audiencia_crear',
            'retroceso_ratificacion_crear',
            'retroceso_cumplimiento_crear',
            'cumplimientos_borrar_cumplimiento',
            'cambiar_fecha_audiencia',
            'cambiar_fecha_cumplimiento',
            'asesoria_crear',
            'asesoria_consultar',
            'asesoria_editar',
            'asesoria_borrar',
            'audiencias_consultar',
            'audiencias_revisar',
            'audiencias_borrar_motivo',
            'audiencias_agregar_motivo',
            'audiencias_borrar_citados',
            'audiencias_guardar_edicion',

            'audiencia_archivar',
            'audiencia_incompetencia',
            'audiencia_desistimiento',
            'audiencia_no_conciliacion',
            'audiencia_editar_solicitante',
            'audiencia_registrar_comparecencia',
            'audiencia_comparece_sin_facultades',
            'audiencias_subir_documentos',

            'audiencia_ver_documento_digital',
            'audiencias_ver_citatorios',

            'notificaciones_ver_documento',
            'notificaciones_ver_notificacion',
            'notificaciones_subir_documento',
            'notificaciones_cambiar_notificador',

            'casos_excepcion_atencion',
            'casos_excepcion_canalizacion',
            'conciliadores_permisos',
            'conciliadores_permisos_consultar',
            'conciliadores_permisos_editar',
            'conciliadores_permisos_crear',
            'cumplimientos_consultar',
            'cumplimientos_generar_cumplimiento',
            'cumplimientos_generar_incumplimiento',
            'cumplimientos_generar_incomparecencia',
            'cumplimientos_generar_cumplimiento_total',
            'cumplimientos_ver_pdf',
            
            'reportes_audiencias',
            'reportes_cumplimientos',
            'reportes_ratificaciones',
            'reportes_notificaciones',
            'reportes_solicitudes',
            'reportes_convenios',
            'reportes_graficas',
            'reportes_productividad',
            'reportes_inegi',
            'reportes_motivos',
            'reportes_general_sede',
            'reportes_conciliador',
            'reportes_cumplimientos_programados',
            'reportes_seguro_social',
            'reportes_municipios',
            'reportes_actividad',

            'incidencia_crear',
            'incidencia_consultar',
            'incidencias_borrar',
            'turnos_atender',

            'poderes_crear',
            'poderes_editar',
            'poderes_borrar',
            'poderes_ver_expediente',
            'poderes_ver_historial',
            'poderes_agregar_representante',
            'por_notificar_asignar',
            'por_notificar_editar',

            'por_notificar_guardar_edicion',
            'ratificaciones_crear',
            'ratificaciones_consultar',
            'ratificaciones_concluir',
            'ratificaciones_subir_documentos',
            'ratificaciones_editar',
            'roles_crear',
            'roles_editar',
            'roles_eliminar',
            'solicitudes_pendientes_validar',
            'solicitudes_validar',
            'solicitudes_ver',
            'solicitudes_crear',
            'solicitudes_revisar',
            'solicitudes_editar',
            'solicitudes_subir_documentos',
            'solicitudes_ver_documentos',

            'turnos_crear',
            'turnos_revisar',
            'usuarios_crear',
            'usuarios_editar',
            'usuarios_eliminar',
            
        ];

        // Crear permisos que no existan
        foreach ($permisosExcel as $permiso) {
            Permission::firstOrCreate(
                ['name' => $permiso, 'guard_name' => 'web']
            );
        }

        //Borrar todos los permisos viejos que NO están en la nueva lista
        Permission::whereNotIn('name', $permisosExcel)
            ->where('guard_name', 'web')
            ->delete();

        // OPCIONAL: Si deseas eliminar los permisos de la BD que YA NO estén en el Excel
        // Permission::whereNotIn('name', $permisosExcel)->delete();
        // ---------------------------------------------------------
        // FASE 2: GESTIÓN DE ROLES Y ASIGNACIÓN
        // ---------------------------------------------------------
        // 3. Obtener o crear Roles
        $superAdmin  = Role::firstOrCreate(['name' => 'Super Usuario', 'guard_name' => 'web']);
        $administrador    = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $capacitacion_admin    = Role::firstOrCreate(['name' => 'Capacitacion Admin', 'guard_name' => 'web']);
        $capacitacion_usuario    = Role::firstOrCreate(['name' => 'Capacitacion Usuario', 'guard_name' => 'web']);
        $auxiliar    = Role::firstOrCreate(['name' => 'Auxiliar', 'guard_name' => 'web']);
        $conciliador = Role::firstOrCreate(['name' => 'Conciliador', 'guard_name' => 'web']);
        $notificador    = Role::firstOrCreate(['name' => 'Notificador', 'guard_name' => 'web']);
        $delegado    = Role::firstOrCreate(['name' => 'Delegado', 'guard_name' => 'web']);
        $estadistica    = Role::firstOrCreate(['name' => 'Estadistica', 'guard_name' => 'web']);
        $turnos = Role::firstOrCreate(['name' => 'Turnos', 'guard_name' => 'web']);
        $registro = Role::firstOrCreate(['name' => 'Registro', 'guard_name' => 'web']);
        $excepcion = Role::firstOrCreate(['name' => 'Excepcion', 'guard_name' => 'web']);
        $enlace      = Role::firstOrCreate(['name' => 'Enlace', 'guard_name' => 'web']);
        $solicitante      = Role::firstOrCreate(['name' => 'Solicitante', 'guard_name' => 'web']);
        $cumplimientos      = Role::firstOrCreate(['name' => 'Cumplimientos', 'guard_name' => 'web']);
        $particular      = Role::firstOrCreate(['name' => 'Particular', 'guard_name' => 'web']);
        $tercer_encuentro      = Role::firstOrCreate(['name' => 'Tercer Encuentro', 'guard_name' => 'web']);
        $directivo      = Role::firstOrCreate(['name' => 'Directivo', 'guard_name' => 'web']);
        $orientadores      = Role::firstOrCreate(['name' => 'Orientadores', 'guard_name' => 'web']);
        

        // 4. Asignar TODOS los permisos al Super Usuario
        // Al haber borrado los viejos en el paso 2, all() solo traerá los nuevos de tu Excel
        $superAdmin->syncPermissions(Permission::all());

        $directivo->syncPermissions([
            'reportes_audiencias',
            'reportes_cumplimientos',
            'reportes_ratificaciones',
            'reportes_cumplimientos',
            'reportes_notificaciones',
            'reportes_solicitudes',
            'reportes_convenios',
            'reportes_graficas',
            'reportes_productividad',
            'reportes_inegi',
            'reportes_motivos',
            'reportes_general_sede',
            'reportes_conciliador',
            'reportes_cumplimientos_programados',
            'reportes_seguro_social',
            'reportes_municipios',
            'reportes_actividad',

            'solicitudes_revisar',

            'ratificaciones_consultar',
            
            'cumplimientos_consultar',
            
            'audiencias_consultar',

            'notificaciones_ver notificacion',


        ]);

        

        $auxiliar->syncPermissions([
            'asesoria_crear',
            'asesoria_consultar',

            'audiencias_consultar',
            'audiencias_revisar',

            'notificaciones_ver_notificacion',

            'reportes_solicitudes',
            'reportes_ratificaciones',

            'poderes_crear',
            'poderes_editar',
            'poderes_agregar_representante',
            
            'ratificaciones_crear',
            'ratificaciones_consultar',
            'ratificaciones_concluir',

            'solicitudes_pendientes_validar',
            'solicitudes_validar',
            'solicitudes_ver',
            'solicitudes_crear',
            'solicitudes_revisar',
            'solicitudes_editar',
            
        ]);

        $orientadores->syncPermissions([
            'asesoria_crear',
            'asesoria_consultar',

            'reportes_solicitudes',

            'poderes_crear',
            'poderes_editar',
            'poderes_agregar_representante',

            'solicitudes_pendientes_validar',
            'solicitudes_validar',
            'solicitudes_ver',
            'solicitudes_crear',
            'solicitudes_revisar',
            'solicitudes_editar',

        ]);
        

        $conciliador->syncPermissions([
            'asesoria_crear',
            'asesoria_consultar',

            'audiencias_consultar',
            'audiencias_revisar',
            'audiencias_borrar_motivo',
            'audiencias_agregar_motivo',
            'audiencias_borrar_citados',
            'audiencias_guardar_edicion',
            'audiencia_archivar',
            'audiencia_incompetencia',
            'audiencia_desistimiento',
            'audiencia_no_conciliacion',
            'audiencia_editar_solicitante',
            'audiencia_registrar_comparecencia',
            'audiencia_comparece_sin_facultades',
            'audiencias_subir_documentos',
            'audiencia_ver_documento_digital',
            'audiencias_ver_citatorios',

            'notificaciones_ver_notificacion',


            'reportes_audiencias',
            'reportes_conciliador',

            'poderes_crear',
            'poderes_editar',
            'poderes_agregar_representante',

            'solicitudes_pendientes_validar',
            'solicitudes_validar',
            'solicitudes_ver',
            'solicitudes_crear',
            'solicitudes_revisar',
            'solicitudes_editar',
            

        ]);


        $enlace->syncPermissions([
            'asesoria_crear',
            'asesoria_consultar',

        ]);

    }
}