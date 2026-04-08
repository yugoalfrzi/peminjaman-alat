<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->datetime('tanggal_pinjam')->change();
            $table->datetime('tanggal_kembali_rencana')->change();
            $table->datetime('tanggal_kembali_aktual')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->date('tanggal_pinjam')->change();
            $table->date('tanggal_kembali_rencana')->change();
            $table->date('tanggal_kembali_aktual')->nullable()->change();
        });
    }
};
