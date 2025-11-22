<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateIntegrationsView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("DROP VIEW IF EXISTS integrations_view");
    
        DB::statement("
            CREATE OR REPLACE VIEW integrations_view AS
            SELECT 'integrations_articles' AS table_name, si.* FROM integrations_articles AS si
            UNION ALL
            SELECT 'integrations_business_partners' AS table_name, si.* FROM integrations_business_partners AS si
            UNION ALL
            SELECT 'integrations_deposits' AS table_name, si.* FROM integrations_deposits AS si
            UNION ALL
            SELECT 'integrations_goods_issues' AS table_name, si.* FROM integrations_goods_issues AS si
            UNION ALL
            SELECT 'integrations_purchase_delivery_notes' AS table_name, si.* FROM integrations_purchase_delivery_notes AS si
            UNION ALL
            SELECT 'integrations_journal_entries' AS table_name, si.* FROM integrations_journal_entries AS si
            UNION ALL
            SELECT 'integrations_purchase_orders' AS table_name, si.* FROM integrations_purchase_orders AS si
            UNION ALL
            SELECT 'integrations_reserve_invoices' AS table_name, si.* FROM integrations_reserve_invoices AS si
            UNION ALL
            SELECT 'integrations_returns' AS table_name, si.* FROM integrations_returns AS si
            UNION ALL
            SELECT 'integrations_stock_transfer' AS table_name, si.* FROM integrations_stock_transfer AS si
            UNION ALL
            SELECT 'integrations_stock_transfer_requests' AS table_name, si.* FROM integrations_stock_transfer_requests AS si;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS integrations_view");
    }
}
