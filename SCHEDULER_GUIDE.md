# Guía de Uso del Sistema de Extracciones Programadas de Watts

## 📋 Descripción General

Este sistema permite ejecutar extracciones de datos de Watts de forma automática y programada, utilizando Laravel Scheduler en lugar de un queue worker constante.

---

## 🚀 Instalación Rápida

### 1. Configurar el Scheduler de Laravel

Abre PowerShell **como Administrador** y ejecuta:

```powershell
cd D:\xampp\htdocs\APP_PRODUCTIVAS\HITCH\WATTS\middleware_hitch
.\install-scheduler.ps1
```

Esto creará una tarea en Windows que ejecuta `php artisan schedule:run` cada minuto.

---

## 📅 Horarios Configurados

### Extracción Diaria Completa
- **Comando**: `watts:extract --type=all --async`
- **Horario**: Todos los días a las **2:00 AM**
- **Zona horaria**: America/Santiago
- **Incluye**: Customers, Products, Vendors, SellOut

### Personalizar Horarios

Edita `app/Console/Kernel.php` en el método `schedule()`:

```php
// Ejemplo: Productos cada 6 horas
$schedule->command('watts:extract --type=products --async')
    ->everySixHours()
    ->timezone('America/Santiago');

// Ejemplo: Clientes los lunes a las 8 AM
$schedule->command('watts:extract --type=customers --async')
    ->weeklyOn(1, '08:00')
    ->timezone('America/Santiago');

// Ejemplo: SellOut cada hora
$schedule->command('watts:extract --type=sellout --async')
    ->hourly()
    ->timezone('America/Santiago');
```

---

## 🔧 Uso Manual del Comando

### Ejecutar todas las extracciones (asíncrono):
```bash
php artisan watts:extract --type=all --async
```

### Ejecutar una extracción específica (asíncrono):
```bash
php artisan watts:extract --type=products --async
php artisan watts:extract --type=customers --async
php artisan watts:extract --type=vendors --async
php artisan watts:extract --type=sellout --async
```

### Ejecutar síncronamente (espera a que termine):
```bash
php artisan watts:extract --type=all
php artisan watts:extract --type=products
```

### Con fechas personalizadas (para SellOut):
```bash
php artisan watts:extract --type=sellout --start-date=2025-01-01 --end-date=2025-01-31 --async
```

---

## 📊 Monitoreo

### Ver tareas programadas:
```bash
php artisan schedule:list
```

### Ver logs del scheduler:
```bash
# Windows PowerShell
Get-Content storage\logs\scheduler.log -Tail 50 -Wait

# CMD
type storage\logs\scheduler.log
```

### Ver logs de Laravel:
```bash
Get-Content storage\logs\laravel.log -Tail 50 -Wait
```

### Ver jobs en cola (si usas --async):
```bash
php artisan queue:monitor watts_extraction_customers,watts_extraction_products,watts_extraction_vendors,watts_extraction_sellout
```

### Ver jobs fallidos:
```bash
php artisan queue:failed
```

---

## ⚙️ Configuración del Worker (para modo --async)

Si usas el modo `--async`, necesitas tener el queue worker corriendo:

### Opción 1: Manual (desarrollo)
```bash
.\start-queue-worker.bat
```

### Opción 2: Servicio de Windows (producción)
```powershell
.\install-queue-service.ps1
```

---

## 🔍 Validaciones del Comando

El comando `watts:extract` valida automáticamente:

✅ Que la empresa WATTS exista y esté activa
✅ Que haya configuración FTP activa
✅ Que los FileTypes existan y estén activos
✅ Crea FileLog para cada extracción
✅ Registra errores en file_errors si algo falla

---

## 📧 Notificaciones por Email

Para recibir emails cuando falla una extracción programada:

1. Configura `ADMIN_EMAIL` en tu `.env`:
```env
ADMIN_EMAIL=tu-email@example.com
```

2. Configura el servidor SMTP en `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-contraseña-app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tuempresa.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 🛠️ Gestión del Task Scheduler de Windows

### Ver la tarea:
1. Presiona `Win + R`
2. Escribe: `taskschd.msc`
3. Busca: `Laravel_Scheduler_Watts`

### Iniciar/Detener manualmente:
```powershell
# Iniciar
Start-ScheduledTask -TaskName "Laravel_Scheduler_Watts"

# Detener
Stop-ScheduledTask -TaskName "Laravel_Scheduler_Watts"

# Ver estado
Get-ScheduledTask -TaskName "Laravel_Scheduler_Watts" | Select-Object State
```

### Eliminar la tarea:
```powershell
Unregister-ScheduledTask -TaskName "Laravel_Scheduler_Watts" -Confirm:$false
```

---

## 🐛 Troubleshooting

### El scheduler no ejecuta las tareas:
1. Verifica que la tarea de Windows esté corriendo:
   ```powershell
   Get-ScheduledTask -TaskName "Laravel_Scheduler_Watts"
   ```

2. Revisa los logs:
   ```bash
   type storage\logs\scheduler.log
   ```

3. Ejecuta manualmente para ver errores:
   ```bash
   php artisan schedule:run
   ```

### Los jobs no se procesan (modo --async):
1. Verifica que el queue worker esté corriendo
2. Revisa los logs de Laravel
3. Reinicia el worker si es necesario

### Cambios en el código no se reflejan:
1. Si usas queue worker, reinícialo
2. Si es el scheduler, espera al siguiente minuto (se recarga automáticamente)

---

## 📝 Ejemplos de Uso

### Ejecutar extracción completa ahora:
```bash
php artisan watts:extract --type=all --async
```

### Ejecutar solo productos síncronamente:
```bash
php artisan watts:extract --type=products
```

### Ejecutar SellOut de la última semana:
```bash
php artisan watts:extract --type=sellout --start-date=2025-01-15 --end-date=2025-01-22 --async
```

### Ver qué se ejecutará hoy:
```bash
php artisan schedule:list
```

---

## ✅ Ventajas de este Sistema

✅ **No consume recursos constantemente** (solo se ejecuta cuando es necesario)
✅ **Horarios flexibles** (configura cuando quieras)
✅ **Validaciones automáticas** antes de ejecutar
✅ **Logs detallados** de cada ejecución
✅ **Notificaciones por email** en caso de fallo
✅ **Modo síncrono y asíncrono** según necesites
✅ **Fácil de monitorear** con comandos de Laravel

---

## 🔄 Diferencia con Queue Worker

### Queue Worker (anterior):
- Corre **constantemente** verificando colas
- Consume recursos 24/7
- Procesa jobs inmediatamente cuando se encolan

### Scheduler (actual):
- Se ejecuta **solo cuando es necesario**
- No consume recursos cuando no hay tareas
- Ejecuta en horarios específicos configurados
- Más eficiente para tareas diarias/periódicas

---

## 📞 Soporte

Si tienes problemas:
1. Revisa los logs en `storage/logs/`
2. Ejecuta el comando manualmente para ver errores
3. Verifica la configuración en `app/Console/Kernel.php`
