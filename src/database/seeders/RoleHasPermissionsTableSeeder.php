<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RoleHasPermissionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('role_has_permissions')->delete();
        
        \DB::table('role_has_permissions')->insert(array (
            0 => 
            array (
                'permission_id' => 1,
                'role_id' => 16,
            ),
            1 => 
            array (
                'permission_id' => 1,
                'role_id' => 17,
            ),
            2 => 
            array (
                'permission_id' => 2,
                'role_id' => 16,
            ),
            3 => 
            array (
                'permission_id' => 2,
                'role_id' => 17,
            ),
            4 => 
            array (
                'permission_id' => 3,
                'role_id' => 16,
            ),
            5 => 
            array (
                'permission_id' => 3,
                'role_id' => 17,
            ),
            6 => 
            array (
                'permission_id' => 4,
                'role_id' => 16,
            ),
            7 => 
            array (
                'permission_id' => 4,
                'role_id' => 17,
            ),
            8 => 
            array (
                'permission_id' => 5,
                'role_id' => 16,
            ),
            9 => 
            array (
                'permission_id' => 5,
                'role_id' => 17,
            ),
            10 => 
            array (
                'permission_id' => 5,
                'role_id' => 20,
            ),
            11 => 
            array (
                'permission_id' => 5,
                'role_id' => 21,
            ),
            12 => 
            array (
                'permission_id' => 5,
                'role_id' => 22,
            ),
            13 => 
            array (
                'permission_id' => 5,
                'role_id' => 23,
            ),
            14 => 
            array (
                'permission_id' => 5,
                'role_id' => 31,
            ),
            15 => 
            array (
                'permission_id' => 5,
                'role_id' => 32,
            ),
            16 => 
            array (
                'permission_id' => 6,
                'role_id' => 16,
            ),
            17 => 
            array (
                'permission_id' => 6,
                'role_id' => 17,
            ),
            18 => 
            array (
                'permission_id' => 6,
                'role_id' => 20,
            ),
            19 => 
            array (
                'permission_id' => 6,
                'role_id' => 21,
            ),
            20 => 
            array (
                'permission_id' => 6,
                'role_id' => 22,
            ),
            21 => 
            array (
                'permission_id' => 6,
                'role_id' => 23,
            ),
            22 => 
            array (
                'permission_id' => 6,
                'role_id' => 31,
            ),
            23 => 
            array (
                'permission_id' => 6,
                'role_id' => 32,
            ),
            24 => 
            array (
                'permission_id' => 7,
                'role_id' => 16,
            ),
            25 => 
            array (
                'permission_id' => 7,
                'role_id' => 17,
            ),
            26 => 
            array (
                'permission_id' => 7,
                'role_id' => 20,
            ),
            27 => 
            array (
                'permission_id' => 7,
                'role_id' => 21,
            ),
            28 => 
            array (
                'permission_id' => 7,
                'role_id' => 22,
            ),
            29 => 
            array (
                'permission_id' => 7,
                'role_id' => 23,
            ),
            30 => 
            array (
                'permission_id' => 7,
                'role_id' => 31,
            ),
            31 => 
            array (
                'permission_id' => 7,
                'role_id' => 32,
            ),
            32 => 
            array (
                'permission_id' => 8,
                'role_id' => 16,
            ),
            33 => 
            array (
                'permission_id' => 8,
                'role_id' => 17,
            ),
            34 => 
            array (
                'permission_id' => 8,
                'role_id' => 20,
            ),
            35 => 
            array (
                'permission_id' => 8,
                'role_id' => 21,
            ),
            36 => 
            array (
                'permission_id' => 8,
                'role_id' => 22,
            ),
            37 => 
            array (
                'permission_id' => 8,
                'role_id' => 31,
            ),
            38 => 
            array (
                'permission_id' => 9,
                'role_id' => 16,
            ),
            39 => 
            array (
                'permission_id' => 9,
                'role_id' => 17,
            ),
            40 => 
            array (
                'permission_id' => 9,
                'role_id' => 30,
            ),
            41 => 
            array (
                'permission_id' => 10,
                'role_id' => 16,
            ),
            42 => 
            array (
                'permission_id' => 10,
                'role_id' => 17,
            ),
            43 => 
            array (
                'permission_id' => 10,
                'role_id' => 30,
            ),
            44 => 
            array (
                'permission_id' => 11,
                'role_id' => 16,
            ),
            45 => 
            array (
                'permission_id' => 11,
                'role_id' => 17,
            ),
            46 => 
            array (
                'permission_id' => 11,
                'role_id' => 23,
            ),
            47 => 
            array (
                'permission_id' => 12,
                'role_id' => 16,
            ),
            48 => 
            array (
                'permission_id' => 12,
                'role_id' => 17,
            ),
            49 => 
            array (
                'permission_id' => 13,
                'role_id' => 16,
            ),
            50 => 
            array (
                'permission_id' => 13,
                'role_id' => 17,
            ),
            51 => 
            array (
                'permission_id' => 13,
                'role_id' => 18,
            ),
            52 => 
            array (
                'permission_id' => 13,
                'role_id' => 19,
            ),
            53 => 
            array (
                'permission_id' => 13,
                'role_id' => 20,
            ),
            54 => 
            array (
                'permission_id' => 13,
                'role_id' => 38,
            ),
            55 => 
            array (
                'permission_id' => 14,
                'role_id' => 16,
            ),
            56 => 
            array (
                'permission_id' => 14,
                'role_id' => 17,
            ),
            57 => 
            array (
                'permission_id' => 14,
                'role_id' => 18,
            ),
            58 => 
            array (
                'permission_id' => 15,
                'role_id' => 16,
            ),
            59 => 
            array (
                'permission_id' => 15,
                'role_id' => 17,
            ),
            60 => 
            array (
                'permission_id' => 15,
                'role_id' => 18,
            ),
            61 => 
            array (
                'permission_id' => 16,
                'role_id' => 16,
            ),
            62 => 
            array (
                'permission_id' => 16,
                'role_id' => 17,
            ),
            63 => 
            array (
                'permission_id' => 16,
                'role_id' => 18,
            ),
            64 => 
            array (
                'permission_id' => 17,
                'role_id' => 16,
            ),
            65 => 
            array (
                'permission_id' => 17,
                'role_id' => 17,
            ),
            66 => 
            array (
                'permission_id' => 17,
                'role_id' => 18,
            ),
            67 => 
            array (
                'permission_id' => 18,
                'role_id' => 16,
            ),
            68 => 
            array (
                'permission_id' => 18,
                'role_id' => 17,
            ),
            69 => 
            array (
                'permission_id' => 18,
                'role_id' => 18,
            ),
            70 => 
            array (
                'permission_id' => 18,
                'role_id' => 20,
            ),
            71 => 
            array (
                'permission_id' => 18,
                'role_id' => 31,
            ),
            72 => 
            array (
                'permission_id' => 19,
                'role_id' => 16,
            ),
            73 => 
            array (
                'permission_id' => 19,
                'role_id' => 17,
            ),
            74 => 
            array (
                'permission_id' => 19,
                'role_id' => 18,
            ),
            75 => 
            array (
                'permission_id' => 19,
                'role_id' => 20,
            ),
            76 => 
            array (
                'permission_id' => 19,
                'role_id' => 31,
            ),
            77 => 
            array (
                'permission_id' => 20,
                'role_id' => 16,
            ),
            78 => 
            array (
                'permission_id' => 20,
                'role_id' => 17,
            ),
            79 => 
            array (
                'permission_id' => 20,
                'role_id' => 20,
            ),
            80 => 
            array (
                'permission_id' => 20,
                'role_id' => 21,
            ),
            81 => 
            array (
                'permission_id' => 20,
                'role_id' => 22,
            ),
            82 => 
            array (
                'permission_id' => 20,
                'role_id' => 23,
            ),
            83 => 
            array (
                'permission_id' => 20,
                'role_id' => 31,
            ),
            84 => 
            array (
                'permission_id' => 20,
                'role_id' => 32,
            ),
            85 => 
            array (
                'permission_id' => 21,
                'role_id' => 16,
            ),
            86 => 
            array (
                'permission_id' => 21,
                'role_id' => 17,
            ),
            87 => 
            array (
                'permission_id' => 21,
                'role_id' => 20,
            ),
            88 => 
            array (
                'permission_id' => 21,
                'role_id' => 21,
            ),
            89 => 
            array (
                'permission_id' => 21,
                'role_id' => 22,
            ),
            90 => 
            array (
                'permission_id' => 21,
                'role_id' => 31,
            ),
            91 => 
            array (
                'permission_id' => 21,
                'role_id' => 32,
            ),
            92 => 
            array (
                'permission_id' => 22,
                'role_id' => 16,
            ),
            93 => 
            array (
                'permission_id' => 22,
                'role_id' => 17,
            ),
            94 => 
            array (
                'permission_id' => 22,
                'role_id' => 20,
            ),
            95 => 
            array (
                'permission_id' => 22,
                'role_id' => 21,
            ),
            96 => 
            array (
                'permission_id' => 22,
                'role_id' => 22,
            ),
            97 => 
            array (
                'permission_id' => 22,
                'role_id' => 23,
            ),
            98 => 
            array (
                'permission_id' => 22,
                'role_id' => 31,
            ),
            99 => 
            array (
                'permission_id' => 22,
                'role_id' => 32,
            ),
            100 => 
            array (
                'permission_id' => 23,
                'role_id' => 16,
            ),
            101 => 
            array (
                'permission_id' => 23,
                'role_id' => 17,
            ),
            102 => 
            array (
                'permission_id' => 23,
                'role_id' => 23,
            ),
            103 => 
            array (
                'permission_id' => 23,
                'role_id' => 27,
            ),
            104 => 
            array (
                'permission_id' => 23,
                'role_id' => 32,
            ),
            105 => 
            array (
                'permission_id' => 24,
                'role_id' => 16,
            ),
            106 => 
            array (
                'permission_id' => 24,
                'role_id' => 17,
            ),
            107 => 
            array (
                'permission_id' => 24,
                'role_id' => 23,
            ),
            108 => 
            array (
                'permission_id' => 24,
                'role_id' => 28,
            ),
            109 => 
            array (
                'permission_id' => 25,
                'role_id' => 16,
            ),
            110 => 
            array (
                'permission_id' => 25,
                'role_id' => 17,
            ),
            111 => 
            array (
                'permission_id' => 25,
                'role_id' => 23,
            ),
            112 => 
            array (
                'permission_id' => 25,
                'role_id' => 28,
            ),
            113 => 
            array (
                'permission_id' => 25,
                'role_id' => 32,
            ),
            114 => 
            array (
                'permission_id' => 26,
                'role_id' => 16,
            ),
            115 => 
            array (
                'permission_id' => 26,
                'role_id' => 17,
            ),
            116 => 
            array (
                'permission_id' => 26,
                'role_id' => 23,
            ),
            117 => 
            array (
                'permission_id' => 26,
                'role_id' => 32,
            ),
            118 => 
            array (
                'permission_id' => 27,
                'role_id' => 16,
            ),
            119 => 
            array (
                'permission_id' => 27,
                'role_id' => 17,
            ),
            120 => 
            array (
                'permission_id' => 27,
                'role_id' => 23,
            ),
            121 => 
            array (
                'permission_id' => 27,
                'role_id' => 32,
            ),
            122 => 
            array (
                'permission_id' => 28,
                'role_id' => 16,
            ),
            123 => 
            array (
                'permission_id' => 28,
                'role_id' => 17,
            ),
            124 => 
            array (
                'permission_id' => 28,
                'role_id' => 29,
            ),
            125 => 
            array (
                'permission_id' => 28,
                'role_id' => 32,
            ),
            126 => 
            array (
                'permission_id' => 29,
                'role_id' => 16,
            ),
            127 => 
            array (
                'permission_id' => 29,
                'role_id' => 17,
            ),
            128 => 
            array (
                'permission_id' => 29,
                'role_id' => 29,
            ),
            129 => 
            array (
                'permission_id' => 30,
                'role_id' => 16,
            ),
            130 => 
            array (
                'permission_id' => 30,
                'role_id' => 17,
            ),
            131 => 
            array (
                'permission_id' => 30,
                'role_id' => 29,
            ),
        ));
        
        
    }
}