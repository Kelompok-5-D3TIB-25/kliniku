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
        Schema::create('staf', function (Blueprint $table) {
            $table->string('staf_user_id', 20)->primary();
            $table->enum('staf_role', ['dokter', 'staf', 'pasien'])->default('staf');
            $table->string('staf_jabatan', 100);
            $table->string('staf_shiftnama', 30);
            $table->time('staf_shiftmulai');
            $table->time('staf_shiftselesai');

            $table->foreign(['staf_user_id', 'staf_role'], 'fk_staf_user')
                ->references(['user_id', 'user_role'])
                ->on('user')
                ->onDelete('cascade');
        });

        DB::statement("ALTER TABLE staf ADD CONSTRAINT ck_staf_role CHECK (staf_role = 'staf')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staf');
    }
};
