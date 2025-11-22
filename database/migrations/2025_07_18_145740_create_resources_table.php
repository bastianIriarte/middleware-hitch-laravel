<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable()->collation('utf8mb4_general_ci');
            $table->string('slug')->unique()->collation('utf8mb4_general_ci');
            $table->string('integrations_table')->collation('utf8mb4_general_ci');
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('show_user')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('resources');
    }
}
