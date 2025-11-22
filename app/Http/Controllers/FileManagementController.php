<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\FileType;
use App\Models\FtpConfig;
use App\Models\FileLog;
use App\Models\FileError;
use App\Services\FileLogService;
use App\Services\FtpService;
use App\Http\Requests\FtpConfigRequest;
use Illuminate\Http\Request;

class FileManagementController extends Controller
{
    protected $fileLogService;
    protected $ftpService;

    public function __construct(FileLogService $fileLogService, FtpService $ftpService)
    {
        $this->middleware('auth');
        $this->fileLogService = $fileLogService;
        $this->ftpService = $ftpService;
    }

    public function index()
    {
        $sidenav = "";
        $stats = [
            'total_companies' => Company::count(),
            'active_companies' => Company::where('status', true)->count(),
            'total_file_types' => FileType::count(),
            'active_file_types' => FileType::where('status', true)->count(),
            'total_files' => FileLog::count(),
            'files_uploaded' => FileLog::where('status', 'uploaded')->count(),
            'files_failed' => FileLog::where('status', 'failed')->count(),
            'total_errors' => FileError::count(),
        ];

        $recentLogs = FileLog::with(['company', 'fileType'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('file-management.index', compact('sidenav', 'stats', 'recentLogs'));
    }

    public function companies()
    {
        $sidenav = "";
        $companies = Company::withCount(['fileLogs', 'fileErrors'])
            ->with('ftpConfig')
            ->orderBy('name')
            ->get();

        return view('file-management.companies', compact('sidenav', 'companies'));
    }

    public function ftpConfig($companyId)
    {
        $sidenav = "";
        $company = Company::findOrFail($companyId);
        $ftpConfig = FtpConfig::where('company_id', $companyId)->first();

        return view('file-management.ftp-config', compact('sidenav', 'company', 'ftpConfig'));
    }

    public function saveFtpConfig(FtpConfigRequest $request)
    {
        $ftpConfig = FtpConfig::updateOrCreate(
            ['company_id' => $request->company_id],
            [
                'host' => $request->host,
                'port' => $request->port ?? 21,
                'username' => $request->username,
                'password' => $request->password,
                'root_path' => $request->root_path ?? '/',
                'ssl' => $request->ssl ?? false,
                'passive' => $request->passive ?? true,
                'timeout' => $request->timeout ?? 30,
                'status' => $request->status ?? true,
                'user_created' => auth()->id(),
                'user_updated' => auth()->id(),
            ]
        );

        return redirect()
            ->route('file-management.companies')
            ->with('success', 'Configuración FTP guardada correctamente');
    }

    public function testFtpConnection($companyId)
    {
        $ftpConfig = FtpConfig::where('company_id', $companyId)->firstOrFail();
        $result = $this->ftpService->testConnection($ftpConfig);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }

    public function logs(Request $request)
    {
        $sidenav = "";

        $companies = Company::where('status', true)->get();
        $fileTypes = FileType::where('status', true)->get();

        $query = FileLog::with(['company', 'fileType', 'errors']);

        if ($request->has('company_id') && $request->company_id) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->has('file_type_id') && $request->file_type_id) {
            $query->where('file_type_id', $request->file_type_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('file-management.logs', compact('sidenav', 'logs', 'companies', 'fileTypes'));
    }

    public function errors(Request $request)
    {
        $sidenav = "";
        $companies = Company::where('status', true)->get();
        $fileTypes = FileType::where('status', true)->get();

        $query = FileError::with(['company', 'fileType', 'fileLog']);

        if ($request->has('company_id') && $request->company_id) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->has('file_type_id') && $request->file_type_id) {
            $query->where('file_type_id', $request->file_type_id);
        }

        if ($request->has('severity') && $request->severity) {
            $query->where('severity', $request->severity);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $errorsData = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('file-management.errors', compact('sidenav', 'errorsData', 'companies', 'fileTypes'));
    }

    public function stats(Request $request)
    {
        $sidenav = "";
        $companies = Company::where('status', true)->get();
        $fileTypes = FileType::where('status', true)->get();

        $companyId = $request->get('company_id');
        $fileTypeId = $request->get('file_type_id');

        $stats = $this->fileLogService->getStatsByCompanyAndType($companyId, $fileTypeId);

        $recentLogs = FileLog::with(['company', 'fileType'])
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when($fileTypeId, fn($q) => $q->where('file_type_id', $fileTypeId))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('file-management.stats', compact('sidenav', 'stats', 'companies', 'fileTypes', 'recentLogs'));
    }
}
