<?php
// Test simple para verificar el endpoint
echo "Testing file upload endpoint\n";
echo "URL: http://127.0.0.1:8000/api/files/upload/CUSTOMER_001/PRODUCTS\n";
echo "\nEl endpoint espera:\n";
echo "- Content-Type: multipart/form-data\n";
echo "- Campo 'file': archivo CSV/TXT/XLSX/XLS\n";
echo "- Campos opcionales: records_count, rejected_count\n";
echo "\nTu curl actual envía JSON, pero debería enviar un archivo.\n";
