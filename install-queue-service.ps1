# Script para instalar el Queue Worker como servicio de Windows usando NSSM
# Requiere NSSM (Non-Sucking Service Manager)

Write-Host "=== Instalador de Queue Worker como Servicio de Windows ===" -ForegroundColor Green
Write-Host ""

# Verificar si se está ejecutando como administrador
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

if (-not $isAdmin) {
    Write-Host "ERROR: Este script debe ejecutarse como Administrador" -ForegroundColor Red
    Write-Host "Haz clic derecho en PowerShell y selecciona 'Ejecutar como administrador'" -ForegroundColor Yellow
    pause
    exit
}

# Rutas
$projectPath = "D:\xampp\htdocs\APP_PRODUCTIVAS\HITCH\WATTS\middleware_hitch"
$phpPath = "C:\xampp\php\php.exe"
$artisanPath = "$projectPath\artisan"
$nssmPath = "$projectPath\nssm.exe"

# Verificar que existan los archivos necesarios
if (-not (Test-Path $phpPath)) {
    Write-Host "ERROR: No se encontró PHP en $phpPath" -ForegroundColor Red
    pause
    exit
}

if (-not (Test-Path $artisanPath)) {
    Write-Host "ERROR: No se encontró artisan en $artisanPath" -ForegroundColor Red
    pause
    exit
}

# Descargar NSSM si no existe
if (-not (Test-Path $nssmPath)) {
    Write-Host "Descargando NSSM..." -ForegroundColor Yellow
    $nssmUrl = "https://nssm.cc/release/nssm-2.24.zip"
    $nssmZip = "$env:TEMP\nssm.zip"
    
    try {
        Invoke-WebRequest -Uri $nssmUrl -OutFile $nssmZip
        Expand-Archive -Path $nssmZip -DestinationPath $env:TEMP -Force
        Copy-Item "$env:TEMP\nssm-2.24\win64\nssm.exe" $nssmPath
        Remove-Item $nssmZip
        Write-Host "NSSM descargado exitosamente" -ForegroundColor Green
    } catch {
        Write-Host "ERROR: No se pudo descargar NSSM. Descárgalo manualmente de https://nssm.cc/download" -ForegroundColor Red
        pause
        exit
    }
}

# Nombre del servicio
$serviceName = "LaravelQueueWorker_Watts"

# Verificar si el servicio ya existe
$existingService = Get-Service -Name $serviceName -ErrorAction SilentlyContinue

if ($existingService) {
    Write-Host "El servicio '$serviceName' ya existe. ¿Deseas reinstalarlo? (S/N)" -ForegroundColor Yellow
    $response = Read-Host
    
    if ($response -eq "S" -or $response -eq "s") {
        Write-Host "Deteniendo y eliminando servicio existente..." -ForegroundColor Yellow
        & $nssmPath stop $serviceName
        & $nssmPath remove $serviceName confirm
        Start-Sleep -Seconds 2
    } else {
        Write-Host "Instalación cancelada" -ForegroundColor Red
        pause
        exit
    }
}

# Instalar el servicio
Write-Host "Instalando servicio '$serviceName'..." -ForegroundColor Yellow

& $nssmPath install $serviceName $phpPath

# Configurar parámetros
& $nssmPath set $serviceName AppDirectory $projectPath
& $nssmPath set $serviceName AppParameters "artisan queue:work --queue=watts_extraction_customers,watts_extraction_products,watts_extraction_vendors,watts_extraction_sellout,default --tries=3 --timeout=600 --sleep=3"
& $nssmPath set $serviceName DisplayName "Laravel Queue Worker - Watts Extraction"
& $nssmPath set $serviceName Description "Procesa las colas de extracción de datos de Watts"
& $nssmm set $serviceName Start SERVICE_AUTO_START

# Configurar logs
$logPath = "$projectPath\storage\logs"
& $nssmPath set $serviceName AppStdout "$logPath\queue-worker-stdout.log"
& $nssmPath set $serviceName AppStderr "$logPath\queue-worker-stderr.log"

# Configurar reinicio automático
& $nssmPath set $serviceName AppExit Default Restart
& $nssmPath set $serviceName AppRestartDelay 5000

Write-Host ""
Write-Host "=== Servicio instalado exitosamente ===" -ForegroundColor Green
Write-Host ""
Write-Host "Nombre del servicio: $serviceName" -ForegroundColor Cyan
Write-Host "Estado: Instalado pero no iniciado" -ForegroundColor Yellow
Write-Host ""
Write-Host "Para iniciar el servicio, ejecuta:" -ForegroundColor White
Write-Host "  nssm start $serviceName" -ForegroundColor Cyan
Write-Host ""
Write-Host "O usa el administrador de servicios de Windows (services.msc)" -ForegroundColor White
Write-Host ""
Write-Host "Logs del worker:" -ForegroundColor White
Write-Host "  - Stdout: $logPath\queue-worker-stdout.log" -ForegroundColor Cyan
Write-Host "  - Stderr: $logPath\queue-worker-stderr.log" -ForegroundColor Cyan
Write-Host ""

$startNow = Read-Host "¿Deseas iniciar el servicio ahora? (S/N)"
if ($startNow -eq "S" -or $startNow -eq "s") {
    & $nssmPath start $serviceName
    Write-Host "Servicio iniciado" -ForegroundColor Green
    
    # Mostrar estado
    Start-Sleep -Seconds 2
    $service = Get-Service -Name $serviceName
    Write-Host "Estado actual: $($service.Status)" -ForegroundColor Cyan
}

Write-Host ""
Write-Host "Presiona cualquier tecla para salir..."
pause
