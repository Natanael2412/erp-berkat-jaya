<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::withCount('productUnits')->orderBy('name')->get();

        return view('units.index', compact('units'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'alpha_dash', 'unique:units,code'],
            'name' => ['required', 'string', 'max:50'],
            'allows_decimal' => ['nullable', 'boolean'],
        ]);
        $data['code'] = strtoupper($data['code']);
        $data['allows_decimal'] = $request->boolean('allows_decimal');

        Unit::create($data);

        return back()->with('success', 'Satuan ditambahkan.');
    }

    public function update(Request $request, Unit $unit)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('units', 'code')->ignore($unit->id)],
            'name' => ['required', 'string', 'max:50'],
            'allows_decimal' => ['nullable', 'boolean'],
        ]);
        $data['code'] = strtoupper($data['code']);
        $data['allows_decimal'] = $request->boolean('allows_decimal');

        $unit->update($data);

        return back()->with('success', 'Satuan diperbarui.');
    }
}
