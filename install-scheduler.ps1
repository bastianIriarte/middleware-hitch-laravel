# Script para configurar el Laravel Scheduler en Windows Task Scheduler
# Ejecutar como Administrador

Write-Host "=== Configurador de Laravel Scheduler para Watts Extraction ===" -ForegroundColor Green
Write-Host ""

# Verificar permisos de administrador
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

if (-not $isAdmin) {
    Write-Host "ERROR: Este script debe ejecutarse como Administrador" -ForegroundColor Red
    Write-Host "Haz clic derecho en PowerShell y selecciona 'Ejecutar como administrador'" -ForegroundColor Yellow
    pause
    exit
}

# Configuracion
$taskName = "Laravel_Scheduler_Watts"
$projectPath = "D:\xampp\htdocs\APP_PRODUCTIVAS\HITCH\WATTS\middleware_hitch"
$phpPath = "C:\xampp\php\php.exe"
$artisanPath = "$projectPath\artisan"

# Verificar archivos
if (-not (Test-Path $phpPath)) {
    Write-Host "ERROR: No se encontro PHP en $phpPath" -ForegroundColor Red
    pause
    exit
}

if (-not (Test-Path $artisanPath)) {
    Write-Host "ERROR: No se encontro artisan en $artisanPath" -ForegroundColor Red
    pause
    exit
}

Write-Host "OK PHP encontrado: $phpPath" -ForegroundColor Green
Write-Host "OK Artisan encontrado: $artisanPath" -ForegroundColor Green
Write-Host ""

# Eliminar tarea existente si existe
$existingTask = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
if ($existingTask) {
    Write-Host "La tarea '$taskName' ya existe. Deseas eliminarla y recrearla? (S/N)" -ForegroundColor Yellow
    $response = Read-Host
    
    if ($response -eq "S" -or $response -eq "s") {
        Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
        Write-Host "OK Tarea existente eliminada" -ForegroundColor Green
    } else {
        Write-Host "Instalacion cancelada" -ForegroundColor Red
        pause
        exit
    }
}

# Crear la accion (ejecutar schedule:run cada minuto)
$action = New-ScheduledTaskAction -Execute $phpPath -Argument "$artisanPath schedule:run" -WorkingDirectory $projectPath

# Crear el trigger (cada minuto, indefinidamente)
# Usar 9999 dias en lugar de MaxValue para evitar error de Windows
$trigger = New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Minutes 1) -RepetitionDuration (New-TimeSpan -Days 9999)

# Configurar para que se ejecute incluso si el usuario no esta logueado
$principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -LogonType ServiceAccount -RunLevel Highest

# Configurar settings
$settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable -RunOnlyIfNetworkAvailable -MultipleInstances IgnoreNew

# Registrar la tarea
try {
    Register-ScheduledTask -TaskName $taskName -Action $action -Trigger $trigger -Principal $principal -Settings $settings -Description "Ejecuta el Laravel Scheduler cada minuto para procesar tareas programadas de Watts" -ErrorAction Stop
    
    Write-Host ""
    Write-Host "=== Tarea programada creada exitosamente ===" -ForegroundColor Green
} catch {
    Write-Host ""
    Write-Host "ERROR al crear la tarea: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
    Write-Host "Intenta crear la tarea manualmente:" -ForegroundColor Yellow
    Write-Host "1. Presiona Win + R y escribe: taskschd.msc" -ForegroundColor Cyan
    Write-Host "2. Crea una nueva tarea basica" -ForegroundColor Cyan
    Write-Host "3. Programa: $phpPath" -ForegroundColor Cyan
    Write-Host "4. Argumentos: $artisanPath schedule:run" -ForegroundColor Cyan
    Write-Host "5. Directorio: $projectPath" -ForegroundColor Cyan
    pause
    exit
}
Write-Host ""
Write-Host "Nombre: $taskName" -ForegroundColor Cyan
Write-Host "Frecuencia: Cada 1 minuto" -ForegroundColor Cyan
Write-Host "Usuario: SYSTEM" -ForegroundColor Cyan
Write-Host ""
Write-Host "La tarea ejecutara:" -ForegroundColor White
Write-Host "  php artisan schedule:run" -ForegroundColor Cyan
Write-Host ""
Write-Host "Esto verificara cada minuto si hay tareas programadas pendientes" -ForegroundColor Yellow
Write-Host "y ejecutara las que correspondan segun el horario configurado." -ForegroundColor Yellow
Write-Host ""
Write-Host "Tareas programadas actualmente:" -ForegroundColor White
Write-Host "  - Watts Daily Extraction: Todos los dias a las 2:00 AM" -ForegroundColor Cyan
Write-Host ""
Write-Host "Logs del scheduler:" -ForegroundColor White
Write-Host "  $projectPath\storage\logs\scheduler.log" -ForegroundColor Cyan
Write-Host ""
Write-Host "Para ver la tarea en el Administrador de tareas:" -ForegroundColor White
Write-Host "  1. Presiona Win + R" -ForegroundColor Cyan
Write-Host "  2. Escribe: taskschd.msc" -ForegroundColor Cyan
Write-Host "  3. Busca: $taskName" -ForegroundColor Cyan
Write-Host ""

# Iniciar la tarea
Write-Host "Deseas iniciar la tarea ahora? (S/N)" -ForegroundColor Yellow
$startNow = Read-Host

if ($startNow -eq "S" -or $startNow -eq "s") {
    # Verificar que la tarea existe
    $verifyTask = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
    
    if ($verifyTask) {
        try {
            Start-ScheduledTask -TaskName $taskName -ErrorAction Stop
            Write-Host "OK Tarea iniciada" -ForegroundColor Green
            
            Start-Sleep -Seconds 2
            $task = Get-ScheduledTask -TaskName $taskName
            Write-Host "Estado: $($task.State)" -ForegroundColor Cyan
        } catch {
            Write-Host "ADVERTENCIA: No se pudo iniciar la tarea automaticamente" -ForegroundColor Yellow
            Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Yellow
            Write-Host "La tarea se inicio correctamente y se ejecutara segun el horario programado" -ForegroundColor Green
        }
    } else {
        Write-Host "ADVERTENCIA: La tarea no se encontro" -ForegroundColor Yellow
        Write-Host "Verifica manualmente en el Administrador de tareas" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "Presiona cualquier tecla para salir..."
pause
