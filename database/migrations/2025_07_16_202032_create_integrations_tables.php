<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntegrationsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    private function getTables()
    {
        return [
            'articles',
            'business_partners',
            'deposits',
            'goods_issues',
            'goods_receipts',
            'journal_entries',
            'purchase_delivery_notes',
            'purchase_orders',
            'reserve_invoices',
            'returns',
            'stock_transfer',
            'stock_transfer_requests',
        ];
    }

    public function up()
    {
        $tables = $this->getTables() ?? [];

        foreach ($tables as $table) {
            Schema::create("integrations_$table", function (Blueprint $table) {
                $table->id();
                $table->string('service_name', 255)->nullable()->collation('utf8mb4_general_ci');
                $table->string('origin', 255)->nullable()->collation('utf8mb4_general_ci');
                $table->string('destiny', 255)->nullable()->collation('utf8mb4_general_ci');
                $table->longText('create_body')->nullable()->collation('utf8mb4_general_ci');
                $table->longText('request_body')->nullable()->collation('utf8mb4_general_ci');
                $table->string('code', 255)->nullable()->collation('utf8mb4_general_ci');
                $table->longText('message')->nullable()->collation('utf8mb4_general_ci');
                $table->longText('response')->nullable()->collation('utf8mb4_general_ci');
                $table->unsignedBigInteger('status_integration_id')->nullable();
                $table->integer('attempts')->default(0);
                $table->string('caller_method', 255)->nullable()->collation('utf8mb4_general_ci');
                $table->unsignedBigInteger('user_created')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->unsignedBigInteger('user_updated')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->unsignedBigInteger('user_deleted')->nullable();
                $table->tinyInteger('deleted')->default(0);
                $table->timestamp('deleted_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tables = $this->getTables() ?? [];

        foreach ($tables as $table) {
            Schema::dropIfExists("integrations_$table");
        }
    }
}
