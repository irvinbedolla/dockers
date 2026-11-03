<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\Filesystem;
use Google\Client;
use Google\Service\Drive;
use Masbug\Flysystem\GoogleDriveAdapter;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // \PDF corrige caracteres raros antes de imprimir (ver App\Support\PdfSeguro).
        $this->app->bind('dompdf.wrapper', function ($app) {
            return new \App\Support\PdfSeguro($app['dompdf'], $app['config'], $app['files'], $app['view']);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Detrás de CloudFront la app recibe HTTP; sin esto los enlaces,
        // formularios y URLs firmadas saldrían con http://.
        if (config('app.env') !== 'local' || request()->server('HTTP_X_FORWARDED_PROTO') === 'https') {
            URL::forceScheme('https');
        }
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
