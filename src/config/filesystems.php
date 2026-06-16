<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],

        'abogados' => [
            'driver' => 'local',
            'root' => storage_path('app/documentos_abogados'),
            //'url' => env('APP_URL').'/fotos-de-usuarios',
            'visibility' => 'public',
        ],

        'Solicitud' => [
            'driver' => 'local',
            'root' => storage_path('app/documentos_solicitud'),
            //'url' => env('APP_URL').'/fotos-de-usuarios',
            'visibility' => 'public',
        ],
        
        'google' => [
            'driver' => 'google',
            'clientId' => env('GOOGLE_DRIVE_CLIENT_ID'),
            'clientSecret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
            'refreshToken' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
            'folder' => env('GOOGLE_DRIVE_FOLDER_ID'),
            'serviceAccountId' => env('GOOGLE_DRIVE_SERVICE_ACCOUNT_ID'),
            'serviceAccountKeyFile' => storage_path('app/google-drive-key.json'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        //public_path('storage')                  => storage_path('app/public'),
        public_path('publico')                  => storage_path('app/public'),
        public_path('documentos')               => storage_path('app/documentos_abogados'),
        public_path('documentosPersonal')       => storage_path('app/documentos_personal'),
        public_path('documentosModulos')        => storage_path('app/documentos_modulo'),
        public_path('images')                   => storage_path('app/images'),
        public_path('documentosSolicitud')      => storage_path('app/documentos_solicitud'),
        public_path('documentosCitatorios')     => storage_path('app/documentos_citatorios'),
        public_path('documentos_ratificacion')  => storage_path('app/documentos_ratificacion'),
        public_path('documentos_notificacion')  => storage_path('app/documentos_notificacion')
    ],

];
