Write-Host "Iniciando Queue Worker para Watts Extraction..." -ForegroundColor Green
Write-Host ""
Write-Host "Procesando colas:" -ForegroundColor Yellow
Write-Host "  - watts_extraction_customers" -ForegroundColor Cyan
Write-Host "  - watts_extraction_products" -ForegroundColor Cyan
Write-Host "  - watts_extraction_vendors" -ForegroundColor Cyan
Write-Host "  - watts_extraction_sellout" -ForegroundColor Cyan
Write-Host "  - default" -ForegroundColor Cyan
Write-Host ""
Write-Host "Presiona Ctrl+C para detener el worker" -ForegroundColor Red
Write-Host ""

Set-Location "D:\xampp\htdocs\APP_PRODUCTIVAS\HITCH\WATTS\middleware_hitch"

php artisan queue:work `
    --queue=watts_extraction_customers,watts_extraction_products,watts_extraction_vendors,watts_extraction_sellout,default `
    --tries=3 `
    --timeout=600 `
    --sleep=3 `
    --max-jobs=1000 `
    --max-time=3600 `
    --verbose
