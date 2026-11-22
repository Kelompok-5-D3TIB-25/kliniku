<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jadwal', function (Blueprint $table) {
            $table->increments('jadwal_id');
            $table->string('jadwal_dokter_user_id', 20);
            $table->unsignedTinyInteger('jadwal_hari');
            $table->time('jadwal_jammulai');
            $table->time('jadwal_jamselesai');

            $table->unique(['jadwal_dokter_user_id', 'jadwal_hari', 'jadwal_jammulai'], 'uq_jadwal_dokterharijam');

            $table->foreign('jadwal_dokter_user_id', 'fk_jadwal_dokter')
                ->references('dokter_user_id')
                ->on('dokter')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        DB::statement('ALTER TABLE jadwal ADD CONSTRAINT ck_jadwal_hari CHECK (jadwal_hari BETWEEN 1 AND 7)');
        DB::statement('ALTER TABLE jadwal ADD CONSTRAINT ck_jadwal_jam CHECK (jadwal_jamselesai > jadwal_jammulai)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal');
    }
};
