<?php

namespace App\Services;

use App\Models\Company;
use App\Models\FtpConfig;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FtpService
{
    public function uploadFile(Company $company, string $localPath, string $remotePath)
    {
        try {
            $ftpConfig = $company->ftpConfig;

            if (!$ftpConfig || !$ftpConfig->status) {
                return [
                    'success' => false,
                    'message' => 'No se encontró configuración FTP activa para la empresa',
                ];
            }

            $diskConfig = $this->buildFtpDiskConfig($ftpConfig);

            config(['filesystems.disks.dynamic_ftp' => $diskConfig]);

            $fileContents = file_get_contents($localPath);

            if ($fileContents === false) {
                return [
                    'success' => false,
                    'message' => 'No se pudo leer el archivo local',
                ];
            }

            $uploaded = Storage::disk('dynamic_ftp')->put($remotePath, $fileContents);

            if ($uploaded) {
                Log::info("Archivo subido exitosamente al FTP", [
                    'company' => $company->code,
                    'remote_path' => $remotePath,
                ]);

                return [
                    'success' => true,
                    'message' => 'Archivo subido exitosamente al FTP',
                    'remote_path' => $remotePath,
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al subir archivo al FTP',
                ];
            }
        } catch (\Exception $e) {
            Log::error("Error en uploadFile FTP", [
                'company' => $company->code,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al subir archivo: ' . $e->getMessage(),
            ];
        }
    }

    public function testConnection(FtpConfig $ftpConfig)
    {
        try {
            Log::info('=== TEST FTP CONNECTION INICIO ===', [
                'host' => $ftpConfig->host,
                'port' => $ftpConfig->port,
                'username' => $ftpConfig->username,
                'ssl' => $ftpConfig->ssl,
                'passive' => $ftpConfig->passive,
            ]);

            // Intentar conexión con funciones nativas de PHP FTP
            $connection = null;
            
            if ($ftpConfig->ssl) {
                Log::info('Intentando conexión FTP con SSL...');
                $connection = @ftp_ssl_connect($ftpConfig->host, $ftpConfig->port, $ftpConfig->timeout);
            } else {
                Log::info('Intentando conexión FTP sin SSL...');
                $connection = @ftp_connect($ftpConfig->host, $ftpConfig->port, $ftpConfig->timeout);
            }

            if (!$connection) {
                Log::error('No se pudo establecer conexión con el servidor FTP', [
                    'host' => $ftpConfig->host,
                    'port' => $ftpConfig->port,
                ]);
                
                return [
                    'success' => false,
                    'message' => 'No se pudo conectar al servidor FTP. Verifica el host y puerto.',
                ];
            }

            Log::info('Conexión establecida, intentando login...');

            // Intentar login
            $login = @ftp_login($connection, $ftpConfig->username, $ftpConfig->password);

            if (!$login) {
                ftp_close($connection);
                Log::error('Login FTP falló', [
                    'username' => $ftpConfig->username,
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Credenciales incorrectas. Verifica usuario y contraseña.',
                ];
            }

            Log::info('Login exitoso');

            // Configurar modo pasivo si está habilitado
            if ($ftpConfig->passive) {
                ftp_pasv($connection, true);
                Log::info('Modo pasivo activado');
            }

            // Intentar cambiar al directorio root si está configurado
            if (!empty($ftpConfig->root_path)) {
                $chdir = @ftp_chdir($connection, $ftpConfig->root_path);
                if (!$chdir) {
                    Log::warning('No se pudo cambiar al directorio root', [
                        'root_path' => $ftpConfig->root_path,
                    ]);
                } else {
                    Log::info('Directorio root accesible', ['root_path' => $ftpConfig->root_path]);
                }
            }

            // Intentar listar archivos del directorio actual para verificar permisos
            $files = @ftp_nlist($connection, '.');
            if ($files === false) {
                Log::warning('No se pudo listar archivos del directorio');
            } else {
                Log::info('Listado de archivos exitoso', ['files_count' => count($files)]);
            }

            // Cerrar conexión nativa
            ftp_close($connection);

            Log::info('Conexión FTP nativa exitosa, probando con Laravel Storage...');

            // Ahora probar con Laravel Storage para subir un archivo de prueba
            $diskConfig = $this->buildFtpDiskConfig($ftpConfig);
            config(['filesystems.disks.test_ftp' => $diskConfig]);

            $testFileName = 'test_connection_' . time() . '.txt';
            $testContent = 'Test connection from middleware_hitch at ' . now()->toDateTimeString();

            Log::info('Intentando subir archivo de prueba', ['filename' => $testFileName]);

            $uploaded = Storage::disk('test_ftp')->put($testFileName, $testContent);

            if ($uploaded) {
                Log::info('Archivo de prueba subido exitosamente');
                
                // Intentar eliminar el archivo de prueba
                $deleted = Storage::disk('test_ftp')->delete($testFileName);
                Log::info('Archivo de prueba eliminado', ['deleted' => $deleted]);

                Log::info('=== TEST FTP CONNECTION EXITOSO ===');

                return [
                    'success' => true,
                    'message' => 'Conexión FTP exitosa. Credenciales válidas y permisos de escritura confirmados.',
                ];
            } else {
                Log::warning('No se pudo subir archivo de prueba');
                
                return [
                    'success' => false,
                    'message' => 'Conexión exitosa pero no se pudo escribir en el servidor FTP. Verifica permisos.',
                ];
            }
        } catch (\Exception $e) {
            Log::error('=== TEST FTP CONNECTION ERROR ===', [
                'host' => $ftpConfig->host,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al conectar con FTP: ' . $e->getMessage(),
            ];
        }
    }

    protected function buildFtpDiskConfig(FtpConfig $ftpConfig)
    {
        return [
            'driver' => 'ftp',
            'host' => $ftpConfig->host,
            'username' => $ftpConfig->username,
            'password' => $ftpConfig->password,
            'port' => $ftpConfig->port,
            'root' => $ftpConfig->root_path,
            'passive' => $ftpConfig->passive,
            'ssl' => $ftpConfig->ssl,
            'timeout' => $ftpConfig->timeout,
        ];
    }

    public function listFiles(Company $company, string $directory = '/')
    {
        try {
            $ftpConfig = $company->ftpConfig;

            if (!$ftpConfig || !$ftpConfig->status) {
                return [
                    'success' => false,
                    'message' => 'No se encontró configuración FTP activa para la empresa',
                ];
            }

            $diskConfig = $this->buildFtpDiskConfig($ftpConfig);

            config(['filesystems.disks.dynamic_ftp' => $diskConfig]);

            $files = Storage::disk('dynamic_ftp')->files($directory);

            return [
                'success' => true,
                'message' => 'Archivos listados exitosamente',
                'data' => $files,
            ];
        } catch (\Exception $e) {
            Log::error("Error en listFiles FTP", [
                'company' => $company->code,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al listar archivos: ' . $e->getMessage(),
            ];
        }
    }
}
