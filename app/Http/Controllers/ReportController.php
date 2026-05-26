<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Rental;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('reports/Index', [
            'summary' => [
                'total_revenue'       => Rental::where('status', 'completed')->sum('total_amount'),
                'total_rentals'       => Rental::where('status', 'completed')->count(),
                'avg_rental_duration' => Rental::where('status', 'completed')
                    ->selectRaw('AVG(DATEDIFF(actual_dropoff_at, actual_pickup_at)) as avg_days')
                    ->value('avg_days'),
                'avg_rental_value'    => Rental::where('status', 'completed')->avg('total_amount'),
                'total_customers'     => Customer::count(),
                'fleet_utilization'   => $this->fleetUtilization(),
            ],
            'revenueByMonth' => Rental::selectRaw(
                    "DATE_FORMAT(actual_dropoff_at,'%Y-%m') as month,
                     SUM(total_amount) as revenue,
                     COUNT(*) as rentals"
                )
                ->where('status', 'completed')
                ->where('actual_dropoff_at', '>=', now()->subMonths(12))
                ->groupBy('month')
                ->orderBy('month')
                ->get(),

            'revenueByCategory' => DB::table('rentals')
                ->join('vehicles', 'rentals.vehicle_id', '=', 'vehicles.id')
                ->join('vehicle_categories', 'vehicles.category_id', '=', 'vehicle_categories.id')
                ->where('rentals.status', 'completed')
                ->groupBy('vehicle_categories.id', 'vehicle_categories.name')
                ->selectRaw('vehicle_categories.name, SUM(rentals.total_amount) as revenue, COUNT(*) as rentals')
                ->get(),

            'revenueByBranch' => DB::table('rentals')
                ->join('branches', 'rentals.pickup_branch_id', '=', 'branches.id')
                ->where('rentals.status', 'completed')
                ->groupBy('branches.id', 'branches.name', 'branches.city')
                ->selectRaw('branches.name, branches.city, SUM(rentals.total_amount) as revenue, COUNT(*) as rentals')
                ->get(),

            'topVehicles' => DB::table('rentals')
                ->join('vehicles', 'rentals.vehicle_id', '=', 'vehicles.id')
                ->where('rentals.status', 'completed')
                ->groupBy('vehicles.id', 'vehicles.make', 'vehicles.model', 'vehicles.plate_number')
                ->selectRaw('vehicles.make, vehicles.model, vehicles.plate_number, COUNT(*) as rentals, SUM(rentals.total_amount) as revenue')
                ->orderByDesc('rentals')
                ->limit(10)
                ->get(),

            'topCustomers' => DB::table('rentals')
                ->join('customers', 'rentals.customer_id', '=', 'customers.id')
                ->join('users', 'customers.user_id', '=', 'users.id')
                ->where('rentals.status', 'completed')
                ->groupBy('customers.id', 'users.name', 'users.email')
                ->selectRaw('users.name, users.email, COUNT(*) as rentals, SUM(rentals.total_amount) as spent')
                ->orderByDesc('spent')
                ->limit(10)
                ->get(),
        ]);
    }

    private function fleetUtilization(): float
    {
        $totalVehicles = Vehicle::where('is_active', true)->count();
        if ($totalVehicles === 0) return 0;

        $rentedVehicles = Vehicle::whereIn('status', ['rented', 'reserved'])->count();
        return round(($rentedVehicles / $totalVehicles) * 100, 1);
    }
}
