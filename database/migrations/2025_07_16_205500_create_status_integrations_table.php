<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusIntegrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('status_integrations', function (Blueprint $table) {
            $table->id();
            $table->string('status')->nullable()->collation('utf8mb4_general_ci');
            $table->string('description')->nullable()->collation('utf8mb4_general_ci');
            $table->string('badge')->nullable()->collation('utf8mb4_general_ci');
            $table->string('icon')->nullable()->collation('utf8mb4_general_ci');
            $table->unsignedBigInteger('user_created')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('user_updated')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('user_deleted')->nullable();
            $table->tinyInteger('deleted')->default(0);
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('status_integrations');
    }
}
