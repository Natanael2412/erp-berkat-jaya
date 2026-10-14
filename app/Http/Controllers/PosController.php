<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Services\SalesService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    public function __construct(private SalesService $service) {}

    public function index()
    {
        $products = Product::where('is_active', true)
            ->with(['activeUnits.unit', 'baseUnit'])
            ->orderBy('name')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'sku' => $p->sku,
                'name' => $p->name,
                'stock' => (float) $p->current_stock,
                'base_unit' => $p->baseUnit->name,
                'units' => $p->activeUnits->map(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->unit->name,
                    'factor' => (float) $u->conversion_factor,
                    'price' => (float) $u->selling_price,
                ])->values(),
            ]);

        $todaySales = Sale::withTotal()
            ->where('cashier_id', auth()->id())
            ->where('status', 'completed')
            ->whereDate('sale_date', today())
            ->get();

        return view('pos.index', [
            'products' => $products,
            'todayCount' => $todaySales->count(),
            'todayTotal' => $todaySales->sum(fn ($s) => $s->total()),
        ]);
    }

    /**
     * Klien hanya mengirim product_unit_id + qty. Harga, HPP, dan total dihitung ulang di server.
     */
    public function checkout(Request $request)
    {
        $data = $request->validate([
            'payment_method' => ['required', Rule::in(array_keys(Sale::PAYMENT_METHODS))],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'cart' => ['required', 'array', 'min:1', 'max:100'],
            'cart.*.product_unit_id' => ['required', 'integer', 'exists:product_units,id'],
            'cart.*.qty' => ['required', 'numeric', 'gt:0', 'max:100000'],
        ], ['cart.required' => 'Keranjang masih kosong.']);

        try {
            $sale = $this->service->checkout($data['cart'], $data['payment_method'], (float) ($data['amount_paid'] ?? 0));
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first()], 422);
        }

        $sale->load('items');

        return response()->json([
            'message' => 'Transaksi berhasil.',
            'invoice_number' => $sale->invoice_number,
            'total' => $sale->total(),
            'change' => $sale->change(),
            'receipt_url' => route('pos.receipt', $sale),
        ]);
    }

    public function receipt(Sale $sale)
    {
        $user = auth()->user();
        if ($user->role === 'cashier' && $sale->cashier_id !== $user->id) {
            abort(403);
        }

        $sale->load(['items.productUnit.product', 'items.productUnit.unit', 'cashier']);

        return view('pos.receipt', compact('sale'));
    }
}
