<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('permissions')->delete();
        
        \DB::table('permissions')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'ver-rol',
                'guard_name' => 'web',
                'created_at' => '2023-05-10 08:28:26',
                'updated_at' => '2023-05-10 08:28:26',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'crear-rol',
                'guard_name' => 'web',
                'created_at' => '2023-05-10 08:28:26',
                'updated_at' => '2023-05-10 08:28:26',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'editar-rol',
                'guard_name' => 'web',
                'created_at' => '2023-05-10 08:28:26',
                'updated_at' => '2023-05-10 08:28:26',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'borrar-rol',
                'guard_name' => 'web',
                'created_at' => '2023-05-10 08:28:26',
                'updated_at' => '2023-05-10 08:28:26',
            ),
            4 => 
            array (
                'id' => 5,
                'name' => 'ver-abogado',
                'guard_name' => 'web',
                'created_at' => '2023-05-10 08:28:26',
                'updated_at' => '2023-05-10 08:28:26',
            ),
            5 => 
            array (
                'id' => 6,
                'name' => 'crear-abogado',
                'guard_name' => 'web',
                'created_at' => '2023-05-10 08:28:26',
                'updated_at' => '2023-05-10 08:28:26',
            ),
            6 => 
            array (
                'id' => 7,
                'name' => 'editar-abogado',
                'guard_name' => 'web',
                'created_at' => '2023-05-10 08:28:26',
                'updated_at' => '2023-05-10 08:28:26',
            ),
            7 => 
            array (
                'id' => 8,
                'name' => 'borrar-abogado',
                'guard_name' => 'web',
                'created_at' => '2023-05-10 08:28:26',
                'updated_at' => '2023-05-10 08:28:26',
            ),
            8 => 
            array (
                'id' => 9,
                'name' => 'ver-usuario',
                'guard_name' => 'web',
                'created_at' => '2023-05-10 08:28:26',
                'updated_at' => '2023-05-10 08:28:26',
            ),
            9 => 
            array (
                'id' => 10,
                'name' => 'crear-usuario',
                'guard_name' => 'web',
                'created_at' => '2023-05-10 08:28:26',
                'updated_at' => '2023-05-10 08:28:26',
            ),
            10 => 
            array (
                'id' => 11,
                'name' => 'editar-usuario',
                'guard_name' => 'web',
                'created_at' => '2023-05-10 08:28:26',
                'updated_at' => '2023-05-10 08:28:26',
            ),
            11 => 
            array (
                'id' => 12,
                'name' => 'borrar-usuario',
                'guard_name' => 'web',
                'created_at' => '2023-05-10 08:28:26',
                'updated_at' => '2023-05-10 08:28:26',
            ),
            12 => 
            array (
                'id' => 13,
                'name' => 'ver-curso',
                'guard_name' => 'web',
                'created_at' => '2024-06-05 15:38:07',
                'updated_at' => '2024-06-05 15:38:11',
            ),
            13 => 
            array (
                'id' => 14,
                'name' => 'crear-curso',
                'guard_name' => 'web',
                'created_at' => '2024-06-05 15:38:24',
                'updated_at' => '2024-06-05 15:38:24',
            ),
            14 => 
            array (
                'id' => 15,
                'name' => 'editar-curso',
                'guard_name' => 'web',
                'created_at' => '2024-06-05 15:38:24',
                'updated_at' => '2024-06-05 15:38:24',
            ),
            15 => 
            array (
                'id' => 16,
                'name' => 'borrar-curso',
                'guard_name' => 'web',
                'created_at' => '2024-06-05 15:38:24',
                'updated_at' => '2024-06-05 15:38:24',
            ),
            16 => 
            array (
                'id' => 17,
                'name' => 'aceptar-persona',
                'guard_name' => 'web',
                'created_at' => '2024-06-05 15:38:24',
                'updated_at' => '2024-06-05 15:38:24',
            ),
            17 => 
            array (
                'id' => 18,
                'name' => 'ver-miscapacitaciones',
                'guard_name' => 'web',
                'created_at' => '2024-06-12 10:42:28',
                'updated_at' => '2024-06-12 10:42:29',
            ),
            18 => 
            array (
                'id' => 19,
                'name' => 'crear-miscapacitaciones',
                'guard_name' => 'web',
                'created_at' => '2024-06-12 10:43:03',
                'updated_at' => '2024-06-12 10:43:03',
            ),
            19 => 
            array (
                'id' => 20,
                'name' => 'ver-seer',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 10:53:14',
                'updated_at' => '2024-08-29 10:53:21',
            ),
            20 => 
            array (
                'id' => 21,
                'name' => 'crear-seer',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 10:53:26',
                'updated_at' => '2024-08-29 10:53:26',
            ),
            21 => 
            array (
                'id' => 22,
                'name' => 'editar-seer',
                'guard_name' => 'web',
                'created_at' => '2024-08-29 10:53:48',
                'updated_at' => '2024-08-29 10:53:48',
            ),
            22 => 
            array (
                'id' => 23,
                'name' => 'ver-estaditica',
                'guard_name' => 'web',
                'created_at' => '2024-09-02 10:46:16',
                'updated_at' => '2024-09-23 12:46:24',
            ),
            23 => 
            array (
                'id' => 24,
                'name' => 'crear-turnos',
                'guard_name' => 'web',
                'created_at' => '2024-10-04 15:22:36',
                'updated_at' => '2024-10-04 15:22:36',
            ),
            24 => 
            array (
                'id' => 25,
                'name' => 'ver-turno',
                'guard_name' => 'web',
                'created_at' => '2024-10-04 15:22:36',
                'updated_at' => '2024-10-04 15:22:36',
            ),
            25 => 
            array (
                'id' => 26,
                'name' => 'ver-reporte-estadistica',
                'guard_name' => 'web',
                'created_at' => '2024-11-21 11:46:49',
                'updated_at' => '2024-11-21 11:46:49',
            ),
            26 => 
            array (
                'id' => 27,
                'name' => 'ver-estadistica',
                'guard_name' => 'web',
                'created_at' => '2024-11-21 11:47:01',
                'updated_at' => '2024-11-21 11:47:02',
            ),
            27 => 
            array (
                'id' => 28,
                'name' => 'ver-registro',
                'guard_name' => 'web',
                'created_at' => '2024-11-22 14:03:19',
                'updated_at' => '2024-11-22 14:03:19',
            ),
            28 => 
            array (
                'id' => 29,
                'name' => 'crear-registro',
                'guard_name' => 'web',
                'created_at' => '2024-11-22 15:03:18',
                'updated_at' => '2024-11-22 14:05:18',
            ),
            29 => 
            array (
                'id' => 30,
                'name' => 'editar-registro',
                'guard_name' => 'web',
                'created_at' => '2024-11-22 14:04:29',
                'updated_at' => '2024-11-22 14:04:52',
            ),
        ));
        
        
    }
}