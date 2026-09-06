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
        // 1. Backfill kategori_id for existing records before dropping column
        if (Schema::hasColumn('laporan', 'kategori') && Schema::hasColumn('laporan', 'kategori_id')) {
            $categories = DB::table('kategori_laporans')->pluck('id', 'kode')->toArray();

            foreach ($categories as $kode => $id) {
                DB::table('laporan')
                    ->whereNull('kategori_id')
                    ->where('kategori', $kode)
                    ->update(['kategori_id' => $id]);
            }

            // 2. Drop index and column 'kategori'
            Schema::table('laporan', function (Blueprint $table) {
                // Drop index on kategori column if it exists
                $table->dropIndex(['kategori']);
                $table->dropColumn('kategori');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan', function (Blueprint $table) {
            $table->string('kategori', 50)->nullable()->after('kategori_id');
            $table->index('kategori');
        });
    }
};
