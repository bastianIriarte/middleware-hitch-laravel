<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiConnectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_connections', function (Blueprint $table) {
            $table->id();
            $table->string('software')->nullable()->collation('utf8mb4_general_ci');
            $table->string('endpoint')->nullable()->collation('utf8mb4_general_ci');
            $table->string('database')->nullable()->collation('utf8mb4_general_ci');
            $table->string('username')->nullable()->collation('utf8mb4_general_ci');
            $table->string('password')->nullable()->collation('utf8mb4_general_ci');
            $table->string('api_key')->nullable()->collation('utf8mb4_general_ci');
            $table->tinyInteger('status')->default(0);
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
        Schema::dropIfExists('api_connections');
    }
}
