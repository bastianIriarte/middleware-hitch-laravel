@echo off
echo Iniciando Queue Worker para Watts Extraction...
echo.
echo Procesando colas:
echo - watts_extraction_customers
echo - watts_extraction_products
echo - watts_extraction_vendors
echo - watts_extraction_sellout
echo - default
echo.
echo Presiona Ctrl+C para detener el worker
echo.

cd /d D:\xampp\htdocs\APP_PRODUCTIVAS\HITCH\WATTS\middleware_hitch

php artisan queue:work --queue=watts_extraction_customers,watts_extraction_products,watts_extraction_vendors,watts_extraction_sellout,default --tries=3 --timeout=600 --sleep=3 --max-jobs=1000 --max-time=3600

pause
