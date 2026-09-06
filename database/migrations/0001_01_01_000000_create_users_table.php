<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Skema sesuai SCHEMA.md Section 3 — tabel users SIMPEL DBI.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('email', 150)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            // RBAC — role utama (selain permission Spatie yang granular)
            $table->string('role', 30);

            // Relasi kontekstual per role (nullable sesuai SCHEMA.md)
            // FK ke desa_binaan & upt ditambahkan setelah tabel tersebut dibuat
            $table->unsignedBigInteger('desa_id')->nullable();  // terisi jika role=desa
            $table->unsignedBigInteger('upt_id')->nullable();   // terisi jika role=upt

            // Field khusus PIMPASA
            $table->string('nip', 30)->nullable()->unique();    // wajib jika role=pimpasa
            $table->string('golongan', 50)->nullable();
            $table->string('kontak', 30)->nullable();           // nomor HP/WA

            // Status akun — nonaktifkan tanpa hapus histori
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Index sesuai SCHEMA.md
            $table->index('role');
            $table->index('desa_id');
            $table->index('upt_id');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
