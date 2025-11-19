<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PromoController extends Controller
{
    public function index()
    {
        $promos = Promo::orderBy('code')->paginate(12);
        return view('admin.promos.index', compact('promos'));
    }

    public function create()
    {
        return view('admin.promos.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:60', 'alpha_dash', 'unique:promos,code'],
            'type' => ['required', Rule::in(['percent', 'nominal'])],
            'value' => ['required', 'integer', 'min:1'],
            'usage_limit_total' => ['nullable', 'integer', 'min:0'],
            'usage_limit_per_user' => ['nullable', 'integer', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', Rule::when($request->filled('starts_at'), 'after_or_equal:starts_at')],
            'is_active' => ['nullable', Rule::in(['0','1'])],
        ]);

        $data['is_active'] = (int)($request->input('is_active', 1));
        $data['code'] = strtoupper($data['code']);

        Promo::create($data);

        return redirect()->route('admin.promos.index')->with('status', 'Promo dibuat.');
    }

    public function edit(Promo $promo)
    {
        return view('admin.promos.edit', compact('promo'));
    }

    public function update(Request $request, Promo $promo)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:60', 'alpha_dash', Rule::unique('promos','code')->ignore($promo->id)],
            'type' => ['required', Rule::in(['percent', 'nominal'])],
            'value' => ['required', 'integer', 'min:1'],
            'usage_limit_total' => ['nullable', 'integer', 'min:0'],
            'usage_limit_per_user' => ['nullable', 'integer', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', Rule::when($request->filled('starts_at'), 'after_or_equal:starts_at')],
            'is_active' => ['nullable', Rule::in(['0','1'])],
        ]);

        $data['is_active'] = (int)($request->input('is_active', $promo->is_active));
        $data['code'] = strtoupper($data['code']);

        $promo->update($data);

        return redirect()->route('admin.promos.index')->with('status', 'Promo diperbarui.');
    }

    public function destroy(Promo $promo)
    {
        $promo->delete();
        return redirect()->route('admin.promos.index')->with('status', 'Promo dihapus.');
    }
}