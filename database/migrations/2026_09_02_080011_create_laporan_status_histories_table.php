<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel laporan_status_histories — audit trail APPEND-ONLY.
     * Tidak boleh di-update atau di-delete dari aplikasi (SCHEMA.md Section 5).
     * Memenuhi kebutuhan Bab I poin "e": ketiadaan audit trail jadi masalah utama.
     */
    public function up(): void
    {
        Schema::create('laporan_status_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('laporan_id');

            $table->string('status_dari', 30)->nullable(); // null untuk histori pertama (submit)
            $table->string('status_ke', 30);

            $table->unsignedBigInteger('actor_id'); // siapa yang mengubah status
            $table->text('catatan')->nullable();

            // Hanya created_at — tidak ada updated_at karena baris ini immutable
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('laporan_id')
                ->references('id')
                ->on('laporan')
                ->cascadeOnDelete();

            $table->foreign('actor_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();

            $table->index('laporan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_status_histories');
    }
};
