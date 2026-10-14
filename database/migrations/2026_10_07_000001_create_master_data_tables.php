<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Master data.
 *
 * Normalisasi:
 * - `units` adalah master satuan (Pcs, Meter, Roll, Dus) agar tidak ada teks bebas yang duplikat.
 * - `product_units` menyimpan konversi ke satuan dasar + harga jual per satuan.
 *   Satuan dasar produk selalu punya baris dengan conversion_factor = 1.
 * - `products.current_stock` & `current_hpp` adalah cache (denormalisasi terkontrol);
 *   sumber kebenarannya adalah tabel `stock_movements` (kartu stok).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 50);
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 150);
            $table->string('phone', 30)->nullable();
            $table->string('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 50)->unique();
            $table->string('name', 150);
            $table->foreignId('base_unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('min_stock', 15, 4)->default(0);
            $table->decimal('current_stock', 15, 4)->default(0);
            $table->decimal('current_hpp', 18, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('conversion_factor', 15, 4);
            $table->decimal('selling_price', 18, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'unit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_units');
        Schema::dropIfExists('products');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('units');
    }
};
