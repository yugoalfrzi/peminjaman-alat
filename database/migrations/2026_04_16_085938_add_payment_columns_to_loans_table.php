<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->boolean('is_paid')->default(false)->after('denda');
            $table->timestamp('paid_at')->nullable()->after('is_paid');
            $table->string('payment_method')->nullable()->after('paid_at');
        });
    }

    public function down()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['is_paid', 'paid_at', 'payment_method']);
        });
    }
};