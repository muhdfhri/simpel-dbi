<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel laporan_verifikasi — hasil kerja PIMPASA, 1:1 dengan laporan.
     * Sesuai SCHEMA.md Section 4.
     */
    public function up(): void
    {
        Schema::create('laporan_verifikasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('laporan_id')->unique(); // 1:1 dengan laporan
            $table->unsignedBigInteger('pimpasa_id');           // FK → users (PIMPASA yang verifikasi)

            // Checklist validitas — boolean array (JSON) sesuai SCHEMA.md
            $table->json('checklist_validitas');

            // Catatan — wajib diisi jika keputusan bukan "diverifikasi"
            $table->text('catatan')->nullable();

            $table->string('keputusan', 50);

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('laporan_id')
                ->references('id')
                ->on('laporan')
                ->cascadeOnDelete();

            $table->foreign('pimpasa_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_verifikasi');
    }
};
