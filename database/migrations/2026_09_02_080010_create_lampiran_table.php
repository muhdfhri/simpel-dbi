<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel lampiran — polymorphic attachment untuk laporan, tindak_lanjut, dan kegiatan_pembinaan.
     * Sesuai SCHEMA.md Section 4.
     *
     * Polymorphic relations:
     *   - Laporan           → lampiranable_type = 'App\Models\Laporan'
     *   - LaporanTindakLanjut → lampiranable_type = 'App\Models\LaporanTindakLanjut'
     *   - KegiatanPembinaan → lampiranable_type = 'App\Models\KegiatanPembinaan'
     */
    public function up(): void
    {
        Schema::create('lampiran', function (Blueprint $table) {
            $table->id();

            // Polymorphic columns
            $table->unsignedBigInteger('lampiranable_id');
            $table->string('lampiranable_type', 150);

            $table->string('path', 255);              // storage path
            $table->string('nama_file_asli', 255);    // nama file original
            $table->string('tipe_file', 20);
            $table->integer('ukuran_bytes');          // validasi max 5MB di backend

            // Siapa yang mengupload
            $table->unsignedBigInteger('uploaded_by');

            $table->timestamp('created_at')->useCurrent(); // tidak ada updated_at — immutable

            // Polymorphic index
            $table->index(['lampiranable_id', 'lampiranable_type']);

            $table->foreign('uploaded_by')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lampiran');
    }
};
