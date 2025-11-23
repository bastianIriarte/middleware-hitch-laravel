<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProtocolToFtpConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ftp_configs', function (Blueprint $table) {
            $table->enum('protocol', ['ftp', 'sftp'])->default('ftp')->after('company_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ftp_configs', function (Blueprint $table) {
            $table->dropColumn('protocol');
        });
    }
}
