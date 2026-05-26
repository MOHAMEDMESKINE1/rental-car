<?php

namespace App\Services;

use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AvailabilityService
{
    public function getAvailableVehicles(
        Carbon $pickupDate,
        Carbon $dropoffDate,
        ?string $categoryId = null,
        ?string $branchId = null,
    ): Collection {
        return Vehicle::query()
            ->with(['category', 'branch'])
            ->where('status', 'available')
            ->where('is_active', true)
            ->when($categoryId, fn(Builder $q) => $q->where('category_id', $categoryId))
            ->when($branchId, fn(Builder $q) => $q->where('branch_id', $branchId))
            ->whereDoesntHave('reservations', function (Builder $q) use ($pickupDate, $dropoffDate) {
                $q->whereIn('status', ['pending', 'confirmed'])
                    ->where(function ($q2) use ($pickupDate, $dropoffDate) {
                        $q2->whereBetween('pickup_date', [$pickupDate, $dropoffDate])
                            ->orWhereBetween('dropoff_date', [$pickupDate, $dropoffDate])
                            ->orWhere(function ($q3) use ($pickupDate, $dropoffDate) {
                                $q3->where('pickup_date', '<=', $pickupDate)
                                    ->where('dropoff_date', '>=', $dropoffDate);
                            });
                    });
            })
            ->whereDoesntHave('rentals', function (Builder $q) use ($pickupDate, $dropoffDate) {
                $q->whereIn('status', ['active', 'overdue'])
                    ->where(function ($q2) use ($pickupDate, $dropoffDate) {
                        $q2->whereBetween('planned_pickup_at', [$pickupDate, $dropoffDate])
                            ->orWhereBetween('planned_dropoff_at', [$pickupDate, $dropoffDate])
                            ->orWhere(function ($q3) use ($pickupDate, $dropoffDate) {
                                $q3->where('planned_pickup_at', '<=', $pickupDate)
                                    ->where('planned_dropoff_at', '>=', $dropoffDate);
                            });
                    });
            })
            ->get();
    }

    public function isVehicleAvailable(string $vehicleId, Carbon $pickupDate, Carbon $dropoffDate, ?string $excludeReservationId = null, ?string $excludeRentalId = null): bool
    {
        $vehicle = Vehicle::findOrFail($vehicleId);

        if ($vehicle->status !== 'available' || ! $vehicle->is_active) {
            return false;
        }

        $conflictingReservation = $vehicle->reservations()
            ->whereIn('status', ['pending', 'confirmed'])
            ->when($excludeReservationId, fn($q) => $q->where('id', '!=', $excludeReservationId))
            ->where(function ($q) use ($pickupDate, $dropoffDate) {
                $q->whereBetween('pickup_date', [$pickupDate, $dropoffDate])
                    ->orWhereBetween('dropoff_date', [$pickupDate, $dropoffDate])
                    ->orWhere(fn($q2) => $q2->where('pickup_date', '<=', $pickupDate)->where('dropoff_date', '>=', $dropoffDate));
            })
            ->exists();

        if ($conflictingReservation) return false;

        $conflictingRental = $vehicle->rentals()
            ->whereIn('status', ['active', 'overdue'])
            ->when($excludeRentalId, fn($q) => $q->where('id', '!=', $excludeRentalId))
            ->where(function ($q) use ($pickupDate, $dropoffDate) {
                $q->whereBetween('planned_pickup_at', [$pickupDate, $dropoffDate])
                    ->orWhereBetween('planned_dropoff_at', [$pickupDate, $dropoffDate])
                    ->orWhere(fn($q2) => $q2->where('planned_pickup_at', '<=', $pickupDate)->where('planned_dropoff_at', '>=', $dropoffDate));
            })
            ->exists();

        return ! $conflictingRental;
    }
}
