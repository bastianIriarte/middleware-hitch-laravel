<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFtpConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ftp_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('host');
            $table->integer('port')->default(21);
            $table->string('username');
            $table->string('password');
            $table->string('root_path')->default('/');
            $table->boolean('ssl')->default(false);
            $table->boolean('passive')->default(true);
            $table->integer('timeout')->default(30);
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('user_created')->nullable();
            $table->unsignedBigInteger('user_updated')->nullable();
            $table->unsignedBigInteger('user_deleted')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_created')->references('id')->on('users')->onDelete('set null');
            $table->foreign('user_updated')->references('id')->on('users')->onDelete('set null');
            $table->foreign('user_deleted')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ftp_configs');
    }
}
