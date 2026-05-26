<?php

namespace App\Http\Controllers;

use App\Models\Damage;
use App\Models\ExtraService;
use App\Models\InsurancePlan;
use App\Models\Maintenance;
use App\Models\Promotion;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

class MaintenanceController extends Controller
{
    public function index(Request $request): Response
    {
        $records = Maintenance::with('vehicle')
            ->when($request->vehicle_id, fn($q) => $q->where('vehicle_id', $request->vehicle_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest('scheduled_date')
            ->paginate(15);

        return Inertia::render('maintenance/Index', [
            'records'  => $records,
            'vehicles' => Vehicle::all(['id', 'make', 'model', 'plate_number']),
            'filters'  => $request->only(['vehicle_id', 'status']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vehicle_id'     => 'required|uuid|exists:vehicles,id',
            'type'           => 'required|in:oil_change,tire_rotation,brake_service,inspection,repair,other',
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'cost'           => 'nullable|numeric|min:0',
            'scheduled_date' => 'required|date',
            'performed_by'   => 'nullable|string|max:255',
            'garage_name'    => 'nullable|string|max:255',
        ]);

        $maintenance = Maintenance::create($data);
        Vehicle::find($data['vehicle_id'])->update(['status' => 'maintenance']);

        return back()->with('success', 'Maintenance scheduled.');
    }

    public function complete(Request $request, Maintenance $maintenance): RedirectResponse
    {
        $request->validate(['completed_date' => 'required|date', 'cost' => 'nullable|numeric']);

        $maintenance->update([
            'status'         => 'completed',
            'completed_date' => $request->completed_date,
            'cost'           => $request->cost ?? $maintenance->cost,
            'mileage_at_service' => $request->mileage,
        ]);

        $maintenance->vehicle->update(['status' => 'available', 'next_service_date' => now()->addMonths(6)]);

        return back()->with('success', 'Maintenance completed. Vehicle is available.');
    }
}

// ── InsurancePlan ──────────────────────────────────────────────────────────────

namespace App\Http\Controllers;

use App\Models\InsurancePlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

class InsurancePlanController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Insurance/Index', ['plans' => InsurancePlan::all()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'code'              => 'required|string|max:50|unique:insurance_plans',
            'coverage_type'     => 'required|in:basic,collision,comprehensive,full',
            'price_per_day'     => 'required|numeric|min:0',
            'deductible'        => 'nullable|numeric|min:0',
            'covers_theft'      => 'boolean',
            'covers_third_party'=> 'boolean',
            'description'       => 'nullable|string',
        ]);
        InsurancePlan::create($request->all());
        return back()->with('success', 'Insurance plan created.');
    }

    public function update(Request $request, InsurancePlan $insurancePlan): RedirectResponse
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'price_per_day' => 'required|numeric|min:0',
            'is_active'     => 'boolean',
        ]);
        $insurancePlan->update($request->all());
        return back()->with('success', 'Insurance plan updated.');
    }

    public function destroy(InsurancePlan $insurancePlan): RedirectResponse
    {
        $insurancePlan->update(['is_active' => false]);
        return back()->with('success', 'Insurance plan deactivated.');
    }
}

// ── ExtraService ───────────────────────────────────────────────────────────────

namespace App\Http\Controllers;

use App\Models\ExtraService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ExtraServiceController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('ExtraServices/Index', ['services' => ExtraService::all()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'code'         => 'required|string|unique:extra_services',
            'type'         => 'required|in:per_day,one_time',
            'price'        => 'required|numeric|min:0',
            'max_quantity' => 'required|integer|min:1',
        ]);
        ExtraService::create($request->all());
        return back()->with('success', 'Service created.');
    }

    public function update(Request $request, ExtraService $extraService): RedirectResponse
    {
        $extraService->update($request->validate(['name' => 'required', 'price' => 'required|numeric', 'is_active' => 'boolean']));
        return back()->with('success', 'Service updated.');
    }

    public function destroy(ExtraService $extraService): RedirectResponse
    {
        $extraService->update(['is_active' => false]);
        return back()->with('success', 'Service deactivated.');
    }
}

// ── Promotion ─────────────────────────────────────────────────────────────────

namespace App\Http\Controllers;

use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PromotionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Settings/Promotions', ['promotions' => Promotion::latest()->paginate(20)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'code'            => 'required|string|max:50|unique:promotions',
            'type'            => 'required|in:percentage,fixed',
            'value'           => 'required|numeric|min:0',
            'valid_from'      => 'required|date',
            'valid_until'     => 'required|date|after:valid_from',
            'max_uses'        => 'nullable|integer|min:1',
            'min_rental_days' => 'required|integer|min:1',
        ]);
        Promotion::create($request->all());
        return back()->with('success', 'Promotion created.');
    }

    public function update(Request $request, Promotion $promotion): RedirectResponse
    {
        $promotion->update($request->validate(['is_active' => 'boolean', 'valid_until' => 'date']));
        return back()->with('success', 'Promotion updated.');
    }

    public function destroy(Promotion $promotion): RedirectResponse
    {
        $promotion->delete();
        return back()->with('success', 'Promotion deleted.');
    }
}
