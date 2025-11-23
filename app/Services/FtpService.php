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

    /**
     * Sube contenido de archivo directamente al FTP sin necesidad de archivo local
     *
     * @param FtpConfig $ftpConfig Configuración FTP
     * @param string $filename Nombre del archivo remoto
     * @param string $fileContent Contenido del archivo
     * @return array
     */
    public function uploadFileContent(FtpConfig $ftpConfig, string $filename, string $fileContent)
    {
        try {
            if (!$ftpConfig || !$ftpConfig->status) {
                return [
                    'success' => false,
                    'message' => 'Configuración FTP no está activa',
                ];
            }

            $diskConfig = $this->buildFtpDiskConfig($ftpConfig);

            config(['filesystems.disks.dynamic_ftp' => $diskConfig]);

            // Construir ruta remota
            $remotePath = trim($ftpConfig->root_path ?? '', '/') . '/' . $filename;

            $uploaded = Storage::disk('dynamic_ftp')->put($remotePath, $fileContent);

            if ($uploaded) {
                Log::info("Archivo subido exitosamente al FTP (desde contenido)", [
                    'filename' => $filename,
                    'remote_path' => $remotePath,
                    'size' => strlen($fileContent),
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
            Log::error("Error en uploadFileContent FTP", [
                'filename' => $filename,
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
            // Obtener la contraseña desencriptada
            $decryptedPassword = $ftpConfig->password;
            $passwordLength = strlen($decryptedPassword);
            $passwordPreview = $passwordLength > 0 ? substr($decryptedPassword, 0, 2) . str_repeat('*', min($passwordLength - 2, 6)) : '[VACÍA]';
            $protocol = $ftpConfig->protocol ?? 'ftp';

            Log::info('=== TEST CONNECTION INICIO ===', [
                'protocol' => strtoupper($protocol),
                'host' => $ftpConfig->host,
                'port' => $ftpConfig->port,
                'username' => $ftpConfig->username,
                'ssl' => $ftpConfig->ssl,
                'passive' => $ftpConfig->passive,
                'password_length' => $passwordLength,
                'password_preview' => $passwordPreview,
            ]);

            // Si es SFTP, usar Laravel Storage directamente
            if ($protocol === 'sftp') {
                return $this->testSftpConnection($ftpConfig);
            }

            // FTP tradicional - usar funciones nativas de PHP
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

            Log::info('Conexión establecida, intentando login...', [
                'username' => $ftpConfig->username,
                'password_length' => $passwordLength,
            ]);

            // Intentar login con la contraseña desencriptada
            $login = @ftp_login($connection, $ftpConfig->username, $decryptedPassword);

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

    protected function testSftpConnection(FtpConfig $ftpConfig)
    {
        try {
            Log::info('Intentando conexión SFTP con Laravel Storage...');

            // Crear configuración de disco SFTP
            $diskConfig = $this->buildFtpDiskConfig($ftpConfig);
            config(['filesystems.disks.test_sftp' => $diskConfig]);

            // Intentar crear archivo de prueba
            $testFileName = 'test_connection_' . time() . '.txt';
            $testContent = 'Test SFTP connection from middleware_hitch at ' . now()->toDateTimeString();

            Log::info('Intentando subir archivo de prueba SFTP', ['filename' => $testFileName]);

            $uploaded = Storage::disk('test_sftp')->put($testFileName, $testContent);

            if ($uploaded) {
                Log::info('Archivo de prueba SFTP subido exitosamente');

                // Intentar eliminar el archivo de prueba
                $deleted = Storage::disk('test_sftp')->delete($testFileName);
                Log::info('Archivo de prueba SFTP eliminado', ['deleted' => $deleted]);

                Log::info('=== TEST SFTP CONNECTION EXITOSO ===');

                return [
                    'success' => true,
                    'message' => 'Conexión SFTP exitosa. Credenciales válidas y permisos de escritura confirmados.',
                ];
            } else {
                Log::warning('No se pudo subir archivo de prueba SFTP');

                return [
                    'success' => false,
                    'message' => 'Conexión SFTP falló. Verifica credenciales y permisos.',
                ];
            }
        } catch (\Exception $e) {
            Log::error('=== TEST SFTP CONNECTION ERROR ===', [
                'host' => $ftpConfig->host,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al conectar con SFTP: ' . $e->getMessage(),
            ];
        }
    }

    protected function buildFtpDiskConfig(FtpConfig $ftpConfig)
    {
        $protocol = $ftpConfig->protocol ?? 'ftp';

        if ($protocol === 'sftp') {
            return [
                'driver' => 'sftp',
                'host' => $ftpConfig->host,
                'username' => $ftpConfig->username,
                'password' => $ftpConfig->password,
                'port' => $ftpConfig->port,
                'root' => $ftpConfig->root_path,
                'timeout' => $ftpConfig->timeout,
                'directoryPerm' => 0755,
            ];
        }

        // FTP tradicional
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
