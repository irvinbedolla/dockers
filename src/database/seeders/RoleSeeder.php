<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

            // FASE 2: GESTIÓN DE ROLES Y ASIGNACIÓN

        $superAdmin  = Role::firstOrCreate(['name' => 'Super Usuario', 'guard_name' => 'web']);
        $auxiliar    = Role::firstOrCreate(['name' => 'Auxiliar', 'guard_name' => 'web']);
        $conciliador = Role::firstOrCreate(['name' => 'Conciliador', 'guard_name' => 'web']);
        $notificador    = Role::firstOrCreate(['name' => 'Notificador', 'guard_name' => 'web']);
        $delegado    = Role::firstOrCreate(['name' => 'Delegado', 'guard_name' => 'web']);
        $estadistica    = Role::firstOrCreate(['name' => 'Estadistica', 'guard_name' => 'web']);
        $turnos = Role::firstOrCreate(['name' => 'Turnos', 'guard_name' => 'web']);
        $excepcion = Role::firstOrCreate(['name' => 'Excepcion', 'guard_name' => 'web']);
        $enlace      = Role::firstOrCreate(['name' => 'Enlace', 'guard_name' => 'web']); 
        $cumplimientos      = Role::firstOrCreate(['name' => 'Cumplimientos', 'guard_name' => 'web']);
        $directivo      = Role::firstOrCreate(['name' => 'Directivo', 'guard_name' => 'web']);
        $orientadores      = Role::firstOrCreate(['name' => 'Orientador', 'guard_name' => 'web']);

        $administrador    = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $capacitacion_admin    = Role::firstOrCreate(['name' => 'Capacitacion Admin', 'guard_name' => 'web']);
        $capacitacion_usuario    = Role::firstOrCreate(['name' => 'Capacitacion Usuario', 'guard_name' => 'web']);

        $registro = Role::firstOrCreate(['name' => 'Registro', 'guard_name' => 'web']);
        $solicitante      = Role::firstOrCreate(['name' => 'Solicitante', 'guard_name' => 'web']);
        $tercer_encuentro      = Role::firstOrCreate(['name' => 'Tercer Encuentro', 'guard_name' => 'web']);
        $particular      = Role::firstOrCreate(['name' => 'Particular', 'guard_name' => 'web']);
         

        // 4. Asignar TODOS los permisos al Super Usuario
        // Al haber borrado los viejos en el paso 2, all() solo traerá los nuevos de tu Excel
        $superAdmin->syncPermissions(Permission::all());

        $directivo->syncPermissions([
            'audiencias_consultar',
            'audiencia_ver_documento_digital',
            'audiencias_ver_citatorios',
            'audiencias_revisar',

            'asesoria_consultar',

            'poderes_ver_expediente',
            'poderes_ver_historial',

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
            'solicitudes_ver',
            'solicitudes_ver_documentos',

            'ratificaciones_consultar',
            
            'cumplimientos_consultar',
            'cumplimientos_ver_pdf',

            'notificaciones_ver_notificacion',
            'notificaciones_ver_documento',


        ]);

        

        $auxiliar->syncPermissions([
            'asesoria_crear',
            'asesoria_consultar',

            'audiencias_consultar',
            'audiencias_revisar',
            'audiencia_ver_documento_digital',
            'audiencias_ver_citatorios',

            'cumplimientos_consultar',
            'cumplimientos_generar_cumplimiento',
            'cumplimientos_generar_incumplimiento',
            'cumplimientos_generar_incomparecencia',
            'cumplimientos_generar_cumplimiento_total',

            'notificaciones_ver_notificacion',

            'reportes_solicitudes',
            'reportes_ratificaciones',

            'poderes_crear',
            'poderes_editar',
            'poderes_agregar_representante',
            'poderes_ver_expediente',
            
            'ratificaciones_subir_documentos',
            'ratificaciones_crear',
            'ratificaciones_consultar',
            'ratificaciones_concluir',

            'solicitudes_pendientes_validar',
            'solicitudes_validar',
            'solicitudes_ver',
            'solicitudes_ver_documentos',
            'solicitudes_crear',
            'solicitudes_revisar',
            'solicitudes_editar',
            'solicitudes_subir_documentos',
            
        ]);

        $orientadores->syncPermissions([
            'audiencias_consultar',
            'audiencia_ver_documento_digital',
            'audiencias_ver_citatorios',

            'asesoria_crear',
            'asesoria_consultar',

            'reportes_solicitudes',

            'poderes_crear',
            'poderes_editar',
            'poderes_agregar_representante',
            'poderes_ver_expediente',

            'solicitudes_pendientes_validar',
            'solicitudes_validar',
            'solicitudes_ver',
            'solicitudes_ver_documentos',
            'solicitudes_crear',
            'solicitudes_revisar',
            'solicitudes_editar',
            'solicitudes_subir_documentos',

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
            'audiencias_iniciar',
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

            'ratificaciones_subir_documentos',

            'poderes_crear',
            'poderes_editar',
            'poderes_agregar_representante',

            'solicitudes_pendientes_validar',
            'solicitudes_validar',
            'solicitudes_ver',
            'solicitudes_crear',
            'solicitudes_revisar',
            'solicitudes_editar',
            'solicitudes_subir_documentos',
            'solicitudes_ver_documentos',
            

        ]);

        $notificador->syncPermissions([
            'asesoria_crear',
            'asesoria_consultar',
        
            'notificaciones_ver_documento',
            'notificaciones_ver_notificacion',
            'notificaciones_subir_documento',
            'notificaciones_cambiar_notificador',

            'solicitudes_ver',
            'solicitudes_ver_documentos',

            'reportes_notificaciones',
            'por_notificar_asignar',
            'por_notificar_editar',
            'por_notificar_guardar_edicion',

        ]);

        $delegado->syncPermissions([
            'asesoria_crear',
            'asesoria_consultar',

            'audiencias_consultar',
            'audiencia_ver_documento_digital',
            'audiencias_ver_citatorios',
            'audiencias_guardar_edicion',

            'notificaciones_ver_notificacion',
            
            'reportes_audiencias',
            'reportes_cumplimientos',
            'reportes_ratificaciones',
            'reportes_cumplimientos',
            'reportes_notificaciones',
            'reportes_solicitudes',
            'reportes_convenios',
            'reportes_productividad',
            'reportes_motivos',
            'reportes_general_sede',
            'reportes_cumplimientos_programados',

            'ratificaciones_consultar',

            'solicitudes_revisar',
            'solicitudes_ver',
            'solicitudes_ver_documentos',
            
            'cumplimientos_consultar',

            'oficialia_turnar',
            'oficialia_detalles',
            'oficialia_oficio',
            'oficialia_concluir',

        ]);

        $estadistica->syncPermissions([
            'notificaciones_ver_documento',
            'notificaciones_ver_notificacion',

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

            'solicitudes_ver',
            'solicitudes_ver_documentos',


        ]);
        $turnos->syncPermissions([
            'asesoria_crear',
            'asesoria_consultar',

            'oficialia_crear',
            'oficialia_turnar',
            'oficialia_detalles',
            'oficialia_oficio',
            'oficialia_historial',

            'solicitudes_ver',
            'solicitudes_ver_documentos',

            'turnos_ver',
            'turnos_crear',
            'turnos_asignar',

        ]);
        $excepcion->syncPermissions([
            'asesoria_crear',
            'asesoria_consultar',

            'audiencias_consultar',
            'audiencia_ver_documento_digital',
            'audiencias_ver_citatorios',
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
            'solicitudes_ver_documentos',
            'solicitudes_crear',
            'solicitudes_revisar',
            'solicitudes_editar',

            'casos_excepcion_atencion',
            'casos_excepcion_canalizacion',

            'oficialia_crear',
            'oficialia_turnar',
            'oficialia_detalles',
            'oficialia_oficio',
            'oficialia_historial',


        ]);

        $enlace->syncPermissions([
            'asesoria_consultar',

            'audiencias_guardar_edicion',
            
            'notificaciones_ver_documento',
            'notificaciones_ver_notificacion',
            'notificaciones_subir_documento',
            'notificaciones_cambiar_notificador',
            'notificaciones_editar',
            'poderes_agregar_representante',
            'por_notificar_asignar',
            'por_notificar_editar',
            'por_notificar_guardar_edicion',

            'ratificaciones_editar',
            'ratificaciones_consultar',

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

            'solicitudes_ver',
            'solicitudes_ver_documentos',
            'solicitudes_revisar',
            'solicitudes_editar',

        ]);

        $cumplimientos->syncPermissions([
            'asesoria_crear',
            'asesoria_consultar',

            'reportes_cumplimientos',

            'poderes_crear',
            'poderes_editar',
            'poderes_agregar_representante',

            'cumplimientos_consultar',
            'cumplimientos_generar_cumplimiento',
            'cumplimientos_generar_incumplimiento',
            'cumplimientos_generar_incomparecencia',
            'cumplimientos_generar_cumplimiento_total',
            'cumplimientos_ver_pdf',

            'solicitudes_ver',
            'solicitudes_ver_documentos'
        ]);
        //
    }

    public function down(): void
    {
        // 1. Limpiar caché
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Apagar la revisión de llaves foráneas para evitar errores por el orden del rollback
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();

        // 3. Limpiar las asignaciones actuales de la tabla pivote
        \Illuminate\Support\Facades\DB::table('role_has_permissions')->delete();

        // 5. Restaurar la tabla pivote (Permiso, Rol)
        $pivote = [
            ['permission_id' => 1, 'role_id' => 16], ['permission_id' => 1, 'role_id' => 17], ['permission_id' => 1, 'role_id' => 39],
            ['permission_id' => 2, 'role_id' => 16], ['permission_id' => 2, 'role_id' => 17],
            ['permission_id' => 3, 'role_id' => 16], ['permission_id' => 3, 'role_id' => 17],
            ['permission_id' => 4, 'role_id' => 16], ['permission_id' => 4, 'role_id' => 17],
            ['permission_id' => 5, 'role_id' => 16], ['permission_id' => 5, 'role_id' => 17], ['permission_id' => 5, 'role_id' => 20], ['permission_id' => 5, 'role_id' => 21], ['permission_id' => 5, 'role_id' => 22], ['permission_id' => 5, 'role_id' => 23], ['permission_id' => 5, 'role_id' => 31], ['permission_id' => 5, 'role_id' => 32],
            ['permission_id' => 6, 'role_id' => 16], ['permission_id' => 6, 'role_id' => 17], ['permission_id' => 6, 'role_id' => 20], ['permission_id' => 6, 'role_id' => 21], ['permission_id' => 6, 'role_id' => 22], ['permission_id' => 6, 'role_id' => 23], ['permission_id' => 6, 'role_id' => 31], ['permission_id' => 6, 'role_id' => 32],
            ['permission_id' => 7, 'role_id' => 16], ['permission_id' => 7, 'role_id' => 17], ['permission_id' => 7, 'role_id' => 20], ['permission_id' => 7, 'role_id' => 21], ['permission_id' => 7, 'role_id' => 22], ['permission_id' => 7, 'role_id' => 23], ['permission_id' => 7, 'role_id' => 31], ['permission_id' => 7, 'role_id' => 32],
            ['permission_id' => 8, 'role_id' => 16], ['permission_id' => 8, 'role_id' => 17], ['permission_id' => 8, 'role_id' => 20], ['permission_id' => 8, 'role_id' => 21], ['permission_id' => 8, 'role_id' => 22], ['permission_id' => 8, 'role_id' => 31],
            ['permission_id' => 9, 'role_id' => 16], ['permission_id' => 9, 'role_id' => 17], ['permission_id' => 9, 'role_id' => 30],
            ['permission_id' => 10, 'role_id' => 16], ['permission_id' => 10, 'role_id' => 17], ['permission_id' => 10, 'role_id' => 30],
            ['permission_id' => 11, 'role_id' => 16], ['permission_id' => 11, 'role_id' => 17], ['permission_id' => 11, 'role_id' => 23],
            ['permission_id' => 12, 'role_id' => 16], ['permission_id' => 12, 'role_id' => 17],
            ['permission_id' => 13, 'role_id' => 16], ['permission_id' => 13, 'role_id' => 17], ['permission_id' => 13, 'role_id' => 18], ['permission_id' => 13, 'role_id' => 19], ['permission_id' => 13, 'role_id' => 20], ['permission_id' => 13, 'role_id' => 38],
            ['permission_id' => 14, 'role_id' => 16], ['permission_id' => 14, 'role_id' => 17], ['permission_id' => 14, 'role_id' => 18],
            ['permission_id' => 15, 'role_id' => 16], ['permission_id' => 15, 'role_id' => 17], ['permission_id' => 15, 'role_id' => 18],
            ['permission_id' => 16, 'role_id' => 16], ['permission_id' => 16, 'role_id' => 17], ['permission_id' => 16, 'role_id' => 18],
            ['permission_id' => 17, 'role_id' => 16], ['permission_id' => 17, 'role_id' => 17], ['permission_id' => 17, 'role_id' => 18],
            ['permission_id' => 18, 'role_id' => 16], ['permission_id' => 18, 'role_id' => 17], ['permission_id' => 18, 'role_id' => 18], ['permission_id' => 18, 'role_id' => 20], ['permission_id' => 18, 'role_id' => 31],
            ['permission_id' => 19, 'role_id' => 16], ['permission_id' => 19, 'role_id' => 17], ['permission_id' => 19, 'role_id' => 18], ['permission_id' => 19, 'role_id' => 20], ['permission_id' => 19, 'role_id' => 31],
            ['permission_id' => 20, 'role_id' => 16], ['permission_id' => 20, 'role_id' => 17], ['permission_id' => 20, 'role_id' => 20], ['permission_id' => 20, 'role_id' => 21], ['permission_id' => 20, 'role_id' => 22], ['permission_id' => 20, 'role_id' => 23], ['permission_id' => 20, 'role_id' => 31], ['permission_id' => 20, 'role_id' => 32],
            ['permission_id' => 21, 'role_id' => 16], ['permission_id' => 21, 'role_id' => 17], ['permission_id' => 21, 'role_id' => 20], ['permission_id' => 21, 'role_id' => 21], ['permission_id' => 21, 'role_id' => 22], ['permission_id' => 21, 'role_id' => 31], ['permission_id' => 21, 'role_id' => 32],
            ['permission_id' => 22, 'role_id' => 16], ['permission_id' => 22, 'role_id' => 17], ['permission_id' => 22, 'role_id' => 20], ['permission_id' => 22, 'role_id' => 21], ['permission_id' => 22, 'role_id' => 22], ['permission_id' => 22, 'role_id' => 23], ['permission_id' => 22, 'role_id' => 31], ['permission_id' => 22, 'role_id' => 32],
            ['permission_id' => 23, 'role_id' => 16], ['permission_id' => 23, 'role_id' => 17], ['permission_id' => 23, 'role_id' => 23], ['permission_id' => 23, 'role_id' => 27], ['permission_id' => 23, 'role_id' => 32],
            ['permission_id' => 24, 'role_id' => 16], ['permission_id' => 24, 'role_id' => 17], ['permission_id' => 24, 'role_id' => 23], ['permission_id' => 24, 'role_id' => 28],
            ['permission_id' => 25, 'role_id' => 16], ['permission_id' => 25, 'role_id' => 17], ['permission_id' => 25, 'role_id' => 23], ['permission_id' => 25, 'role_id' => 28], ['permission_id' => 25, 'role_id' => 32],
            ['permission_id' => 26, 'role_id' => 16], ['permission_id' => 26, 'role_id' => 17], ['permission_id' => 26, 'role_id' => 23], ['permission_id' => 26, 'role_id' => 32],
            ['permission_id' => 27, 'role_id' => 16], ['permission_id' => 27, 'role_id' => 17], ['permission_id' => 27, 'role_id' => 23], ['permission_id' => 27, 'role_id' => 32],
            ['permission_id' => 28, 'role_id' => 16], ['permission_id' => 28, 'role_id' => 17], ['permission_id' => 28, 'role_id' => 29], ['permission_id' => 28, 'role_id' => 32],
            ['permission_id' => 29, 'role_id' => 16], ['permission_id' => 29, 'role_id' => 17], ['permission_id' => 29, 'role_id' => 29],
            ['permission_id' => 30, 'role_id' => 16], ['permission_id' => 30, 'role_id' => 17], ['permission_id' => 30, 'role_id' => 29]
        ];

        \Illuminate\Support\Facades\DB::table('role_has_permissions')->insert($pivote);

        // 6. Volver a encender la revisión de llaves foráneas
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
    }
}
