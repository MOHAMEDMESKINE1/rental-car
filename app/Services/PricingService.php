<?php

namespace App\Services;

use App\Models\ExtraService;
use App\Models\InsurancePlan;
use App\Models\Promotion;
use App\Models\Vehicle;
use Carbon\Carbon;

class PricingService
{
    public function calculateRentalPrice(
        Vehicle $vehicle,
        Carbon $pickupDate,
        Carbon $dropoffDate,
        ?InsurancePlan $insurance = null,
        array $extraServiceIds = [],
        ?string $promoCode = null,
    ): array {
        $days = max(1, (int) $pickupDate->diffInDays($dropoffDate));

        // Base amount
        $baseAmount = $vehicle->category->base_price_per_day * $days;

        // Insurance
        $insuranceAmount = 0.0;
        if ($insurance) {
            $insuranceAmount = (float) $insurance->price_per_day * $days;
        }

        // Extra services
        $extrasAmount = 0.0;
        $extrasBreakdown = [];
        if ($extraServiceIds) {
            $services = ExtraService::whereIn('id', $extraServiceIds)->get();
            foreach ($services as $service) {
                $serviceTotal = $service->type === 'per_day'
                    ? (float) $service->price * $days
                    : (float) $service->price;
                $extrasAmount += $serviceTotal;
                $extrasBreakdown[] = [
                    'id'         => $service->id,
                    'name'       => $service->name,
                    'unit_price' => (float) $service->price,
                    'total'      => $serviceTotal,
                ];
            }
        }

        // Promo
        $discountAmount = 0.0;
        $promotion = null;
        if ($promoCode) {
            $promotion = Promotion::where('code', $promoCode)->first();
            if ($promotion && $promotion->isValid() && $days >= $promotion->min_rental_days) {
                $discountAmount = $promotion->calculateDiscount($baseAmount + $insuranceAmount + $extrasAmount);
            }
        }

        $total = $baseAmount + $insuranceAmount + $extrasAmount - $discountAmount;

        return [
            'days'             => $days,
            'base_amount'      => round($baseAmount, 2),
            'insurance_amount' => round($insuranceAmount, 2),
            'extras_amount'    => round($extrasAmount, 2),
            'discount_amount'  => round($discountAmount, 2),
            'total_amount'     => round($total, 2),
            'extras_breakdown' => $extrasBreakdown,
            'promotion'        => $promotion,
        ];
    }

    public function calculateReturnCharges(
        \App\Models\Rental $rental,
        Carbon $actualDropoff,
        int $actualMileage,
        int $dropoffFuelLevel,
    ): array {
        // Late return
        $lateReturnCharges = 0.0;
        if ($actualDropoff->greaterThan($rental->planned_dropoff_at)) {
            $extraDays = (int) $rental->planned_dropoff_at->diffInDays($actualDropoff);
            if ($extraDays > 0) {
                $lateReturnCharges = $extraDays * (float) $rental->vehicle->category->base_price_per_day;
            }
        }

        // Extra km
        $extraKmCharges = 0.0;
        $kmDriven = $actualMileage - $rental->pickup_mileage;
        $freeKm = $rental->vehicle->category->free_km_per_day * $rental->planned_days;
        if ($kmDriven > $freeKm) {
            $extraKm = $kmDriven - $freeKm;
            $extraKmCharges = $extraKm * (float) $rental->vehicle->category->extra_km_price;
        }

        // Fuel charges (refueling fee if returned below pickup level)
        $fuelCharges = 0.0;
        if ($dropoffFuelLevel < $rental->pickup_fuel_level) {
            $fuelMissing = $rental->pickup_fuel_level - $dropoffFuelLevel;
            $fuelCharges = ($fuelMissing / 100) * 80; // assume 80 MAD per full tank
        }

        return [
            'late_return_charges' => round($lateReturnCharges, 2),
            'extra_km_charges'    => round($extraKmCharges, 2),
            'fuel_charges'        => round($fuelCharges, 2),
            'km_driven'           => $kmDriven,
            'extra_km'            => max(0, $kmDriven - $freeKm),
        ];
    }
}
