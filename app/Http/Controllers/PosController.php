<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Journal;
use App\Models\JournalLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::where('current_stock', '>', 0)->get();
        return view('pos.index', compact('products'));
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:Tunai,Transfer,QRIS',
            'cart' => 'required|array',
            'cart.*.product_id' => 'required|exists:products,id',
            'cart.*.qty' => 'required|numeric|min:0.01',
            'cart.*.selling_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $total_amount = 0;
                $total_hpp = 0;

                // 1. Create Sale Header
                $sale = Sale::create([
                    'user_id' => auth()->id(),
                    'total_amount' => 0, // diupdate nanti
                    'payment_method' => $validated['payment_method'],
                ]);

                // 2. Process Cart Items with Pessimistic Locking
                foreach ($validated['cart'] as $item) {
                    // Pessimistic lock pada row product
                    $product = Product::where('id', $item['product_id'])->lockForUpdate()->first();

                    if (!$product) {
                        throw new Exception("Product ID {$item['product_id']} tidak ditemukan.");
                    }

                    if ($item['qty'] > $product->current_stock) {
                        throw new Exception("Stok untuk produk {$product->name} tidak mencukupi. (Stok saat ini: {$product->current_stock})");
                    }

                    $subtotal = $item['qty'] * $item['selling_price'];
                    $subtotal_hpp = $item['qty'] * $product->current_hpp;

                    $total_amount += $subtotal;
                    $total_hpp += $subtotal_hpp;

                    // Buat detail transaksi
                    $sale->items()->create([
                        'product_id' => $product->id,
                        'qty' => $item['qty'],
                        'selling_price' => $item['selling_price'],
                        'hpp_at_sale' => $product->current_hpp,
                    ]);

                    // Potong stok
                    $product->update([
                        'current_stock' => $product->current_stock - $item['qty']
                    ]);
                }

                $sale->update(['total_amount' => $total_amount]);

                // 3. Penjurnalan (Double Entry)
                $journal = Journal::create([
                    'reference_type' => 'Sale',
                    'reference_id' => $sale->id,
                    'date' => now(),
                    'description' => 'Penjualan POS #' . $sale->id,
                ]);

                // Jurnal 1: Kas bertambah, Pendapatan bertambah
                JournalLine::insert([
                    [
                        'journal_id' => $journal->id,
                        'account_code' => '1110', // Asumsi: Kas
                        'debit_amount' => $total_amount,
                        'credit_amount' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'journal_id' => $journal->id,
                        'account_code' => '4110', // Asumsi: Pendapatan Penjualan
                        'debit_amount' => 0,
                        'credit_amount' => $total_amount,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    // Jurnal 2: HPP bertambah, Persediaan Barang berkurang
                    [
                        'journal_id' => $journal->id,
                        'account_code' => '5110', // Asumsi: HPP
                        'debit_amount' => $total_hpp,
                        'credit_amount' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'journal_id' => $journal->id,
                        'account_code' => '1140', // Asumsi: Persediaan Barang
                        'debit_amount' => 0,
                        'credit_amount' => $total_hpp,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]);
            });

            return response()->json(['success' => true, 'message' => 'Transaksi berhasil!']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
