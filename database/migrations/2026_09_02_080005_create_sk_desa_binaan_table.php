<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel sk_desa_binaan — Penetapan SK Desa Binaan Imigrasi.
     * Sesuai SCHEMA.md Section 2 & Bab VI Master Data Kanwil poin 4.
     */
    public function up(): void
    {
        Schema::create('sk_desa_binaan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('desa_id');
            $table->string('nomor_sk', 100);
            $table->date('tanggal_sk');
            $table->string('file_sk_path', 255); // upload PDF

            $table->timestamps();

            $table->foreign('desa_id')
                ->references('id')
                ->on('desa_binaan')
                ->restrictOnDelete(); // SK tidak boleh hilang jika desa masih ada
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sk_desa_binaan');
    }
};
