<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kegiatan_pembinaan', function (Blueprint $table) {
            if (!Schema::hasColumn('kegiatan_pembinaan', 'judul')) {
                $table->string('judul', 255)->after('desa_id');
            }
            if (!Schema::hasColumn('kegiatan_pembinaan', 'status')) {
                $table->string('status', 50)->default('selesai')->after('jumlah_peserta');
            }
            if (!Schema::hasColumn('kegiatan_pembinaan', 'lokasi')) {
                $table->string('lokasi', 255)->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kegiatan_pembinaan', function (Blueprint $table) {
            $table->dropColumn(['judul', 'status', 'lokasi']);
        });
    }
};
