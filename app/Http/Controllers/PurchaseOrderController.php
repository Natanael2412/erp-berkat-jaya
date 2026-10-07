<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PoItem;
use App\Models\Product;
use App\Models\Journal;
use App\Models\JournalLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $pos = PurchaseOrder::with('items.product')->latest()->get();
        return view('purchase_orders.index', compact('pos'));
    }

    public function create()
    {
        $products = Product::with('uomConversions')->get();
        return view('purchase_orders.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_name' => 'required',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.uom_id' => 'required|exists:product_uom_conversions,id',
            'items.*.qty_ordered' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $total_amount = 0;
            foreach ($validated['items'] as $item) {
                $total_amount += ($item['qty_ordered'] * $item['unit_price']);
            }

            $po = PurchaseOrder::create([
                'supplier_name' => $validated['supplier_name'],
                'status' => 'pending',
                'total_amount' => $total_amount,
            ]);

            foreach ($validated['items'] as $item) {
                $po->items()->create($item);
            }
        });

        return redirect()->route('purchase-orders.index')->with('success', 'PO created.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('items.product', 'items.uom');
        return view('purchase_orders.show', compact('purchaseOrder'));
    }

    public function receive(Request $request, $id)
    {
        $po = PurchaseOrder::with('items.uom')->findOrFail($id);
        
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.qty_received' => 'required|numeric|min:0',
            'items.*.qty_retur' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $po) {
            $allCompleted = true;
            $anyReceived = false;
            $totalNilaiMasuk = 0;

            foreach ($validated['items'] as $itemId => $data) {
                $poItem = $po->items()->where('id', $itemId)->lockForUpdate()->first();
                if (!$poItem) continue;

                $received = $data['qty_received'];
                $retur = $data['qty_retur'];

                $poItem->update([
                    'qty_received' => $poItem->qty_received + $received,
                    'qty_retur' => $poItem->qty_retur + $retur,
                ]);

                if ($poItem->qty_received < $poItem->qty_ordered) {
                    $allCompleted = false;
                }
                
                if ($received > 0) {
                    $anyReceived = true;
                    // Konversi UoM
                    $multiplier = $poItem->uom->multiplier;
                    $qtyBaseUnit = $received * $multiplier;
                    
                    $product = Product::where('id', $poItem->product_id)->lockForUpdate()->first();
                    
                    // Kalkulasi HPP Moving Average
                    // Nilai stok lama
                    $oldStockValue = $product->current_stock * $product->current_hpp;
                    // Nilai masuk (di PO unit_price adalah harga per UoM)
                    $incomingValue = $received * $poItem->unit_price;
                    $totalNilaiMasuk += $incomingValue;
                    
                    $newTotalStock = $product->current_stock + $qtyBaseUnit;
                    $newHpp = ($newTotalStock > 0) ? (($oldStockValue + $incomingValue) / $newTotalStock) : $product->current_hpp;

                    $product->update([
                        'current_stock' => $newTotalStock,
                        'current_hpp' => $newHpp,
                    ]);
                }
            }

            if ($allCompleted) {
                $po->update(['status' => 'komplit']);
            } else if ($anyReceived) {
                $po->update(['status' => 'parsial']);
            }

            if ($totalNilaiMasuk > 0) {
                // Jurnal: (Debit) Persediaan Barang, (Kredit) Utang Dagang
                $journal = Journal::create([
                    'reference_type' => 'PO',
                    'reference_id' => $po->id,
                    'date' => now(),
                    'description' => 'Penerimaan Barang PO #' . $po->id,
                ]);

                JournalLine::insert([
                    [
                        'journal_id' => $journal->id,
                        'account_code' => '1140', // Asumsi Persediaan Barang
                        'debit_amount' => $totalNilaiMasuk,
                        'credit_amount' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'journal_id' => $journal->id,
                        'account_code' => '2110', // Asumsi Utang Dagang
                        'debit_amount' => 0,
                        'credit_amount' => $totalNilaiMasuk,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]);
            }
        });

        return redirect()->route('purchase-orders.show', $po->id)->with('success', 'Penerimaan barang berhasil disimpan.');
    }
}
