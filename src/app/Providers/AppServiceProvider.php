<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\Filesystem;
use Google\Client;
use Google\Service\Drive;
use Masbug\Flysystem\GoogleDriveAdapter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Aquí le enseñamos a Laravel qué significa el driver 'google'
        Storage::extend('google', function($app, $config) {
            $client = new Client();
            
            // Le indicamos que lea el archivo JSON de la cuenta de servicio
            $client->setAuthConfig($config['serviceAccountKeyFile']);
            $client->addScope(Drive::DRIVE);

            $service = new Drive($client);
            
            // Conectamos el adaptador de Masbug
            $adapter = new GoogleDriveAdapter($service, '1ajzauiDiSlA9n4OuzxjQGy7Xxl2aphp6');
            $driver = new Filesystem($adapter);

            return new FilesystemAdapter($driver, $adapter);
        });
    }
}
