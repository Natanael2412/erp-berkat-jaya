<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Schema;

/** Landing page publik toko listrik (etalase jualan untuk pelanggan). */
class LandingController extends Controller
{
    public function __invoke()
    {
        if (auth()->check() && ! request()->has('store')) {
            return redirect()->to(auth()->user()->homeRoute());
        }

        $featured = collect();
        if (Schema::hasTable('products')) {
            try {
                $featured = Product::query()
                    ->where('is_active', true)
                    ->with(['baseUnit', 'activeUnits.unit'])
                    ->whereHas('activeUnits', fn ($q) => $q->where('selling_price', '>', 0))
                    ->orderByDesc('current_stock')
                    ->limit(8)
                    ->get()
                    ->map(function (Product $p) {
                        $base = $p->activeUnits->firstWhere('unit_id', $p->base_unit_id);

                        return (object) [
                            'name' => $p->name,
                            'sku' => $p->sku,
                            'unit' => $p->baseUnit->name ?? 'Pcs',
                            'price' => (float) ($base->selling_price ?? 0),
                            'others' => $p->activeUnits->where('unit_id', '!=', $p->base_unit_id)
                                ->map(fn ($u) => $u->unit->name.' '.rp($u->selling_price))->values()->all(),
                            'in_stock' => (float) $p->current_stock > 0,
                        ];
                    });
            } catch (\Throwable $e) {
                $featured = collect();
            }
        }

        return view('welcome', [
            'featured' => $featured,
            'store' => config('store'),
        ]);
    }
}
