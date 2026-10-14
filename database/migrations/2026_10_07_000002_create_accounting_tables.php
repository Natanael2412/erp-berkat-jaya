<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Inti akuntansi (double-entry).
 *
 * - `journal_lines.account_id` memakai restrictOnDelete: akun yang sudah dipakai
 *   tidak boleh dihapus agar buku besar tidak pernah hilang.
 * - Jurnal bersifat immutable; koreksi dilakukan dengan jurnal pembalik (`reversal_of_id`).
 * - `document_sequences` memberi nomor dokumen berurutan yang aman dari race condition
 *   (baris di-lock saat penomoran).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 100);
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->enum('normal_balance', ['debit', 'credit']);
            $table->boolean('is_cash')->default(false); // Kas / Bank, dipakai untuk pilihan pembayaran
            $table->timestamps();
        });

        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 10);
            $table->string('period', 6); // YYYYMM
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['prefix', 'period']);
        });

        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->string('journal_number', 30)->unique();
            $table->date('journal_date');
            $table->string('description');
            $table->nullableMorphs('source');
            $table->foreignId('reversal_of_id')->nullable()->constrained('journals')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('journal_date');
        });

        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained('journals')->restrictOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);
            $table->string('memo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journals');
        Schema::dropIfExists('document_sequences');
        Schema::dropIfExists('accounts');
    }
};
