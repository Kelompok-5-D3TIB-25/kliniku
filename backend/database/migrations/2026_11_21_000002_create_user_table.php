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
        Schema::create('user', function (Blueprint $table) {
            $table->string('user_id', 20)->primary();
            $table->enum('user_role', ['dokter', 'staf', 'pasien']);
            $table->string('user_nama', 150);
            $table->char('user_nik', 16)->unique('uq_user_nik');
            $table->string('user_nowa', 15);
            $table->string('user_email', 100)->nullable()->unique('uq_user_email');
            $table->string('user_password', 255);
            $table->enum('user_status', ['aktif', 'nonaktif'])->default('aktif');
            $table->dateTime('user_createdat')->useCurrent();
            $table->dateTime('user_updatedat')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['user_id', 'user_role'], 'uq_user_idrole');
            $table->index('user_role', 'idx_user_role');
            $table->index('user_nama', 'idx_user_nama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};
