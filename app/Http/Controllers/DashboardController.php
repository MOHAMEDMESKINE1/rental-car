<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Rental;
use App\Models\Reservation;
use App\Models\Vehicle;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $stats = [
            'total_vehicles'     => Vehicle::count(),
            'available_vehicles' => Vehicle::where('status', 'available')->count(),
            'active_rentals'     => Rental::where('status', 'active')->count(),
            'overdue_rentals'    => Rental::overdue()->count(),
            'pending_reservations' => Reservation::where('status', 'pending')->count(),
            'total_customers'    => Customer::count(),
            'revenue_today'      => Rental::whereDate('actual_dropoff_at', today())
                ->where('status', 'completed')
                ->sum('total_amount'),
            'revenue_month'      => Rental::whereMonth('actual_dropoff_at', now()->month)
                ->whereYear('actual_dropoff_at', now()->year)
                ->where('status', 'completed')
                ->sum('total_amount'),
        ];

        $revenueChart = Rental::selectRaw("DATE_FORMAT(actual_dropoff_at, '%Y-%m') as month, SUM(total_amount) as revenue, COUNT(*) as count")
            ->where('status', 'completed')
            ->where('actual_dropoff_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $vehicleStatusChart = Vehicle::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        $recentRentals = Rental::with(['customer.user', 'vehicle'])
            ->latest()
            ->limit(10)
            ->get();

        $upcomingReservations = Reservation::with(['customer.user', 'vehicle'])
            ->where('status', 'confirmed')
            ->where('pickup_date', '>=', now())
            ->orderBy('pickup_date')
            ->limit(5)
            ->get();

        $overdueRentals = Rental::with(['customer.user', 'vehicle'])
            ->overdue()
            ->get();

        return Inertia::render('Dashboard', compact(
            'stats',
            'revenueChart',
            'vehicleStatusChart',
            'recentRentals',
            'upcomingReservations',
            'overdueRentals',
        ));
    }
}
