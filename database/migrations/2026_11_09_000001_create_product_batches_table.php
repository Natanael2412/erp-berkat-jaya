<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('goods_receipt_id')->nullable()->constrained('goods_receipts')->nullOnDelete();
            $table->foreignId('purchase_order_item_id')->nullable()->constrained('purchase_order_items')->nullOnDelete();
            $table->string('batch_sku', 80)->unique();
            $table->integer('initial_qty');
            $table->integer('remaining_qty');
            $table->decimal('unit_cost', 18, 4)->default(0);
            $table->date('received_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['product_id', 'remaining_qty']);
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreignId('product_batch_id')->nullable()->after('product_unit_id')->constrained('product_batches')->nullOnDelete();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('product_batch_id')->nullable()->after('product_id')->constrained('product_batches')->nullOnDelete();
        });

        // Kuantitas barang tetap menggunakan bilangan bulat (Integer), tidak perlu desimal
        DB::table('units')->update(['allows_decimal' => false]);

        // Backfill stok rol fisik terpisah untuk produk kabel yang sudah memiliki stok
        $rollProducts = DB::table('products')
            ->join('product_units', 'products.id', '=', 'product_units.product_id')
            ->join('units', 'product_units.unit_id', '=', 'units.id')
            ->whereRaw('UPPER(units.code) = ?', ['ROLL'])
            ->where('product_units.is_active', true)
            ->where('products.current_stock', '>', 0)
            ->select('products.id', 'products.sku', 'products.current_stock', 'products.current_hpp', 'product_units.conversion_factor')
            ->get();

        foreach ($rollProducts as $rp) {
            $capacity = max(1, (int) round((float) $rp->conversion_factor));
            $remaining = (int) round((float) $rp->current_stock);
            $seq = 1;
            while ($remaining > 0) {
                $chunk = min($capacity, $remaining);
                DB::table('product_batches')->insert([
                    'product_id' => $rp->id,
                    'batch_sku' => sprintf('%s-ROL%03d', $rp->sku, $seq++),
                    'initial_qty' => $capacity,
                    'remaining_qty' => $chunk,
                    'unit_cost' => $rp->current_hpp,
                    'received_date' => now()->toDateString(),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $remaining -= $chunk;
            }
        }
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_batch_id');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_batch_id');
        });

        Schema::dropIfExists('product_batches');
    }
};
