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
        Schema::create('pasien', function (Blueprint $table) {
            $table->string('pasien_user_id', 20)->primary();
            $table->enum('pasien_role', ['dokter', 'staf', 'pasien'])->default('pasien');
            $table->string('pasien_norm', 20)->unique('uq_pasien_norm');
            $table->enum('pasien_jeniskelamin', ['Laki-laki', 'Perempuan']);
            $table->date('pasien_tgllahir');
            $table->string('pasien_alamat', 255);
            $table->char('pasien_nobpjs', 13)->nullable()->unique('uq_pasien_nobpjs');
            $table->enum('pasien_goldarah', ['A', 'B', 'AB', 'O'])->nullable();
            $table->string('pasien_alergiobat', 255)->nullable();
            $table->unsignedTinyInteger('pasien_poli_id')->nullable();

            $table->index('pasien_poli_id', 'idx_pasien_poli');

            $table->foreign(['pasien_user_id', 'pasien_role'], 'fk_pasien_user')
                ->references(['user_id', 'user_role'])
                ->on('user')
                ->onDelete('cascade');

            $table->foreign('pasien_poli_id', 'fk_pasien_poli')
                ->references('poli_id')
                ->on('poli')
                ->onUpdate('cascade')
                ->nullOnDelete();
        });

        DB::statement("ALTER TABLE pasien ADD CONSTRAINT ck_pasien_role CHECK (pasien_role = 'pasien')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pasien');
    }
};
