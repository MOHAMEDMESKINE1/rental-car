<?php

namespace App\Http\Controllers;

use App\Http\Requests\RentalRequest;
use App\Http\Requests\ReturnVehicleRequest;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\ExtraService;
use App\Models\InsurancePlan;
use App\Models\Rental;
use App\Models\Reservation;
use App\Services\RentalService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\QueryBuilderRequest;

class RentalController extends Controller
{
    public function __construct(private readonly RentalService $rentalService) {}

    public function index(Request $request): Response
    {
        $rentals = QueryBuilderRequest::for(Rental::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::partial('rental_number'),
            ])
            ->with(['customer.user', 'vehicle', 'pickupBranch', 'dropoffBranch', 'agent'])
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Rentals/Index', [
            'rentals' => $rentals,
            'filters' => $request->query(),
            'summary' => [
                'active'    => Rental::where('status', 'active')->count(),
                'overdue'   => Rental::overdue()->count(),
                'completed' => Rental::where('status', 'completed')->whereDate('updated_at', today())->count(),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Rentals/Create', [
            'reservations'  => Reservation::with(['customer.user', 'vehicle'])->where('status', 'confirmed')->get(),
            'customers'     => Customer::notBlacklisted()->with('user')->get(),
            'branches'      => Branch::active()->get(['id', 'name', 'city']),
            'insurancePlans'=> InsurancePlan::active()->get(),
            'extraServices' => ExtraService::active()->get(),
            'reservationId' => $request->reservation_id,
        ]);
    }

    public function storeFromReservation(Request $request, Reservation $reservation): RedirectResponse
    {
        $request->validate([
            'pickup_mileage'   => 'required|integer|min:0',
            'pickup_fuel_level'=> 'required|integer|min:0|max:100',
        ]);

        $rental = $this->rentalService->createFromReservation(
            $reservation,
            $request->pickup_mileage,
            $request->pickup_fuel_level,
            auth()->id(),
        );

        return redirect()->route('rentals.show', $rental)
            ->with('success', 'Rental started successfully.');
    }

    public function show(Rental $rental): Response
    {
        $rental->load([
            'customer.user', 'vehicle.category', 'pickupBranch', 'dropoffBranch',
            'insurancePlan', 'promotion', 'extraServices', 'additionalDrivers',
            'inspections', 'damages', 'payments', 'invoice', 'agent', 'review',
        ]);

        return Inertia::render('Rentals/Show', compact('rental'));
    }

    public function returnForm(Rental $rental): Response
    {
        abort_unless($rental->status === 'active', 403, 'This rental is not active.');

        return Inertia::render('Rentals/Return', [
            'rental' => $rental->load(['customer.user', 'vehicle', 'pickupBranch', 'dropoffBranch']),
        ]);
    }

    public function processReturn(ReturnVehicleRequest $request, Rental $rental): RedirectResponse
    {
        abort_unless($rental->status === 'active', 403);

        $rental = $this->rentalService->completeRental(
            $rental,
            $request->dropoff_mileage,
            $request->dropoff_fuel_level,
            Carbon::parse($request->actual_dropoff_at),
        );

        return redirect()->route('rentals.show', $rental)
            ->with('success', 'Vehicle returned and rental completed.');
    }

    public function cancel(Rental $rental): RedirectResponse
    {
        abort_unless(in_array($rental->status, ['active']), 403);

        $rental->update(['status' => 'cancelled']);
        $rental->vehicle->update(['status' => 'available']);

        return redirect()->route('rentals.index')->with('success', 'Rental cancelled.');
    }
}
