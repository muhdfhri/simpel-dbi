<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah FK dari users ke desa_binaan dan upt.
     * Harus dilakukan setelah desa_binaan & upt dibuat (tidak bisa dalam satu migration
     * karena circular reference: users butuh desa_binaan, desa_binaan butuh users).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('desa_id')
                ->references('id')
                ->on('desa_binaan')
                ->nullOnDelete();

            $table->foreign('upt_id')
                ->references('id')
                ->on('upt')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['desa_id']);
            $table->dropForeign(['upt_id']);
        });
    }
};
