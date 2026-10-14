<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Unit;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function __construct(private ProductService $service) {}

    public function index(Request $request)
    {
        $products = Product::with(['baseUnit', 'units.unit'])
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%")))
            ->when($request->filter === 'low', fn ($q) => $q->whereColumn('current_stock', '<=', 'min_stock'))
            ->when($request->filter === 'inactive', fn ($q) => $q->where('is_active', false), fn ($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->paginate(20)->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.form', [
            'product' => new Product(['is_active' => true, 'min_stock' => 0]),
            'units' => Unit::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $product = $this->service->create($data);

        return redirect()->route('products.show', $product)->with('success', 'Produk ditambahkan.');
    }

    public function show(Product $product)
    {
        $product->load(['baseUnit', 'units.unit']);
        $movements = $product->stockMovements()->with('source')->latest('id')->paginate(25);

        return view('products.show', compact('product', 'movements'));
    }

    public function edit(Product $product)
    {
        $product->load(['baseUnit', 'units.unit']);

        return view('products.form', [
            'product' => $product,
            'units' => Unit::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, $product);
        $this->service->update($product, $data);

        return redirect()->route('products.show', $product)->with('success', 'Produk diperbarui.');
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $baseUnitId = $product?->base_unit_id ?? $request->input('base_unit_id');

        $rules = [
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($product?->id)],
            'name' => ['required', 'string', 'max:150'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'base_selling_price' => ['required', 'numeric', 'min:0'],
            'units' => ['array'],
            'units.*.unit_id' => ['required', 'distinct', 'exists:units,id', Rule::notIn([$baseUnitId])],
            'units.*.conversion_factor' => ['required', 'numeric', 'gt:1'],
            'units.*.selling_price' => ['required', 'numeric', 'min:0'],
        ];

        if (! $product) {
            $rules += [
                'base_unit_id' => ['required', 'exists:units,id'],
                'opening_qty' => ['nullable', 'numeric', 'min:0'],
                'opening_cost' => ['required_with:opening_qty', 'nullable', 'numeric', 'min:0'],
                'opening_date' => ['nullable', 'date', 'before_or_equal:today'],
            ];
        }

        return $request->validate($rules, [
            'units.*.unit_id.not_in' => 'Satuan konversi tidak boleh sama dengan satuan dasar.',
            'units.*.unit_id.distinct' => 'Satuan konversi tidak boleh ganda.',
            'units.*.conversion_factor.gt' => 'Isi konversi harus lebih dari 1 satuan dasar.',
            'opening_cost.required_with' => 'HPP awal wajib diisi bila ada stok awal.',
        ], [
            'base_selling_price' => 'harga jual satuan dasar',
            'opening_qty' => 'stok awal',
            'opening_cost' => 'HPP awal',
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}
