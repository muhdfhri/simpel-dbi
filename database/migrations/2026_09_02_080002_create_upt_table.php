<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel upt — Master 11 Satuan Kerja UPT Imigrasi Sumatera Utara.
     * Sesuai SCHEMA.md Section 2.
     */
    public function up(): void
    {
        Schema::create('upt', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);  // mis. "Kantor Imigrasi Kelas I Medan"
            $table->string('tipe', 50);
            $table->unsignedBigInteger('wilayah_id'); // FK ke wilayah_administratif
            $table->timestamps();

            $table->foreign('wilayah_id')
                ->references('id')
                ->on('wilayah_administratif')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upt');
    }
};
