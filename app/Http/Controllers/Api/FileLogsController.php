<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FileLogService;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FileLogsController extends Controller
{
    protected $fileLogService;

    public function __construct(FileLogService $fileLogService)
    {
        $this->fileLogService = $fileLogService;
    }

    public function getLogs(Request $request)
    {
        try {
            $filters = [
                'per_page' => $request->get('per_page', 20),
                'status' => $request->get('status'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
            ];

            $logs = $this->fileLogService->getLogsByCompanyAndType(
                $request->get('company_id'),
                $request->get('file_type_id'),
                $filters
            );

            return ApiResponse::successWithTotal(
                $logs->items(),
                $logs->total(),
                'Logs obtenidos correctamente',
                200
            );
        } catch (\Exception $e) {
            Log::error('Error en getLogs', ['error' => $e->getMessage()]);
            return ApiResponse::errorWithStatus('Error al obtener logs: ' . $e->getMessage(), null, 500);
        }
    }

    public function getErrors(Request $request)
    {
        try {
            $filters = [
                'per_page' => $request->get('per_page', 20),
                'severity' => $request->get('severity'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
            ];

            $errors = $this->fileLogService->getErrorsByCompanyAndType(
                $request->get('company_id'),
                $request->get('file_type_id'),
                $filters
            );

            return ApiResponse::successWithTotal(
                $errors->items(),
                $errors->total(),
                'Errores obtenidos correctamente',
                200
            );
        } catch (\Exception $e) {
            Log::error('Error en getErrors', ['error' => $e->getMessage()]);
            return ApiResponse::errorWithStatus('Error al obtener errores: ' . $e->getMessage(), null, 500);
        }
    }

    public function getStats(Request $request)
    {
        try {
            $stats = $this->fileLogService->getStatsByCompanyAndType(
                $request->get('company_id'),
                $request->get('file_type_id')
            );

            return ApiResponse::successWithTotal(
                $stats,
                1,
                'Estadísticas obtenidas correctamente',
                200
            );
        } catch (\Exception $e) {
            Log::error('Error en getStats', ['error' => $e->getMessage()]);
            return ApiResponse::errorWithStatus('Error al obtener estadísticas: ' . $e->getMessage(), null, 500);
        }
    }
}
