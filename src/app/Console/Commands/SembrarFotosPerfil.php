<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\FotoPerfil;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Instala en storage las fotos versionadas de database/fotos-semilla.
 *
 * Las fotos que vienen del diseno de gafetes viajan en el repositorio porque
 * tienen que llegar solas a cada ambiente. Este comando las mueve a su lugar
 * definitivo y escribe users.foto_perfil. Es idempotente: correrlo dos veces
 * no hace nada la segunda.
 */
class SembrarFotosPerfil extends Command
{
    protected $signature = 'fotos:sembrar
                            {--forzar : Reemplaza la foto aunque el usuario ya tenga una}
                            {--simular : Solo reporta lo que haria, sin escribir}';

    protected $description = 'Instala las fotos de perfil versionadas en database/fotos-semilla';

    public function handle(): int
    {
        $carpeta  = database_path('fotos-semilla');
        $manifest = $carpeta.'/manifest.json';

        if (! is_file($manifest)) {
            $this->error("No existe {$manifest}");

            return self::FAILURE;
        }

        try {
            $filas = json_decode(file_get_contents($manifest), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            $this->error('El manifest.json no es JSON valido: '.$e->getMessage());

            return self::FAILURE;
        }

        $simular   = (bool) $this->option('simular');
        $puestas   = 0;
        $saltadas  = 0;
        $problemas = [];

        foreach ($filas as $fila) {
            $correo  = $fila['email']   ?? null;
            $archivo = $fila['archivo'] ?? null;

            if (! $correo || ! $archivo) {
                $problemas[] = 'Entrada del manifest sin email o sin archivo';
                continue;
            }

            $ruta = $carpeta.'/'.basename($archivo);

            if (! is_file($ruta)) {
                $problemas[] = "Falta el archivo {$archivo} (para {$correo})";
                continue;
            }

            $usuario = User::where('email', $correo)->first();

            if (! $usuario) {
                $problemas[] = "Sin usuario para {$correo}";
                continue;
            }

            // Sin --forzar no pisa una foto que alguien ya subio por la pantalla.
            if ($usuario->foto_perfil && ! $this->option('forzar')) {
                $saltadas++;
                continue;
            }

            if ($simular) {
                $this->line("  sembraria {$archivo} -> {$correo}");
                $puestas++;
                continue;
            }

            try {
                $destino = FotoPerfil::CARPETA.'/'.Str::uuid()->toString().'.webp';
                Storage::disk('public')->put($destino, FotoPerfil::procesar($ruta));

                $anterior = $usuario->foto_perfil;
                $usuario->foto_perfil = $destino;
                $usuario->save();

                FotoPerfil::borrar($anterior);
                $puestas++;
            } catch (Throwable $e) {
                $problemas[] = "Fallo {$archivo}: ".$e->getMessage();
            }
        }

        $this->newLine();
        $this->info("Asignadas: {$puestas}  ·  Sin cambio: {$saltadas}");

        foreach ($problemas as $problema) {
            $this->warn('  '.$problema);
        }

        return $problemas === [] ? self::SUCCESS : self::FAILURE;
    }
}
