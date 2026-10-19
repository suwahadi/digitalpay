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
        Schema::table('users', function (Blueprint $table) {
            // Cek kolom yang belum ada sebelum menambahkan
            if (!Schema::hasColumn('users', 'otp_code')) {
                $table->string('otp_code', 6)->nullable()->after('password');
            }
            
            if (!Schema::hasColumn('users', 'otp_expires_at')) {
                $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
            }
            
            // Tambahkan email_verified_at jika belum ada
            if (!Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('updated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['otp_code', 'otp_expires_at']);
            
            // Jangan drop email_verified_at dan status karena mungkin sudah ada sebelumnya
        });
    }
};
