<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambahkan kolom tanggal_selesai dan buat desa_id nullable
        Schema::table('kegiatan_pembinaan', function (Blueprint $table) {
            if (!Schema::hasColumn('kegiatan_pembinaan', 'tanggal_selesai')) {
                $table->date('tanggal_selesai')->nullable()->after('tanggal');
            }
            $table->unsignedBigInteger('desa_id')->nullable()->change();
        });

        // 2. Buat tabel pivot kegiatan_pembinaan_desa
        if (!Schema::hasTable('kegiatan_pembinaan_desa')) {
            Schema::create('kegiatan_pembinaan_desa', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('kegiatan_pembinaan_id');
                $table->unsignedBigInteger('desa_binaan_id');
                $table->timestamps();

                $table->foreign('kegiatan_pembinaan_id')
                    ->references('id')
                    ->on('kegiatan_pembinaan')
                    ->onDelete('cascade');

                $table->foreign('desa_binaan_id')
                    ->references('id')
                    ->on('desa_binaan')
                    ->onDelete('cascade');

                $table->unique(['kegiatan_pembinaan_id', 'desa_binaan_id'], 'keg_desa_unique');
            });
        }

        // 3. Migrasikan data desa_id yang sudah ada di tabel kegiatan_pembinaan ke tabel pivot
        $existingKegiatan = DB::table('kegiatan_pembinaan')->whereNotNull('desa_id')->get();
        foreach ($existingKegiatan as $kegiatan) {
            DB::table('kegiatan_pembinaan_desa')->updateOrInsert(
                [
                    'kegiatan_pembinaan_id' => $kegiatan->id,
                    'desa_binaan_id' => $kegiatan->desa_id,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatan_pembinaan_desa');

        Schema::table('kegiatan_pembinaan', function (Blueprint $table) {
            if (Schema::hasColumn('kegiatan_pembinaan', 'tanggal_selesai')) {
                $table->dropColumn('tanggal_selesai');
            }
        });
    }
};
