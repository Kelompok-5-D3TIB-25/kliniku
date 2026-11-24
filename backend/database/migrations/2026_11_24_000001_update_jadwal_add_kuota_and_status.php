<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('jadwal', function (Blueprint $table) {
            $table->unsignedSmallInteger('jadwal_kuota')->default(25)->after('jadwal_jamselesai');
            $table->enum('jadwal_status', ['aktif', 'cuti', 'tutup'])->default('aktif')->after('jadwal_kuota');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal', function (Blueprint $table) {
            $table->dropColumn(['jadwal_kuota', 'jadwal_status']);
        });
    }
};
