<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FtpConfigRequest;
use App\Models\Company;
use App\Models\FtpConfig;
use App\Models\FileType;
use App\Services\FtpService;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FileConfigController extends Controller
{
    protected $ftpService;

    public function __construct(FtpService $ftpService)
    {
        $this->ftpService = $ftpService;
    }

    public function getCompanies()
    {
        try {
            $companies = Company::where('status', true)
                ->select('id', 'code', 'name', 'email', 'phone', 'status')
                ->get();

            return ApiResponse::successWithTotal(
                $companies,
                $companies->count(),
                'Empresas obtenidas correctamente',
                200
            );
        } catch (\Exception $e) {
            Log::error('Error en getCompanies', ['error' => $e->getMessage()]);
            return ApiResponse::errorWithStatus('Error al obtener empresas: ' . $e->getMessage(), null, 500);
        }
    }

    public function getFileTypes()
    {
        try {
            $fileTypes = FileType::where('status', true)
                ->select('id', 'code', 'name', 'description', 'file_extension', 'status')
                ->get();

            return ApiResponse::successWithTotal(
                $fileTypes,
                $fileTypes->count(),
                'Tipos de archivo obtenidos correctamente',
                200
            );
        } catch (\Exception $e) {
            Log::error('Error en getFileTypes', ['error' => $e->getMessage()]);
            return ApiResponse::errorWithStatus('Error al obtener tipos de archivo: ' . $e->getMessage(), null, 500);
        }
    }

    public function getFtpConfig($companyId)
    {
        try {
            $company = Company::find($companyId);
            if (!$company) {
                return ApiResponse::errorWithStatus('Empresa no encontrada', null, 404);
            }

            $ftpConfig = FtpConfig::where('company_id', $companyId)->first();
            if (!$ftpConfig) {
                return ApiResponse::errorWithStatus('No se encontró configuración FTP para esta empresa', null, 404);
            }

            return ApiResponse::successWithTotal(
                [
                    'id' => $ftpConfig->id,
                    'company_id' => $ftpConfig->company_id,
                    'host' => $ftpConfig->host,
                    'port' => $ftpConfig->port,
                    'username' => $ftpConfig->username,
                    'root_path' => $ftpConfig->root_path,
                    'ssl' => $ftpConfig->ssl,
                    'passive' => $ftpConfig->passive,
                    'timeout' => $ftpConfig->timeout,
                    'status' => $ftpConfig->status,
                ],
                1,
                'Configuración FTP obtenida correctamente',
                200
            );
        } catch (\Exception $e) {
            Log::error('Error en getFtpConfig', ['company_id' => $companyId, 'error' => $e->getMessage()]);
            return ApiResponse::errorWithStatus('Error al obtener configuración FTP: ' . $e->getMessage(), null, 500);
        }
    }

    public function saveFtpConfig(FtpConfigRequest $request)
    {
        try {
            $user = auth()->user();
            $userId = $user ? $user->id : null;

            $ftpConfig = FtpConfig::where('company_id', $request->company_id)->first();

            if ($ftpConfig) {
                $ftpConfig->update([
                    'host' => $request->host,
                    'port' => $request->port ?? 21,
                    'username' => $request->username,
                    'password' => $request->password,
                    'root_path' => $request->root_path ?? '/',
                    'ssl' => $request->ssl ?? false,
                    'passive' => $request->passive ?? true,
                    'timeout' => $request->timeout ?? 30,
                    'status' => $request->status ?? true,
                    'user_updated' => $userId,
                ]);
            } else {
                $ftpConfig = FtpConfig::create([
                    'company_id' => $request->company_id,
                    'host' => $request->host,
                    'port' => $request->port ?? 21,
                    'username' => $request->username,
                    'password' => $request->password,
                    'root_path' => $request->root_path ?? '/',
                    'ssl' => $request->ssl ?? false,
                    'passive' => $request->passive ?? true,
                    'timeout' => $request->timeout ?? 30,
                    'status' => $request->status ?? true,
                    'user_created' => $userId,
                ]);
            }

            Log::info('Configuración FTP guardada', ['ftp_config_id' => $ftpConfig->id, 'company_id' => $request->company_id]);

            return ApiResponse::successWithTotal(
                ['ftp_config_id' => $ftpConfig->id, 'company_id' => $ftpConfig->company_id],
                1,
                'Configuración FTP guardada correctamente',
                200
            );
        } catch (\Exception $e) {
            Log::error('Error en saveFtpConfig', ['error' => $e->getMessage()]);
            return ApiResponse::errorWithStatus('Error al guardar configuración FTP: ' . $e->getMessage(), null, 500);
        }
    }

    public function testFtpConnection($companyId)
    {
        try {
            $ftpConfig = FtpConfig::where('company_id', $companyId)->first();
            if (!$ftpConfig) {
                return ApiResponse::errorWithStatus('No se encontró configuración FTP para esta empresa', null, 404);
            }

            $result = $this->ftpService->testConnection($ftpConfig);

            if ($result['success']) {
                return ApiResponse::successWithTotal(['message' => $result['message']], 1, $result['message'], 200);
            } else {
                return ApiResponse::errorWithStatus($result['message'], null, 400);
            }
        } catch (\Exception $e) {
            Log::error('Error en testFtpConnection', ['company_id' => $companyId, 'error' => $e->getMessage()]);
            return ApiResponse::errorWithStatus('Error al probar conexión FTP: ' . $e->getMessage(), null, 500);
        }
    }

    public function updateCompanyStatus(Request $request, $companyId)
    {
        try {
            $company = Company::find($companyId);
            if (!$company) {
                return ApiResponse::errorWithStatus('Empresa no encontrada', null, 404);
            }

            $user = auth()->user();
            $userId = $user ? $user->id : null;

            $company->update([
                'status' => $request->status ?? true,
                'user_updated' => $userId,
            ]);

            return ApiResponse::successWithTotal(
                ['company_id' => $company->id, 'status' => $company->status],
                1,
                'Estado de empresa actualizado correctamente',
                200
            );
        } catch (\Exception $e) {
            Log::error('Error en updateCompanyStatus', ['company_id' => $companyId, 'error' => $e->getMessage()]);
            return ApiResponse::errorWithStatus('Error al actualizar estado de empresa: ' . $e->getMessage(), null, 500);
        }
    }

    public function updateFileTypeStatus(Request $request, $fileTypeId)
    {
        try {
            $fileType = FileType::find($fileTypeId);
            if (!$fileType) {
                return ApiResponse::errorWithStatus('Tipo de archivo no encontrado', null, 404);
            }

            $user = auth()->user();
            $userId = $user ? $user->id : null;

            $fileType->update([
                'status' => $request->status ?? true,
                'user_updated' => $userId,
            ]);

            return ApiResponse::successWithTotal(
                ['file_type_id' => $fileType->id, 'status' => $fileType->status],
                1,
                'Estado de tipo de archivo actualizado correctamente',
                200
            );
        } catch (\Exception $e) {
            Log::error('Error en updateFileTypeStatus', ['file_type_id' => $fileTypeId, 'error' => $e->getMessage()]);
            return ApiResponse::errorWithStatus('Error al actualizar estado de tipo de archivo: ' . $e->getMessage(), null, 500);
        }
    }
}
