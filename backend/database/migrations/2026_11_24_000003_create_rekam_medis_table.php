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
        Schema::create('rekam_medis', function (Blueprint $table) {
            $table->bigIncrements('rekam_medis_id');
            $table->unsignedBigInteger('rekam_medis_antrean_id')->nullable()->unique('uq_rm_antrean');
            $table->string('rekam_medis_pasien_user_id', 20);
            $table->string('rekam_medis_dokter_user_id', 20);
            $table->unsignedTinyInteger('rekam_medis_poli_id');
            $table->date('rekam_medis_tanggal');

            // Encrypted Sensitive Medical Fields (AES-256-CBC)
            // Sesuai mandat UU Perlindungan Data Pribadi (UU PDP No. 27/2022)
            $table->text('rekam_medis_keluhan');
            $table->text('rekam_medis_diagnosa');
            $table->text('rekam_medis_tanda_vital')->nullable(); // Tensi, Nadi, Suhu, BB, TB
            $table->text('rekam_medis_tindakan')->nullable();
            $table->text('rekam_medis_resep_obat')->nullable();
            $table->timestamps();

            // Foreign Keys
            $table->foreign('rekam_medis_antrean_id', 'fk_rm_antrean')
                ->references('antrean_id')
                ->on('antrean')
                ->onDelete('set null');

            $table->foreign('rekam_medis_pasien_user_id', 'fk_rm_pasien')
                ->references('pasien_user_id')
                ->on('pasien')
                ->onDelete('cascade');

            $table->foreign('rekam_medis_dokter_user_id', 'fk_rm_dokter')
                ->references('dokter_user_id')
                ->on('dokter')
                ->onDelete('cascade');

            $table->foreign('rekam_medis_poli_id', 'fk_rm_poli')
                ->references('poli_id')
                ->on('poli')
                ->onDelete('cascade');

            // Indexes
            $table->index(['rekam_medis_pasien_user_id', 'rekam_medis_tanggal'], 'idx_rm_pasien_tgl');
            $table->index(['rekam_medis_dokter_user_id', 'rekam_medis_tanggal'], 'idx_rm_dokter_tgl');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekam_medis');
    }
};
