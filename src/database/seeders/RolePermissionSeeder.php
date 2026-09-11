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
            'audiencias_iniciar',
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
            'notificaciones_editar',

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
            'turnos_ver',
            'turnos_asignar',
            'usuarios_crear',
            'usuarios_editar',
            'usuarios_eliminar',

            'oficialia_crear',
            'oficialia_turnar',
            'oficialia_detalles',
            'oficialia_oficio',
            'oficialia_historial',
            'oficialia_concluir',
            
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
        

    }

    public function down(): void
    {
        // 1. Limpiar caché de Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Borrar los permisos NUEVOS que insertó el Seeder
        $permisosNuevos = [
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
            'turnos_ver',
            'turnos_asignar',
            'usuarios_crear',
            'usuarios_editar',
            'usuarios_eliminar',

            'oficialia_crear',
            'oficialia_turnar',
            'oficialia_detalles',
            'oficialia_oficio',
            'oficialia_historial',
            'oficialia_concluir',
            
        ];
        
        \Spatie\Permission\Models\Permission::whereIn('name', $permisosNuevos)
            ->where('guard_name', 'web')
            ->delete();

        // 3. RECREAR los permisos VIEJITOS (necesitas poner la lista anterior aquí)
        $permisosViejitos = [
            ['id' => 1,  'name' => 'ver-rol', 'guard_name' => 'web'],
            ['id' => 2,  'name' => 'crear-rol', 'guard_name' => 'web'],
            ['id' => 3,  'name' => 'editar-rol', 'guard_name' => 'web'],
            ['id' => 4,  'name' => 'borrar-rol', 'guard_name' => 'web'],
            ['id' => 5,  'name' => 'ver-abogado', 'guard_name' => 'web'],
            ['id' => 6,  'name' => 'crear-abogado', 'guard_name' => 'web'],
            ['id' => 7,  'name' => 'editar-abogado', 'guard_name' => 'web'],
            ['id' => 8,  'name' => 'borrar-abogado', 'guard_name' => 'web'],
            ['id' => 9,  'name' => 'ver-usuario', 'guard_name' => 'web'],
            ['id' => 10, 'name' => 'crear-usuario', 'guard_name' => 'web'],
            ['id' => 11, 'name' => 'editar-usuario', 'guard_name' => 'web'],
            ['id' => 12, 'name' => 'borrar-usuario', 'guard_name' => 'web'],
            ['id' => 13, 'name' => 'ver-curso', 'guard_name' => 'web'],
            ['id' => 14, 'name' => 'crear-curso', 'guard_name' => 'web'],
            ['id' => 15, 'name' => 'editar-curso', 'guard_name' => 'web'],
            ['id' => 16, 'name' => 'borrar-curso', 'guard_name' => 'web'],
            ['id' => 17, 'name' => 'aceptar-persona', 'guard_name' => 'web'],
            ['id' => 18, 'name' => 'ver-miscapacitaciones', 'guard_name' => 'web'],
            ['id' => 19, 'name' => 'crear-miscapacitaciones', 'guard_name' => 'web'],
            ['id' => 20, 'name' => 'ver-seer', 'guard_name' => 'web'],
            ['id' => 21, 'name' => 'crear-seer', 'guard_name' => 'web'],
            ['id' => 22, 'name' => 'editar-seer', 'guard_name' => 'web'],
            ['id' => 23, 'name' => 'ver-estaditica', 'guard_name' => 'web'],
            ['id' => 24, 'name' => 'crear-turnos', 'guard_name' => 'web'],
            ['id' => 25, 'name' => 'ver-turno', 'guard_name' => 'web'],
            ['id' => 26, 'name' => 'ver-reporte-estadistica', 'guard_name' => 'web'],
            ['id' => 27, 'name' => 'ver-estadistica', 'guard_name' => 'web'],
            ['id' => 28, 'name' => 'ver-registro', 'guard_name' => 'web'],
            ['id' => 29, 'name' => 'crear-registro', 'guard_name' => 'web'],
            ['id' => 30, 'name' => 'editar-registro', 'guard_name' => 'web'],
        ];

        foreach ($permisosViejitos as $permiso) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        // 4. (Opcional) Reasignar los permisos viejitos a los roles como estaban antes
        // $enlace = \Spatie\Permission\Models\Role::where('name', 'Enlace')->first();
        // $enlace->syncPermissions(['nombre_permiso_viejo_1', ...]);
    }

}