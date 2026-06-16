<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrganizarDocumentosSolicitud extends Command
{
    protected $signature = 'documentos:organizar';
    protected $description = 'Organiza de forma masiva los archivos sueltos pasándolos a carpetas individuales por su ID correspondiente';

    public function handle()
    {
        // 1. Autodetectar la raíz física en Xampp
        $rutasPosibles = [
            storage_path('app'),
            storage_path('app/public'),
            public_path()
        ];

        $raizFisica = null;
        foreach ($rutasPosibles as $ruta) {
            if (
                //is_dir($ruta . '/documentosSolicitud') || 
                //is_dir($ruta . '/documentos_abogados') //|| 
                is_dir($ruta . '/documentos_ratificacion') //|| 
                //is_dir($ruta . '/documentos_notificacion')
                ) {
                $raizFisica = $ruta;
                break;
            }
        }

        if (!$raizFisica) {
            $this->error("No se pudo encontrar ninguna de las carpetas de documentos en los directorios de Laravel.");
            return Command::FAILURE;
        }

        $this->info("Carpeta raíz de almacenamiento detectada en: {$raizFisica}");

        // 2. Recopilar todos los archivos existentes desde las 5 tablas
        $listaArchivos = [];
/*
        // --- TABLA 1: documentos ---
        if (Schema::hasTable('documentos')) {
            $docs = DB::table('documentos')->select('id_solicitud', 'nombre_documento as archivo')->whereNotNull('nombre_documento')->where('nombre_documento', '<>', '')->get();
            foreach ($docs as $d) { $listaArchivos[] = ['id' => $d->id_solicitud, 'file' => $d->archivo, 'tipo_id' => 'solicitud']; }
        }

        // --- TABLA 2: seer_solicitante ---
        if (Schema::hasTable('seer_solicitante')) {
            $solicitantes = DB::table('seer_solicitante')->select('id_solicitud', 'documentoIdentificacion as archivo')->whereNotNull('documentoIdentificacion')->where('documentoIdentificacion', '<>', '')->get();
            foreach ($solicitantes as $s) { $listaArchivos[] = ['id' => $s->id_solicitud, 'file' => $s->archivo, 'tipo_id' => 'solicitud']; }
        }
*/
        // --- TABLA 3: turnos ---
        if (Schema::hasTable('turnos')) {
            $turnos = DB::table('turnos')->select('id as id_solicitud', 'documentoidentificacion as archivo')->whereNotNull('documentoidentificacion')->where('documentoidentificacion', '<>', '')->get();
            foreach ($turnos as $t) { $listaArchivos[] = ['id' => $t->id_solicitud, 'file' => $t->archivo, 'tipo_id' => 'solicitud']; }
        }

        // --- TABLA 4: seer_citados ---
        if (Schema::hasTable('seer_citados')) {
            $citados = DB::table('seer_citados')->select('id_solicitud', 'documento', 'documento1', 'documento2', 'imagen_domicilio1', 'imagen_domicilio2')->get();
            foreach ($citados as $c) {
                $columnas = ['documento', 'documento1', 'documento2', 'imagen_domicilio1', 'imagen_domicilio2'];
                foreach ($columnas as $col) {
                    if (!empty($c->$col) && $c->$col !== 'Sin documento' && $c->$col !== 'Sin foto') {
                        $listaArchivos[] = ['id' => $c->id_solicitud, 'file' => $c->$col, 'tipo_id' => 'solicitud'];
                    }
                }
            }
        }
/*
        // --- NUEVA TABLA 5: abogados (Poderes con limpieza de comillas de Windows) ---
        if (Schema::hasTable('abogados')) {
            $abogados = DB::table('abogados')->select('idAbogado', 'ineDocumento', 'cedulaDocumento', 'anexo_documeto', 'representacionDocumento')->get();
            foreach ($abogados as $ab) {
                $columnasAbogado = ['ineDocumento', 'cedulaDocumento', 'anexo_documeto', 'representacionDocumento'];
                foreach ($columnasAbogado as $col) {
                    if (!empty($ab->$col) && $ab->$col !== 'Sin anexo' && $ab->$col !== 'Sin documento') {
                        $listaArchivos[] = ['id' => $ab->idAbogado, 'file' => $ab->$col, 'tipo_id' => 'abogado'];
                    }
                }
            }
        }
*/
        $totalRegistros = count($listaArchivos);
        $this->info("Se prepararon {$totalRegistros} candidatos de archivos para procesar.");

        if ($totalRegistros === 0) {
            $this->comment("No hay archivos registrados pendientes.");
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($totalRegistros);
        $bar->start();

        $movidos = 0;
        $noEncontrados = 0;

        $carpetasOrigen = ['documentosSolicitud', 'documentos_abogados', 'documentos_ratificacion', 'documentos_notificacion'];

        // 3. Procesar y mover archivos en el disco duro de Windows
        foreach ($listaArchivos as $item) {
            $nombreArchivoOriginal = $item['file'];
            $idDestino = $item['id'];
            $archivoEncontrado = false;

            // Generamos la alternativa reemplazando comillas por guiones bajos para Windows
            $nombreArchivoModificado = str_replace('"', '_', $nombreArchivoOriginal);

            foreach ($carpetasOrigen as $carpeta) {
                // Ruta 1: Probar con el nombre exacto de la base de datos
                $rutaViejaFisica1 = $raizFisica . DIRECTORY_SEPARATOR . $carpeta . DIRECTORY_SEPARATOR . $nombreArchivoOriginal;
                // Ruta 2: Probar con la mutación de Windows (Guiones bajos)
                $rutaViejaFisica2 = $raizFisica . DIRECTORY_SEPARATOR . $carpeta . DIRECTORY_SEPARATOR . $nombreArchivoModificado;

                // Definimos el destino final (respetando el nombre modificado para evitar errores de escritura en Windows)
                $directorioNuevo = $raizFisica . DIRECTORY_SEPARATOR . $carpeta . DIRECTORY_SEPARATOR . $idDestino;
                $rutaNuevaFisica = $directorioNuevo . DIRECTORY_SEPARATOR . $nombreArchivoModificado;

                // Determinar si el archivo existe bajo alguna de las dos opciones de nombre
                $rutaOrigenFinal = null;
                if (file_exists($rutaViejaFisica1) && !is_dir($rutaViejaFisica1)) {
                    $rutaOrigenFinal = $rutaViejaFisica1;
                } elseif (file_exists($rutaViejaFisica2) && !is_dir($rutaViejaFisica2)) {
                    $rutaOrigenFinal = $rutaViejaFisica2;
                }

                // Si encontramos el archivo físico suelto en la raíz de la carpeta...
                if ($rutaOrigenFinal) {
                    
                    if (!file_exists($directorioNuevo)) {
                        mkdir($directorioNuevo, 0755, true);
                    }

                    if (rename($rutaOrigenFinal, $rutaNuevaFisica)) {
                        $movidos++;
                        $archivoEncontrado = true;
                        break;
                    }
                } 
                // Si el archivo ya se encontraba ordenado dentro de su carpeta
                elseif (file_exists($rutaNuevaFisica)) {
                    $archivoEncontrado = true;
                    break;
                }
            }

            if (!$archivoEncontrado) {
                $noEncontrados++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        
        $this->info("¡Organización Masiva Completa con soporte para reglas de Windows!");
        $this->line("-> Archivos movidos y sanitizados con éxito: <info>{$movidos}</info>");
        $this->line("-> Archivos que ya estaban en su sitio o no se hallaron: <comment>{$noEncontrados}</comment>");
        
        return Command::SUCCESS;
    }
}