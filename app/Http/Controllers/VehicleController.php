<?php

namespace App\Http\Controllers;

use App\Http\Requests\VehicleRequest;
use App\Models\Branch;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\QueryBuilderRequest;

class VehicleController extends Controller
{
    public function index(Request $request): Response
    {
        $vehicles = QueryBuilderRequest::for(Vehicle::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('category_id'),
                AllowedFilter::exact('branch_id'),
                AllowedFilter::exact('fuel_type'),
                AllowedFilter::partial('make'),
                AllowedFilter::partial('model'),
                AllowedFilter::partial('plate_number'),
            ])
            ->allowedSorts(['make', 'model', 'year', 'mileage', 'created_at'])
            ->with(['category', 'branch', 'media'])
            ->withTrashed($request->boolean('trashed'))
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Vehicles/Index', [
            'vehicles'   => $vehicles,
            'categories' => VehicleCategory::active()->get(['id', 'name']),
            'branches'   => Branch::active()->get(['id', 'name', 'city']),
            'filters'    => $request->query(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Vehicles/Form', [
            'categories' => VehicleCategory::active()->get(),
            'branches'   => Branch::active()->get(['id', 'name', 'city']),
        ]);
    }

    public function store(VehicleRequest $request): RedirectResponse
    {
        $vehicle = Vehicle::create($request->validated());

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $vehicle->addMedia($photo)->toMediaCollection('photos');
            }
        }

        if ($request->hasFile('thumbnail')) {
            $vehicle->addMedia($request->file('thumbnail'))->toMediaCollection('thumbnail');
        }

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Vehicle created successfully.');
    }

    public function show(Vehicle $vehicle): Response
    {
        $vehicle->load(['category', 'branch', 'media', 'damages', 'maintenance' => fn($q) => $q->latest()->limit(5)]);

        return Inertia::render('Vehicles/Show', [
            'vehicle'    => $vehicle,
            'activeRental' => $vehicle->activeRental()->with('customer.user')->first(),
            'recentRentals'=> $vehicle->rentals()->with('customer.user')->latest()->limit(5)->get(),
        ]);
    }

    public function edit(Vehicle $vehicle): Response
    {
        return Inertia::render('Vehicles/Form', [
            'vehicle'    => $vehicle->load('media'),
            'categories' => VehicleCategory::active()->get(),
            'branches'   => Branch::active()->get(['id', 'name', 'city']),
        ]);
    }

    public function update(VehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $vehicle->update($request->validated());

        if ($request->hasFile('thumbnail')) {
            $vehicle->clearMediaCollection('thumbnail');
            $vehicle->addMedia($request->file('thumbnail'))->toMediaCollection('thumbnail');
        }

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $vehicle->addMedia($photo)->toMediaCollection('photos');
            }
        }

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Vehicle updated successfully.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->delete();
        return redirect()->route('vehicles.index')
            ->with('success', 'Vehicle deleted successfully.');
    }

    public function updateStatus(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $request->validate(['status' => 'required|in:available,reserved,rented,maintenance,retired']);
        $vehicle->update(['status' => $request->status]);
        return back()->with('success', 'Vehicle status updated.');
    }
}
