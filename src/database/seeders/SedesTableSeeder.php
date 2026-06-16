<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SedesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('sedes')->delete();
        
        \DB::table('sedes')->insert(array (
            0 => 
            array (
                'id' => 1,
                'nombre' => 'Morelia',
                'oficina_apoyo' => 0,
                'create_at' => '2024-09-02 13:22:28',
                'update_at' => '2024-09-02 13:22:28',
            ),
            1 => 
            array (
                'id' => 2,
                'nombre' => 'Zitácuaro',
                'oficina_apoyo' => 1,
                'create_at' => '2024-09-02 13:22:28',
                'update_at' => '2024-09-02 13:22:28',
            ),
            2 => 
            array (
                'id' => 3,
                'nombre' => 'Uruapan',
                'oficina_apoyo' => 0,
                'create_at' => '2024-09-02 14:06:48',
                'update_at' => '2024-09-02 14:06:48',
            ),
            3 => 
            array (
                'id' => 4,
                'nombre' => 'Lázaro Cárdenas',
                'oficina_apoyo' => 3,
                'create_at' => '2024-09-02 14:06:48',
                'update_at' => '2024-09-02 14:06:48',
            ),
            4 => 
            array (
                'id' => 5,
                'nombre' => 'Zamora',
                'oficina_apoyo' => 0,
                'create_at' => '2024-09-02 14:07:20',
                'update_at' => '2024-09-02 14:07:20',
            ),
            5 => 
            array (
                'id' => 6,
                'nombre' => 'Sahuayo',
                'oficina_apoyo' => 5,
                'create_at' => '2024-09-02 14:07:20',
                'update_at' => '2024-09-02 14:07:20',
            ),
        ));
        
        
    }
}