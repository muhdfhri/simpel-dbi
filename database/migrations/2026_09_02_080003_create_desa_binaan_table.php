<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel desa_binaan — Master 171 Desa Binaan Imigrasi.
     * pimpasa_id nullable: bisa kosong sebelum SK penugasan terbit.
     * Sesuai SCHEMA.md Section 2.
     */
    public function up(): void
    {
        Schema::create('desa_binaan', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->unsignedBigInteger('wilayah_id'); // FK → wilayah_administratif (level desa)
            $table->unsignedBigInteger('upt_id');     // FK → upt (UPT pembina wilayah)
            $table->unsignedBigInteger('pimpasa_id')->nullable(); // FK → users (PIMPASA yang ditugaskan)

            // Koordinat geospasial untuk pin peta Leaflet
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            // Status terkini — dihitung berkala oleh job, bukan input manual
            // Sesuai SCHEMA.md Section 7 Catatan: kalkulasi berkala dari laporan aktif
            $table->string('status_terkini', 50)->default('aman');

            $table->timestamps();

            // Foreign keys
            $table->foreign('wilayah_id')
                ->references('id')
                ->on('wilayah_administratif')
                ->restrictOnDelete();

            $table->foreign('upt_id')
                ->references('id')
                ->on('upt')
                ->restrictOnDelete();

            $table->foreign('pimpasa_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            // Index sesuai SCHEMA.md
            $table->index('upt_id');
            $table->index('pimpasa_id');
            $table->index('wilayah_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desa_binaan');
    }
};
