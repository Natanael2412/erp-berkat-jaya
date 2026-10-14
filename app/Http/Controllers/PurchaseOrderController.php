<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function __construct(private PurchaseService $service) {}

    public function index(Request $request)
    {
        $orders = PurchaseOrder::with(['supplier', ...PurchaseOrder::SUMMARY_RELATIONS])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('po_number', 'like', "%{$s}%")
                ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$s}%"))))
            ->latest('id')
            ->paginate(20)->withQueryString();

        return view('purchase_orders.index', compact('orders'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)
            ->with(['activeUnits.unit'])
            ->orderBy('name')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'label' => "{$p->sku} — {$p->name}",
                'hpp' => (float) $p->current_hpp,
                'units' => $p->activeUnits->map(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->unit->name,
                    'factor' => (float) $u->conversion_factor,
                ])->values(),
            ]);

        return view('purchase_orders.create', [
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(),
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'order_date' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_unit_id' => ['required', 'distinct', 'exists:product_units,id'],
            'items.*.qty_ordered' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ], [
            'items.required' => 'Tambahkan minimal satu barang.',
            'items.*.product_unit_id.distinct' => 'Produk dengan satuan yang sama tidak boleh dimasukkan dua kali.',
        ]);

        $po = $this->service->createOrder($data);

        return redirect()->route('purchase-orders.show', $po)->with('success', "PO {$po->po_number} dibuat.");
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier', 'creator',
            'items.productUnit.product', 'items.productUnit.unit',
            ...PurchaseOrder::SUMMARY_RELATIONS,
            'receipts.items.purchaseOrderItem.productUnit.product', 'receipts.receiver', 'receipts.journal',
            'returns.items.purchaseOrderItem.productUnit.product', 'returns.journal',
            'payments.account', 'payments.journal',
        ]);

        return view('purchase_orders.show', [
            'po' => $purchaseOrder,
            'cashAccounts' => Account::where('is_cash', true)->orderBy('code')->get(),
        ]);
    }

    public function receiveForm(PurchaseOrder $purchaseOrder)
    {
        if (! $purchaseOrder->canReceive()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)->with('error', 'PO ini tidak dalam status menunggu barang.');
        }
        $purchaseOrder->load(['supplier', 'items.productUnit.product', 'items.productUnit.unit', 'items.receiptItems']);

        return view('purchase_orders.receive', ['po' => $purchaseOrder]);
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        $data = $request->validate([
            'receipt_date' => ['required', 'date', 'before_or_equal:today', 'after_or_equal:' . $purchaseOrder->order_date->toDateString()],
            'notes' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array'],
            'items.*.qty_received' => ['nullable', 'numeric', 'min:0'],
            'items.*.qty_rejected' => ['nullable', 'numeric', 'min:0'],
        ]);

        $gr = $this->service->receive($purchaseOrder, $data['receipt_date'], $data['items'], $data['notes'] ?? null);

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', "Penerimaan {$gr->gr_number} disimpan. Stok, HPP, dan jurnal telah diperbarui.");
    }

    public function returnForm(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'items.productUnit.product', 'items.productUnit.unit', ...PurchaseOrder::SUMMARY_RELATIONS]);

        if ($purchaseOrder->items->every(fn ($i) => $i->qtyReturnable() <= 0)) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)->with('error', 'Belum ada barang diterima yang bisa diretur.');
        }

        return view('purchase_orders.return', ['po' => $purchaseOrder]);
    }

    public function returnGoods(Request $request, PurchaseOrder $purchaseOrder)
    {
        $data = $request->validate([
            'return_date' => ['required', 'date', 'before_or_equal:today'],
            'reason' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array'],
            'items.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $return = $this->service->returnGoods($purchaseOrder, $data['return_date'], $data['items'], $data['reason']);

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', "Retur {$return->return_number} disimpan. Stok dan utang telah dikurangi.");
    }

    public function markAsPaid(Request $request, PurchaseOrder $purchaseOrder)
    {
        $data = $request->validate([
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'account_id' => ['required', 'exists:accounts,id'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $payment = $this->service->pay($purchaseOrder, $data['payment_date'], (float) $data['amount'], (int) $data['account_id'], $data['notes'] ?? null);

        return back()->with('success', "Pembayaran {$payment->payment_number} sebesar " . rp($payment->amount) . ' dicatat.');
    }

    public function markAsComplete(PurchaseOrder $purchaseOrder)
    {
        $this->service->markAsComplete($purchaseOrder);

        return back()->with('success', 'PO ditutup. Sisa barang yang belum datang tidak akan diterima lagi.');
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        $this->service->cancel($purchaseOrder);

        return back()->with('success', 'PO dibatalkan.');
    }
}
