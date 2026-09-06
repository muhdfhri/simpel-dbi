<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel laporan — tabel inti sistem, satu baris per laporan Desa.
     * Sesuai SCHEMA.md Section 4. Index berat untuk filter dashboard Kanwil.
     */
    public function up(): void
    {
        Schema::create('laporan', function (Blueprint $table) {
            $table->id();

            // Nomor tiket unik — di-generate via LaporanWorkflowService, format LP-2026-000123
            $table->string('kode_tiket', 30)->unique();

            $table->unsignedBigInteger('desa_id');

            // Kategori laporan sesuai SCHEMA.md + Bab VI PRD
            $table->string('kategori', 50);

            $table->string('judul', 150);
            $table->dateTime('tanggal_kejadian');
            $table->string('lokasi_detail', 100); // Dusun/Lingkungan, RT/RW
            $table->text('kronologi');
            $table->integer('estimasi_jumlah_orang')->nullable();

            // Status siklus laporan — sesuai MECHANISME.md Bab IV
            $table->string('status', 50)->default('diajukan');

            // Timestamp tiap tahap (nullable — diisi saat status berubah)
            $table->timestamp('submitted_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('followed_up_at')->nullable();
            $table->timestamp('resolved_at')->nullable();

            // SLA tracking — diisi job terjadwal CheckSlaBreaches (ARCHITECTURE.md)
            $table->boolean('sla_verifikasi_breached')->default(false);    // >48 jam
            $table->boolean('sla_tindak_lanjut_breached')->default(false); // >72 jam / >6 jam kritis
            $table->boolean('red_flag')->default(false);                   // ditampilkan di dashboard pimpinan

            $table->softDeletes(); // SCHEMA.md Section 7: soft delete untuk audit trail
            $table->timestamps();

            // Foreign key
            $table->foreign('desa_id')
                ->references('id')
                ->on('desa_binaan')
                ->restrictOnDelete(); // desa tidak bisa dihapus selama ada laporan

            // Index berat untuk filter multi-dimensi dashboard Kanwil (SCHEMA.md Section 4)
            $table->index('status');
            $table->index('desa_id');
            $table->index('kategori');
            $table->index('red_flag');
            $table->index('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan');
    }
};
