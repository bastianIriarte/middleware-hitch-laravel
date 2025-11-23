# Guía de Configuración del Queue Worker

## Opción 1: Ejecutar Manualmente (Desarrollo/Testing)

### Windows (CMD):
```cmd
start-queue-worker.bat
```

### Windows (PowerShell):
```powershell
.\start-queue-worker.ps1
```

### Comando directo:
```bash
php artisan queue:work --queue=watts_extraction_customers,watts_extraction_products,watts_extraction_vendors,watts_extraction_sellout,default --tries=3 --timeout=600 --sleep=3
```

---

## Opción 2: Servicio de Windows (Producción)

### Instalación:

1. **Abre PowerShell como Administrador**
   - Haz clic derecho en PowerShell
   - Selecciona "Ejecutar como administrador"

2. **Ejecuta el instalador:**
   ```powershell
   cd D:\xampp\htdocs\APP_PRODUCTIVAS\HITCH\WATTS\middleware_hitch
   .\install-queue-service.ps1
   ```

3. **El script automáticamente:**
   - Descarga NSSM (Non-Sucking Service Manager)
   - Crea el servicio "LaravelQueueWorker_Watts"
   - Configura logs automáticos
   - Configura reinicio automático si falla

### Gestión del Servicio:

#### Iniciar el servicio:
```powershell
nssm start LaravelQueueWorker_Watts
```

#### Detener el servicio:
```powershell
nssm stop LaravelQueueWorker_Watts
```

#### Ver estado:
```powershell
nssm status LaravelQueueWorker_Watts
```

#### Reiniciar el servicio:
```powershell
nssm restart LaravelQueueWorker_Watts
```

#### Eliminar el servicio:
```powershell
nssm remove LaravelQueueWorker_Watts confirm
```

### Usando el Administrador de Servicios de Windows:

1. Presiona `Win + R`
2. Escribe `services.msc` y presiona Enter
3. Busca "Laravel Queue Worker - Watts Extraction"
4. Haz clic derecho para Iniciar/Detener/Reiniciar

---

## Logs del Worker

### Logs de Laravel:
```
storage/logs/laravel.log
```

### Logs del servicio (si usas NSSM):
```
storage/logs/queue-worker-stdout.log
storage/logs/queue-worker-stderr.log
```

---

## Monitoreo

### Ver jobs en proceso:
```bash
php artisan queue:monitor watts_extraction_customers,watts_extraction_products,watts_extraction_vendors,watts_extraction_sellout,default
```

### Ver jobs fallidos:
```bash
php artisan queue:failed
```

### Reintentar jobs fallidos:
```bash
# Reintentar todos
php artisan queue:retry all

# Reintentar uno específico
php artisan queue:retry [job-id]
```

### Limpiar jobs fallidos:
```bash
php artisan queue:flush
```

---

## Parámetros del Worker

- `--queue`: Colas a procesar (en orden de prioridad)
- `--tries=3`: Número máximo de reintentos por job
- `--timeout=600`: Tiempo máximo de ejecución por job (10 minutos)
- `--sleep=3`: Segundos de espera cuando no hay jobs
- `--max-jobs=1000`: Reiniciar worker después de procesar 1000 jobs
- `--max-time=3600`: Reiniciar worker después de 1 hora

---

## Colas Configuradas

1. **watts_extraction_customers** - Extracción de clientes
2. **watts_extraction_products** - Extracción de productos
3. **watts_extraction_vendors** - Extracción de vendedores
4. **watts_extraction_sellout** - Extracción de sell out
5. **default** - Cola por defecto para otros jobs

---

## Troubleshooting

### El worker no procesa jobs:
1. Verifica que el worker esté corriendo
2. Revisa los logs en `storage/logs/laravel.log`
3. Verifica la configuración de la base de datos en `.env`

### Jobs se quedan en estado "processing":
```bash
# Reiniciar el worker
nssm restart LaravelQueueWorker_Watts

# O si es manual, presiona Ctrl+C y vuelve a iniciarlo
```

### Cambios en el código no se reflejan:
```bash
# Reiniciar el worker para cargar el nuevo código
nssm restart LaravelQueueWorker_Watts
```

---

## Recomendaciones

✅ **Producción**: Usa el servicio de Windows con NSSM
✅ **Desarrollo**: Usa el script manual (.bat o .ps1)
✅ **Monitoreo**: Revisa regularmente los logs
✅ **Actualizaciones**: Reinicia el worker después de cambios en el código
