<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id'); // INT(10) NOT NULL AUTO_INCREMENT
            $table->string('name')->nullable()->collation('utf8mb4_general_ci');
            $table->string('email')->nullable()->collation('utf8mb4_general_ci');
            $table->string('username')->nullable()->collation('utf8mb4_general_ci');
            $table->string('rut', 15)->nullable()->collation('utf8mb4_general_ci');
            $table->string('mobile', 15)->nullable()->collation('utf8mb4_general_ci');
            $table->string('password')->nullable()->collation('utf8mb4_general_ci');
            $table->string('remember_token')->nullable()->collation('utf8mb4_general_ci');
            $table->string('connection_token')->nullable()->collation('utf8mb4_general_ci');
            $table->timestamp('last_entry')->nullable();
            $table->string('activation_token')->nullable()->collation('utf8mb4_general_ci');
            $table->unsignedBigInteger('profile_id')->default(3);
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('validate_password')->default(0);
            $table->unsignedBigInteger('user_created')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('user_updated')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('user_deleted')->nullable();
            $table->tinyInteger('deleted')->default(0);
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedBigInteger('user_confirmed')->nullable();
            $table->tinyInteger('account_confirmed')->default(0);
            $table->timestamp('account_confirmed_at')->nullable();
            $table->string('menu_type')->nullable()->collation('utf8mb4_general_ci');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
