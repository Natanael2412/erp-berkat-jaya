<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penjualan, kartu stok, dan transaksi kas non-penjualan.
 *
 * - Total penjualan tidak disimpan; diturunkan dari SUM(qty * unit_price) item.
 * - `hpp_at_sale` = HPP per satuan dasar saat transaksi (snapshot untuk jurnal HPP & void).
 * - `stock_movements` adalah kartu stok: setiap perubahan stok tercatat dengan saldo berjalan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 30)->unique();
            $table->dateTime('sale_date');
            $table->foreignId('cashier_id')->constrained('users')->restrictOnDelete();
            $table->enum('payment_method', ['cash', 'transfer', 'qris']);
            $table->decimal('amount_paid', 18, 2);
            $table->enum('status', ['completed', 'void'])->default('completed');
            $table->dateTime('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('void_reason')->nullable();
            $table->timestamps();

            $table->index('sale_date');
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('product_unit_id')->constrained('product_units')->restrictOnDelete();
            $table->decimal('conversion_factor', 15, 4);
            $table->decimal('qty', 15, 4); // dalam satuan jual
            $table->decimal('unit_price', 18, 2); // harga jual per satuan jual
            $table->decimal('hpp_at_sale', 18, 4); // HPP per satuan dasar
            $table->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->dateTime('movement_date');
            $table->enum('type', ['opening', 'purchase', 'purchase_return', 'sale', 'sale_void']);
            $table->nullableMorphs('source');
            $table->decimal('qty_in', 15, 4)->default(0);
            $table->decimal('qty_out', 15, 4)->default(0);
            $table->decimal('unit_cost', 18, 4); // biaya per satuan dasar pada mutasi ini
            $table->decimal('balance_qty', 15, 4);
            $table->decimal('balance_hpp', 18, 4);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number', 30)->unique();
            $table->date('transaction_date');
            $table->enum('type', ['capital', 'drawing', 'expense']); // setoran modal, prive, beban
            $table->foreignId('cash_account_id')->constrained('accounts')->restrictOnDelete();
            $table->decimal('amount', 18, 2);
            $table->string('description');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
