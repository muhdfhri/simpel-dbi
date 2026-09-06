<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel kegiatan_pembinaan — input mandiri PIMPASA, independen dari laporan Desa.
     * Sesuai SCHEMA.md Section 4.
     */
    public function up(): void
    {
        Schema::create('kegiatan_pembinaan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pimpasa_id'); // FK → users (PIMPASA yang mencatat)
            $table->unsignedBigInteger('desa_id');    // FK → desa_binaan (desa sasaran)

            $table->string('jenis_pembinaan', 50);

            $table->date('tanggal');
            $table->integer('jumlah_peserta');
            $table->text('ringkasan_materi');

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('pimpasa_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();

            $table->foreign('desa_id')
                ->references('id')
                ->on('desa_binaan')
                ->restrictOnDelete();

            // Index dipakai modul Rekapitulasi Pembinaan PIMPASA (SCHEMA.md Section 4)
            $table->index(['pimpasa_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan_pembinaan');
    }
};
