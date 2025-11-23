# Sistema de Extracciones Programadas de Watts

## ✅ Sistema Configurado

Se ha creado un sistema completo de extracciones programadas que reemplaza el queue worker constante con un scheduler inteligente.

---

## 🎯 ¿Qué se creó?

### 1. **Comando Laravel** (`watts:extract`)
- Ejecuta extracciones de Watts con validaciones automáticas
- Soporta modo síncrono y asíncrono
- Logs detallados de cada ejecución

### 2. **Scheduler Configurado**
- Extracción diaria automática a las 2:00 AM
- Notificaciones por email en caso de fallo
- Logs en `storage/logs/scheduler.log`

### 3. **Scripts de Instalación**
- `install-scheduler.ps1` - Configura Windows Task Scheduler
- `install-queue-service.ps1` - Configura queue worker como servicio (opcional)
- `start-queue-worker.bat/ps1` - Inicia worker manualmente

### 4. **Documentación**
- `SCHEDULER_GUIDE.md` - Guía completa del sistema
- `QUEUE_WORKER_GUIDE.md` - Guía del queue worker (si lo necesitas)

---

## 🚀 Instalación en 3 Pasos

### Paso 1: Configurar el Scheduler
```powershell
# Abre PowerShell como Administrador
cd D:\xampp\htdocs\APP_PRODUCTIVAS\HITCH\WATTS\middleware_hitch
.\install-scheduler.ps1
```

### Paso 2: Configurar Queue Worker (solo si usas --async)
```powershell
# Abre PowerShell como Administrador
.\install-queue-service.ps1
```

### Paso 3: ¡Listo!
El sistema ejecutará automáticamente las extracciones todos los días a las 2 AM.

---

## 📋 Uso Diario

### Ejecutar manualmente:
```bash
php artisan watts:extract --type=all --async
```

### Ver qué está programado:
```bash
php artisan schedule:list
```

### Ver logs:
```bash
type storage\logs\scheduler.log
type storage\logs\laravel.log
```

---

## 🔧 Personalizar Horarios

Edita `app/Console/Kernel.php`:

```php
// Cambiar hora de ejecución
$schedule->command('watts:extract --type=all --async')
    ->dailyAt('03:00'); // Cambiar a 3 AM

// Ejecutar cada 6 horas
$schedule->command('watts:extract --type=products --async')
    ->everySixHours();

// Solo días laborables
$schedule->command('watts:extract --type=all --async')
    ->weekdays()
    ->at('02:00');
```

---

## ✅ Ventajas

✅ **Eficiente** - Solo se ejecuta cuando es necesario
✅ **Automático** - No requiere intervención manual
✅ **Validado** - Verifica empresa, FTP y tipos de archivo
✅ **Monitoreado** - Logs detallados y notificaciones
✅ **Flexible** - Fácil de personalizar horarios

---

## 📞 Comandos Útiles

```bash
# Ver ayuda del comando
php artisan watts:extract --help

# Ejecutar todas las extracciones
php artisan watts:extract --type=all --async

# Ejecutar solo productos
php artisan watts:extract --type=products --async

# Ejecutar síncronamente (espera a que termine)
php artisan watts:extract --type=products

# Ver tareas programadas
php artisan schedule:list

# Ejecutar scheduler manualmente (para testing)
php artisan schedule:run

# Ver jobs fallidos
php artisan queue:failed

# Reintentar jobs fallidos
php artisan queue:retry all
```

---

## 📖 Documentación Completa

Lee `SCHEDULER_GUIDE.md` para información detallada sobre:
- Configuración avanzada
- Personalización de horarios
- Monitoreo y troubleshooting
- Notificaciones por email
- Gestión del Task Scheduler

---

## 🎉 ¡Todo Listo!

El sistema está configurado y listo para usar. Las extracciones se ejecutarán automáticamente todos los días a las 2 AM.
