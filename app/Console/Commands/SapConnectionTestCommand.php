<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SapServiceLayerService;
use Illuminate\Support\Facades\Config;

class SapConnectionTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sap:test-connection {--items : Test items endpoint} {--debug : Show debug information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar la conexión con SAP Service Layer';

    protected $sapService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SapServiceLayerService $sapService)
    {
        parent::__construct();
        $this->sapService = $sapService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔄 Probando conexión con SAP Service Layer...');
        
        // Mostrar configuración si se solicita debug
        if ($this->option('debug')) {
            $this->showConfiguration();
        }
        
        try {
            // Test de autenticación
            $this->info('📡 Autenticando con SAP...');
            $authenticated = $this->sapService->authenticate();
            
            if (!$authenticated) {
                $this->error('❌ Error de autenticación');
                $this->showTroubleshooting();
                return 1;
            }
            
            $this->info('✅ Autenticación exitosa');
            
            // Test de conexión básica usando endpoint válido
            $this->info('🔍 Probando conexión básica...');
            $connected = $this->sapService->isConnected();
            
            if (!$connected) {
                $this->error('❌ Error de conexión');
                return 1;
            }
            
            $this->info('✅ Conexión establecida');
            
            // Test de información básica
            $this->info('🏢 Obteniendo información básica...');
            try {
                // Obtener información de entidades disponibles
                $entities = $this->sapService->get('/', []);
                $this->info('✅ Entidades disponibles obtenidas');
                
                if ($this->option('debug') && isset($entities['value'])) {
                    $this->line('🔍 Primeras entidades encontradas:');
                    $count = 0;
                    foreach ($entities['value'] as $entity) {
                        if ($count >= 5) break;
                        $name = $entity['name'] ?? 'N/A';
                        $this->line("  - {$name}");
                        $count++;
                    }
                }
            } catch (\Exception $e) {
                $this->warn('⚠️ No se pudieron obtener entidades: ' . $e->getMessage());
            }
            
            // Test específico de artículos si se solicita
            if ($this->option('items')) {
                $this->info('📦 Probando endpoint de artículos...');
                
                try {
                    $items = $this->sapService->get('/Items', [
                        '$top' => 3,
                        '$select' => 'ItemCode,ItemName,InventoryItem'
                    ]);
                    
                    $this->info('✅ Endpoint de artículos funcionando');
                    
                    if (!empty($items['value'])) {
                        $this->line('📋 Artículos encontrados:');
                        foreach ($items['value'] as $item) {
                            $itemCode = $item['ItemCode'] ?? 'N/A';
                            $itemName = $item['ItemName'] ?? 'N/A';
                            $inventoryItem = $item['InventoryItem'] ?? 'N/A';
                            $this->line("  - {$itemCode}: {$itemName} (Inventario: {$inventoryItem})");
                        }
                    } else {
                        $this->warn('⚠️ No se encontraron artículos en la base de datos');
                    }
                    
                } catch (\Exception $e) {
                    $this->error('❌ Error en endpoint de artículos: ' . $e->getMessage());
                    return 1;
                }
            }
            
            // Test adicional: obtener grupos de artículos
            $this->info('🏷️ Probando grupos de artículos...');
            try {
                $groups = $this->sapService->get('/ItemGroups', [
                    '$top' => 3,
                    '$select' => 'Number,GroupName'
                ]);
                
                if (!empty($groups['value'])) {
                    $this->info('✅ Grupos de artículos obtenidos');
                    if ($this->option('debug')) {
                        foreach ($groups['value'] as $group) {
                            $number = $group['Number'] ?? 'N/A';
                            $name = $group['GroupName'] ?? 'N/A';
                            $this->line("  - {$number}: {$name}");
                        }
                    }
                } else {
                    $this->warn('⚠️ No se encontraron grupos de artículos');
                }
            } catch (\Exception $e) {
                $this->warn('⚠️ Error obteniendo grupos: ' . $e->getMessage());
            }
            
            // Mostrar información de la sesión
            $sessionId = $this->sapService->getSessionInfo();
            if ($sessionId) {
                $maskedSessionId = substr($sessionId, 0, 8) . '...';
                $this->info("🔑 Session ID: {$maskedSessionId}");
            }
            
            $this->info('🎉 Todas las pruebas pasaron exitosamente');
            
            // Cerrar sesión
            $this->sapService->logout();
            $this->info('👋 Sesión cerrada');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Error durante las pruebas: ' . $e->getMessage());
            
            if ($this->option('debug')) {
                $this->line('🔍 Stack trace:');
                $this->line($e->getTraceAsString());
            }
            
            $this->showTroubleshooting();
            return 1;
        }
    }

    /**
     * Mostrar configuración actual
     */
    private function showConfiguration()
    {
        $this->line('🔧 Configuración actual:');
        $this->line('  - URL: ' . config('sap.service_layer.base_url', 'NO CONFIGURADA'));
        $this->line('  - Database: ' . config('sap.database', 'NO CONFIGURADA'));
        $this->line('  - Username: ' . config('sap.username', 'NO CONFIGURADO'));
        $this->line('  - Password: ' . (config('sap.password') ? '***' : 'NO CONFIGURADA'));
        $this->line('  - Timeout: ' . config('sap.service_layer.timeout', 30) . 's');
        $this->line('  - Verify SSL: ' . (config('sap.service_layer.verify_ssl', false) ? 'SÍ' : 'NO'));
        $this->line('');
    }

    /**
     * Mostrar guía de solución de problemas
     */
    private function showTroubleshooting()
    {
        $this->line('');
        $this->line('🛠️ Solución de problemas:');
        $this->line('');
        $this->line('1. Verificar que SAP Service Layer esté ejecutándose:');
        $this->line('   curl -k ' . config('sap.service_layer.base_url', 'http://localhost:50000') . '/b1s/v1/');
        $this->line('');
        $this->line('2. Verificar credenciales en el archivo .env:');
        $this->line('   SAP_SERVICE_LAYER_URL=http://192.168.100.9:50000');
        $this->line('   SAP_DATABASE=SBODemoUS');
        $this->line('   SAP_USERNAME=manager');
        $this->line('   SAP_PASSWORD=tu_password');
        $this->line('');
        $this->line('3. Verificar conectividad de red al servidor SAP');
        $this->line('');
        $this->line('4. Revisar logs en storage/logs/laravel.log para más detalles');
        $this->line('');
    }
}