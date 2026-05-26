<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Rental;
use App\Models\Reservation;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RentalService
{
    public function __construct(
        private readonly PricingService $pricing,
        private readonly AvailabilityService $availability,
    ) {}

    public function createFromReservation(Reservation $reservation, int $pickupMileage, int $pickupFuelLevel, int $agentId): Rental
    {
        return DB::transaction(function () use ($reservation, $pickupMileage, $pickupFuelLevel, $agentId) {
            $rental = Rental::create([
                'reservation_id'    => $reservation->id,
                'customer_id'       => $reservation->customer_id,
                'vehicle_id'        => $reservation->vehicle_id,
                'pickup_branch_id'  => $reservation->pickup_branch_id,
                'dropoff_branch_id' => $reservation->dropoff_branch_id,
                'insurance_plan_id' => $reservation->insurance_plan_id,
                'promotion_id'      => $reservation->promotion_id,
                'planned_pickup_at' => $reservation->pickup_date,
                'planned_dropoff_at'=> $reservation->dropoff_date,
                'actual_pickup_at'  => now(),
                'pickup_mileage'    => $pickupMileage,
                'pickup_fuel_level' => $pickupFuelLevel,
                'base_amount'       => $reservation->base_amount,
                'insurance_amount'  => $reservation->insurance_amount,
                'extras_amount'     => $reservation->extras_amount,
                'discount_amount'   => $reservation->discount_amount,
                'total_amount'      => $reservation->total_amount,
                'status'            => 'active',
                'agent_id'          => $agentId,
            ]);

            // Sync extra services
            $extras = $reservation->extraServices()->withPivot('quantity', 'unit_price')->get();
            $syncData = $extras->mapWithKeys(fn($s) => [
                $s->id => ['quantity' => $s->pivot->quantity, 'unit_price' => $s->pivot->unit_price],
            ])->toArray();
            $rental->extraServices()->sync($syncData);

            // Update vehicle & reservation status
            $reservation->vehicle->update(['status' => 'rented']);
            $reservation->update(['status' => 'converted']);

            return $rental->fresh(['customer', 'vehicle', 'pickupBranch', 'dropoffBranch']);
        });
    }

    public function completeRental(Rental $rental, int $actualMileage, int $dropoffFuelLevel, Carbon $actualDropoff): Rental
    {
        return DB::transaction(function () use ($rental, $actualMileage, $dropoffFuelLevel, $actualDropoff) {
            $charges = $this->pricing->calculateReturnCharges($rental, $actualDropoff, $actualMileage, $dropoffFuelLevel);

            $newTotal = (float) $rental->total_amount
                + $charges['late_return_charges']
                + $charges['extra_km_charges']
                + $charges['fuel_charges'];

            $rental->update([
                'actual_dropoff_at'   => $actualDropoff,
                'dropoff_mileage'     => $actualMileage,
                'dropoff_fuel_level'  => $dropoffFuelLevel,
                'late_return_charges' => $charges['late_return_charges'],
                'extra_km_charges'    => $charges['extra_km_charges'],
                'fuel_charges'        => $charges['fuel_charges'],
                'total_amount'        => round($newTotal, 2),
                'status'              => 'completed',
            ]);

            // Release vehicle
            $rental->vehicle->update([
                'status'   => 'available',
                'mileage'  => $actualMileage,
                'fuel_level' => $dropoffFuelLevel,
            ]);

            // Generate invoice
            $this->generateInvoice($rental->fresh());

            return $rental->fresh();
        });
    }

    public function generateInvoice(Rental $rental): Invoice
    {
        $taxRate = 20.00;
        $subtotal = (float) $rental->total_amount;
        $taxAmount = round($subtotal * $taxRate / 100, 2);
        $total = $subtotal + $taxAmount;

        return Invoice::updateOrCreate(
            ['rental_id' => $rental->id],
            [
                'customer_id'     => $rental->customer_id,
                'subtotal'        => $subtotal,
                'tax_rate'        => $taxRate,
                'tax_amount'      => $taxAmount,
                'discount_amount' => (float) $rental->discount_amount,
                'total'           => $total,
                'status'          => 'draft',
                'due_date'        => now()->addDays(7),
            ]
        );
    }
}
