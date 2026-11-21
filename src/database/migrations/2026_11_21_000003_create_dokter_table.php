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
        Schema::create('dokter', function (Blueprint $table) {
            $table->string('dokter_user_id', 20)->primary();
            $table->enum('dokter_role', ['dokter', 'staf', 'pasien'])->default('dokter');
            $table->string('dokter_spesialisasi', 100)->nullable();
            $table->string('dokter_sip', 50)->unique('uq_dokter_sip');
            $table->unsignedSmallInteger('dokter_kuotaharian')->default(25);
            $table->unsignedTinyInteger('dokter_poli_id');

            $table->index('dokter_poli_id', 'idx_dokter_poli');

            $table->foreign(['dokter_user_id', 'dokter_role'], 'fk_dokter_user')
                ->references(['user_id', 'user_role'])
                ->on('user')
                ->onDelete('cascade');

            $table->foreign('dokter_poli_id', 'fk_dokter_poli')
                ->references('poli_id')
                ->on('poli')
                ->onUpdate('cascade');
        });

        DB::statement("ALTER TABLE dokter ADD CONSTRAINT ck_dokter_role CHECK (dokter_role = 'dokter')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokter');
    }
};
