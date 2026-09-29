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
 * definitivo y escribe users.foto_perfil.
 *
 * El comando es deliberadamente incapaz de pisar una foto existente. Solo
 * escribe en dos casos:
 *
 *   - el usuario no tiene foto todavia;
 *   - la tiene en la base pero el archivo ya no esta en el disco, que es lo
 *     que pasa cuando un despliegue se lleva storage/ o cuando se importa una
 *     base de otro ambiente y la columna llega con rutas de alla.
 *
 * Si el archivo sigue ahi, no se toca. Da igual de donde salio: una foto que
 * alguien subio por Administracion es indistinguible de una sembrada -las dos
 * son usuarios/<uuid>.webp- y el unico criterio seguro es no escribir nunca
 * encima de un archivo que existe.
 *
 * Por eso ya no hay --forzar. La bandera solo servia para empujar una version
 * nueva de una foto del repositorio, y a cambio ponia a un comando de
 * despliegue -que se corre sin mirar- en posicion de borrar el trabajo de una
 * persona sin vuelta atras. Para reemplazar la foto de alguien esta la
 * pantalla de Administracion > Usuarios > Editar, que ademas deja a una
 * persona decidiendolo.
 */
class SembrarFotosPerfil extends Command
{
    protected $signature = 'fotos:sembrar
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

        $simular    = (bool) $this->option('simular');
        $plantadas  = 0;
        $reparadas  = 0;
        $respetadas = 0;
        $problemas  = [];

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

            $anterior = trim((string) $usuario->foto_perfil);

            // La unica pregunta que decide: hay un archivo vivo detras de la
            // columna? Si lo hay, es de alguien y se queda.
            if ($anterior !== '' && Storage::disk('public')->exists($anterior)) {
                $respetadas++;
                continue;
            }

            $reparacion = $anterior !== '';

            if ($simular) {
                $this->line($reparacion
                    ? "  repararia {$correo} (su archivo ya no esta)"
                    : "  sembraria {$archivo} -> {$correo}");

                $reparacion ? $reparadas++ : $plantadas++;
                continue;
            }

            try {
                $destino = FotoPerfil::CARPETA.'/'.Str::uuid()->toString().'.webp';
                Storage::disk('public')->put($destino, FotoPerfil::procesar($ruta));

                $usuario->foto_perfil = $destino;
                $usuario->save();

                // No hay nada que borrar: si llegamos aqui es porque la ruta
                // anterior estaba vacia o apuntaba a un archivo inexistente.
                $reparacion ? $reparadas++ : $plantadas++;
            } catch (Throwable $e) {
                $problemas[] = "Fallo {$archivo}: ".$e->getMessage();
            }
        }

        $this->newLine();
        $this->info("Sembradas: {$plantadas}  ·  Reparadas: {$reparadas}  ·  Respetadas: {$respetadas}");

        if ($respetadas > 0) {
            $this->line("  {$respetadas} ya tenian foto en el disco y no se tocaron.");
        }

        foreach ($problemas as $problema) {
            $this->warn('  '.$problema);
        }

        return $problemas === [] ? self::SUCCESS : self::FAILURE;
    }
}
