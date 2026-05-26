<?php

namespace App\Http\Controllers;

use App\Models\VehicleCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;

class VehicleCategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('vehicles/Categories', [
            'categories' => VehicleCategory::withCount('vehicles')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'              => 'required|string|max:100',
            'description'       => 'nullable|string',
            'seat_count'        => 'required|integer|min:2|max:12',
            'luggage_count'     => 'required|integer|min:0|max:10',
            'transmission'      => 'required|in:automatic,manual,both',
            'base_price_per_day'=> 'required|numeric|min:0',
            'extra_km_price'    => 'required|numeric|min:0',
            'free_km_per_day'   => 'required|integer|min:0',
            'is_active'         => 'boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);
        VehicleCategory::create($data);

        return back()->with('success', 'Category created.');
    }

    public function update(Request $request, VehicleCategory $vehicleCategory): RedirectResponse
    {
        $data = $request->validate([
            'name'              => 'required|string|max:100',
            'base_price_per_day'=> 'required|numeric|min:0',
            'extra_km_price'    => 'required|numeric|min:0',
            'free_km_per_day'   => 'required|integer|min:0',
            'is_active'         => 'boolean',
        ]);

        $vehicleCategory->update($data);

        return back()->with('success', 'Category updated.');
    }

    public function destroy(VehicleCategory $vehicleCategory): RedirectResponse
    {
        if ($vehicleCategory->vehicles()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete category with active vehicles.']);
        }

        $vehicleCategory->delete();

        return back()->with('success', 'Category deleted.');
    }
}
