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
        Schema::create('antrean', function (Blueprint $table) {
            $table->bigIncrements('antrean_id');
            $table->string('antrean_nomor', 20); // e.g. "UM-001", "KIA-005"
            $table->unsignedInteger('antrean_angka'); // Sequential integer 1, 2, 3...
            $table->string('antrean_pasien_user_id', 20);
            $table->string('antrean_dokter_user_id', 20);
            $table->unsignedTinyInteger('antrean_poli_id');
            $table->unsignedInteger('antrean_jadwal_id')->nullable();
            $table->date('antrean_tanggal');
            $table->enum('antrean_jenis', ['online', 'walkin'])->default('online');
            $table->enum('antrean_status', [
                'menunggu_checkin', // Booking online, belum tiba di klinik
                'menunggu',          // Sudah check-in / walk-in, siap dipanggil di ruang tunggu
                'dipanggil',         // Sedang dipanggil petugas loket / TV display
                'sedang_diperiksa',  // Sedang diperiksa dokter di ruang poli
                'selesai',           // Selesai diperiksa & rekam medis tersimpan
                'dilewati',          // Dilewati karena tidak hadir saat dipanggil (bisa recall)
                'batal',             // Dibatalkan
            ])->default('menunggu_checkin');
            $table->string('antrean_qr_code', 64)->unique('uq_antrean_qr_code');
            $table->timestamp('antrean_waktu_checkin')->nullable();
            $table->timestamp('antrean_waktu_panggil')->nullable();
            $table->timestamp('antrean_waktu_selesai')->nullable();
            $table->unsignedTinyInteger('antrean_panggilan_ke')->default(0);
            $table->text('antrean_catatan')->nullable();
            $table->timestamps();

            // Foreign Keys
            $table->foreign('antrean_pasien_user_id', 'fk_antrean_pasien')
                ->references('pasien_user_id')
                ->on('pasien')
                ->onDelete('cascade');

            $table->foreign('antrean_dokter_user_id', 'fk_antrean_dokter')
                ->references('dokter_user_id')
                ->on('dokter')
                ->onDelete('cascade');

            $table->foreign('antrean_poli_id', 'fk_antrean_poli')
                ->references('poli_id')
                ->on('poli')
                ->onDelete('cascade');

            $table->foreign('antrean_jadwal_id', 'fk_antrean_jadwal')
                ->references('jadwal_id')
                ->on('jadwal')
                ->onDelete('set null');

            // Composite Indexes for high performance queue queries
            $table->index(['antrean_poli_id', 'antrean_tanggal', 'antrean_status'], 'idx_antrean_poli_tgl_status');
            $table->index(['antrean_dokter_user_id', 'antrean_tanggal'], 'idx_antrean_dokter_tgl');
            $table->index(['antrean_pasien_user_id', 'antrean_tanggal'], 'idx_antrean_pasien_tgl');
            $table->unique(['antrean_poli_id', 'antrean_tanggal', 'antrean_angka'], 'uq_antrean_poli_tgl_angka');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antrean');
    }
};
