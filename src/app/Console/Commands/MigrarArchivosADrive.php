<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Poder; // Ajusta a tu modelo principal si cambia
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Exception;

class MigrarArchivosADrive extends Command
{
    protected $signature = 'siconcilio:migrar-drive';
    protected $description = 'Migración definitiva de documentos locales hacia Google Drive (Desde ID 105)';

    public function handle()
    {
        $this->info('Iniciando migración de producción a Google Drive (A partir del ID 105)...');

        try {
            // 1. Autenticación Directa con OAuth 2.0
            $client = new Client();
            $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
            $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
            $client->addScope(Drive::DRIVE);

            $tokenData = $client->fetchAccessTokenWithRefreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
            
            if (isset($tokenData['error'])) {
                $this->error('Google rechazó el Refresh Token. Motivo: ' . json_encode($tokenData));
                return;
            }
            
            $service = new Drive($client);
            $idCarpetaRaiz = env('GOOGLE_DRIVE_FOLDER_ID'); 

        } catch (Exception $e) {
            $this->error('Fallo crítico al conectar con Google Drive: ' . $e->getMessage());
            return;
        }

        $camposDocumentos = [
            'ineDocumento', 
            'cedulaDocumento', 
            'anexo_documeto', 
            'representacionDocumento'
        ];

        // 2. Procesamiento por bloques
        Poder::where('idAbogado', '>=', 2020)->chunk(50, function ($abogados) use ($service, $idCarpetaRaiz, $camposDocumentos) {
            
            foreach ($abogados as $abogado) {
                $nombreCarpetaAbogado = "Folio_{$abogado->idAbogado}";
                $this->info("Procesando: {$nombreCarpetaAbogado}");

                // Verificamos si realmente hay algún documento que subir antes de crear la carpeta vacía en Drive
                $tieneDocumentos = false;
                foreach ($camposDocumentos as $campo) {
                    if (!empty($abogado->$campo)) {
                        $tieneDocumentos = true;
                        break;
                    }
                }

                if (!$tieneDocumentos) {
                    $this->line("  -> Registro vacío. Omitiendo.");
                    continue;
                }

                // 3. Crear carpeta para el registro actual
                $folderMetadata = new DriveFile([
                    'name' => $nombreCarpetaAbogado,
                    'mimeType' => 'application/vnd.google-apps.folder',
                    'parents' => [$idCarpetaRaiz]
                ]);
                
                try {
                    $carpetaAbogado = $service->files->create($folderMetadata, ['fields' => 'id']);
                    $idCarpetaAbogado = $carpetaAbogado->id;
                } catch (Exception $e) {
                    $this->error("  ✖ No se pudo crear la carpeta: " . $e->getMessage());
                    continue; // Saltamos este abogado si no podemos crear su carpeta
                }

                // 4. Subir documentos
                foreach ($camposDocumentos as $campo) {
                    $rutaGuardadaEnBD = $abogado->$campo;

                    if (empty($rutaGuardadaEnBD)) {
                        continue; 
                    }

                    try {
                        $rutaString = (string) $rutaGuardadaEnBD;
                        $rutaFisicaCorregida = str_replace('"', '_', $rutaString);
                        
                        $pathLocal = str_contains($rutaFisicaCorregida, 'documentos_abogados') 
                                     ? $rutaFisicaCorregida 
                                     : "documentos_abogados/{$rutaFisicaCorregida}";

                        if (!Storage::disk('local')->exists($pathLocal)) {
                            $this->warn("  - Falta archivo físico local en el servidor: {$campo}");
                            continue; 
                        }
                        
                        $contenido = Storage::disk('local')->get($pathLocal);
                        $nombreArchivo = basename($pathLocal);

                        $fileMetadata = new DriveFile([
                            'name' => $nombreArchivo,
                            'parents' => [$idCarpetaAbogado]
                        ]);

                        $service->files->create($fileMetadata, [
                            'data' => $contenido,
                            'mimeType' => 'application/pdf', 
                            'uploadType' => 'multipart',
                            'fields' => 'id'
                        ]);

                        $this->line("  ✔ Éxito: '{$nombreArchivo}' subido a la nube.");
                        
                        // Storage::disk('local')->delete($pathLocal);
                        // $abogado->update([$campo => null]); 

                    } catch (Exception $e) {
                        $this->error("  ✖ Fallo al subir '{$campo}': " . $e->getMessage());
                    }
                }
            }
        });

        $this->info('Migración completada con éxito.');
    }
}