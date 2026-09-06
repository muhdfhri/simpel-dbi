<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel wilayah_administratif — hierarki Provinsi → Kab/Kota → Kecamatan → Desa.
     * Self-relation via parent_id. Sesuai SCHEMA.md Section 2.
     */
    public function up(): void
    {
        Schema::create('wilayah_administratif', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable(); // null untuk level provinsi
            $table->string('nama', 150);
            $table->string('level', 30);
            $table->string('kode_kemendagri', 20)->unique(); // standar Kemendagri
            $table->timestamps();

            // Self-relation
            $table->foreign('parent_id')
                ->references('id')
                ->on('wilayah_administratif')
                ->nullOnDelete();

            // Index sesuai SCHEMA.md
            $table->index(['parent_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wilayah_administratif');
    }
};
