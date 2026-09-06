<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel laporan_tindak_lanjut — hasil kerja UPT, 1:1 dengan laporan.
     * Sesuai SCHEMA.md Section 4.
     */
    public function up(): void
    {
        Schema::create('laporan_tindak_lanjut', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('laporan_id')->unique(); // 1:1 dengan laporan

            // Nomor registrasi penanganan — auto-generate via LaporanWorkflowService
            $table->string('nomor_registrasi', 30)->unique();

            // Seksi penanggung jawab di UPT
            $table->string('seksi_penanggung_jawab', 50);

            // Bentuk intervensi yang dilakukan
            $table->string('bentuk_intervensi', 50);

            $table->text('ringkasan_hasil');
            $table->string('status_akhir', 50);

            // Staf UPT yang menginput tindak lanjut ini
            $table->unsignedBigInteger('ditangani_oleh');

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('laporan_id')
                ->references('id')
                ->on('laporan')
                ->cascadeOnDelete();

            $table->foreign('ditangani_oleh')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_tindak_lanjut');
    }
};
