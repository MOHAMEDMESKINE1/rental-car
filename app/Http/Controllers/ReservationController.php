<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReservationRequest;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\ExtraService;
use App\Models\InsurancePlan;
use App\Models\Promotion;
use App\Models\Reservation;
use App\Models\Vehicle;
use App\Services\AvailabilityService;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\QueryBuilderRequest;

class ReservationController extends Controller
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly PricingService $pricing,
    ) {}

    public function index(Request $request): Response
    {
        $reservations = QueryBuilderRequest::for(Reservation::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::partial('reservation_number'),
            ])
            ->with(['customer.user', 'vehicle', 'pickupBranch', 'dropoffBranch'])
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('reservations/Index', [
            'reservations' => $reservations,
            'filters'      => $request->query(),
        ]);
    }

    public function create(Request $request): Response
    {
        $availableVehicles = [];
        if ($request->filled(['pickup_date', 'dropoff_date'])) {
            $availableVehicles = $this->availability->getAvailableVehicles(
                Carbon::parse($request->pickup_date),
                Carbon::parse($request->dropoff_date),
                $request->category_id,
                $request->branch_id,
            );
        }

        return Inertia::render('reservations/Form', [
            'customers'       => Customer::notBlacklisted()->with('user')->get(),
            'branches'        => Branch::active()->get(['id', 'name', 'city']),
            'insurancePlans'  => InsurancePlan::active()->get(),
            'extraServices'   => ExtraService::active()->get(),
            'availableVehicles' => $availableVehicles,
            'query'           => $request->only(['pickup_date', 'dropoff_date', 'branch_id', 'category_id']),
        ]);
    }

    public function store(ReservationRequest $request): RedirectResponse
    {
        $pickup   = Carbon::parse($request->pickup_date);
        $dropoff  = Carbon::parse($request->dropoff_date);
        $vehicle  = Vehicle::findOrFail($request->vehicle_id);
        $insurance = $request->insurance_plan_id ? InsurancePlan::find($request->insurance_plan_id) : null;

        if (! $this->availability->isVehicleAvailable($vehicle->id, $pickup, $dropoff)) {
            return back()->withErrors(['vehicle_id' => 'This vehicle is no longer available for the selected dates.']);
        }

        $price = $this->pricing->calculateRentalPrice(
            $vehicle, $pickup, $dropoff, $insurance,
            $request->extra_service_ids ?? [],
            $request->promo_code,
        );

        $reservation = Reservation::create([
            ...$request->safe()->except(['extra_service_ids', 'promo_code']),
            'promotion_id'     => $price['promotion']?->id,
            'base_amount'      => $price['base_amount'],
            'insurance_amount' => $price['insurance_amount'],
            'extras_amount'    => $price['extras_amount'],
            'discount_amount'  => $price['discount_amount'],
            'total_amount'     => $price['total_amount'],
            'status'           => 'confirmed',
            'created_by'       => auth()->id(),
        ]);

        if ($request->extra_service_ids) {
            $extras = ExtraService::whereIn('id', $request->extra_service_ids)->get();
            $syncData = $extras->mapWithKeys(fn($s) => [
                $s->id => ['quantity' => 1, 'unit_price' => $s->price],
            ])->toArray();
            $reservation->extraServices()->sync($syncData);
        }

        return redirect()->route('reservations.show', $reservation)
            ->with('success', 'Reservation created successfully.');
    }

    public function show(Reservation $reservation): Response
    {
        $reservation->load(['customer.user', 'vehicle.category', 'pickupBranch', 'dropoffBranch', 'insurancePlan', 'promotion', 'extraServices', 'rental']);

        return Inertia::render('reservations/Show', compact('reservation'));
    }

    public function edit(Reservation $reservation): Response
    {
        return Inertia::render('reservations/Form', [
            'reservation'    => $reservation->load(['extraServices']),
            'customers'      => Customer::notBlacklisted()->with('user')->get(),
            'branches'       => Branch::active()->get(['id', 'name', 'city']),
            'insurancePlans' => InsurancePlan::active()->get(),
            'extraServices'  => ExtraService::active()->get(),
        ]);
    }

    public function update(ReservationRequest $request, Reservation $reservation): RedirectResponse
    {
        $reservation->update($request->validated());
        return redirect()->route('reservations.show', $reservation)->with('success', 'Reservation updated.');
    }

    public function cancel(Request $request, Reservation $reservation): RedirectResponse
    {
        $reservation->update(['status' => 'cancelled']);
        return redirect()->route('reservations.index')->with('success', 'Reservation cancelled.');
    }

    public function calculatePrice(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'vehicle_id'       => 'required|uuid|exists:vehicles,id',
            'pickup_date'      => 'required|date',
            'dropoff_date'     => 'required|date|after:pickup_date',
            'insurance_plan_id'=> 'nullable|uuid|exists:insurance_plans,id',
            'extra_service_ids'=> 'nullable|array',
            'promo_code'       => 'nullable|string',
        ]);

        $vehicle   = Vehicle::with('category')->findOrFail($request->vehicle_id);
        $insurance = $request->insurance_plan_id ? InsurancePlan::find($request->insurance_plan_id) : null;

        $price = $this->pricing->calculateRentalPrice(
            $vehicle,
            Carbon::parse($request->pickup_date),
            Carbon::parse($request->dropoff_date),
            $insurance,
            $request->extra_service_ids ?? [],
            $request->promo_code,
        );

        return response()->json($price);
    }
}
