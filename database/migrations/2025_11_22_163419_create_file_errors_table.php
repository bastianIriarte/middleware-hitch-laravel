<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFileErrorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_errors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('file_type_id')->nullable();
            $table->unsignedBigInteger('file_log_id')->nullable();
            $table->string('error_type')->default('validation');
            $table->text('error_message');
            $table->text('error_details')->nullable();
            $table->integer('line_number')->nullable();
            $table->text('record_data')->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->unsignedBigInteger('user_created')->nullable();
            $table->unsignedBigInteger('user_updated')->nullable();
            $table->unsignedBigInteger('user_deleted')->nullable();
            $table->softDeletes();
            $table->timestamps();



            $table->foreign('company_id')
                ->references('id')->on('companies')
                ->onDelete('cascade')
                ->onUpdate('restrict');

            $table->foreign('file_log_id')
                ->references('id')->on('file_logs')
                ->onDelete('cascade')
                ->onUpdate('restrict');

            $table->foreign('file_type_id')
                ->references('id')->on('file_types')
                ->onDelete('cascade')
                ->onUpdate('restrict');

            $table->foreign('user_created')->references('id')->on('users')->onDelete('set null');
            $table->foreign('user_updated')->references('id')->on('users')->onDelete('set null');
            $table->foreign('user_deleted')->references('id')->on('users')->onDelete('set null');

            $table->index(['company_id', 'file_type_id', 'severity']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_errors');
    }
}
