<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('uomConversions')->get();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => 'required|unique:products',
            'name' => 'required',
            'base_unit' => 'required',
            'conversions' => 'array',
            'conversions.*.unit_name' => 'required',
            'conversions.*.multiplier' => 'required|numeric|min:0.0001',
        ]);

        DB::transaction(function () use ($validated) {
            $product = Product::create([
                'sku' => $validated['sku'],
                'name' => $validated['name'],
                'base_unit' => $validated['base_unit'],
                'current_stock' => 0,
                'current_hpp' => 0,
            ]);

            if (!empty($validated['conversions'])) {
                $product->uomConversions()->createMany($validated['conversions']);
            }
        });

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $product->load('uomConversions');
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku' => 'required|unique:products,sku,' . $product->id,
            'name' => 'required',
            'base_unit' => 'required',
            'conversions' => 'array',
            'conversions.*.unit_name' => 'required',
            'conversions.*.multiplier' => 'required|numeric|min:0.0001',
        ]);

        DB::transaction(function () use ($validated, $product) {
            $product->update([
                'sku' => $validated['sku'],
                'name' => $validated['name'],
                'base_unit' => $validated['base_unit'],
            ]);

            $product->uomConversions()->delete();
            if (!empty($validated['conversions'])) {
                $product->uomConversions()->createMany($validated['conversions']);
            }
        });

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
