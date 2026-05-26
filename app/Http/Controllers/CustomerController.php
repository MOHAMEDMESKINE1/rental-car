<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customers\CustomerRequest;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as RoutingController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CustomerController extends RoutingController
{
    public function index(Request $request): Response
    {
        $customers  = QueryBuilder::for(Customer::class)
    ->allowedFilters(...[
        AllowedFilter::exact('is_blacklisted'),
        AllowedFilter::partial('license_number'),
        AllowedFilter::partial('phone'),
        'nationality',
        'city',
    ])
    ->with(['user'])

    ->paginate(15);


        return Inertia::render('customers/Index', [
            'customers' => $customers,
            'filters'   => $request->query(),
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::modal('customers/modals/Create');
    }

    public function store(CustomerRequest $request): RedirectResponse
    {
        return DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password ?? str()->random(12)),
            ]);
            $user->assignRole('customer');

            $customer = Customer::create(array_merge(
                $request->safe()->except(['name', 'email', 'password']),
                ['user_id' => $user->id]
            ));

            foreach (['license_front', 'license_back', 'passport', 'id_card'] as $doc) {
                if ($request->hasFile($doc)) {
                    $customer->addMedia($request->file($doc))->toMediaCollection($doc);
                }
            }

            return redirect()->route('customers.show', $customer)
                ->with('success', 'Customer created successfully.');
        });
    }

    public function show(Customer $customer): Response
    {
        $customer->load(['user', 'media', 'activeRental.vehicle', 'reservations' => fn($q) => $q->latest()->limit(5)]);

        return Inertia::render('customers/Show', [
            'customer'        => $customer,
            'rentals'         => $customer->rentals()->with('vehicle')->latest()->limit(10)->get(),
            'totalSpent'      => $customer->rentals()->where('status', 'completed')->sum('total_amount'),
            'totalRentals'    => $customer->rentals()->count(),
            'pendingBalance'  => $customer->rentals()->where('status', 'active')->sum('total_amount'),
        ]);
    }

    public function edit(Customer $customer): Response
    {
        return Inertia::render('customers/Form', [
            'customer' => $customer->load(['user', 'media']),
        ]);
    }

    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        DB::transaction(function () use ($request, $customer) {
            $customer->user->update($request->safe()->only(['name', 'email']));
            $customer->update($request->safe()->except(['name', 'email', 'password']));

            foreach (['license_front', 'license_back', 'passport', 'id_card'] as $doc) {
                if ($request->hasFile($doc)) {
                    $customer->clearMediaCollection($doc);
                    $customer->addMedia($request->file($doc))->toMediaCollection($doc);
                }
            }
        });

        return redirect()->route('customers.show', $customer)->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer removed.');
    }

    public function toggleBlacklist(Request $request, Customer $customer): RedirectResponse
    {
        $request->validate(['reason' => 'nullable|string|max:255']);
        $customer->update([
            'is_blacklisted'   => ! $customer->is_blacklisted,
            'blacklist_reason' => $customer->is_blacklisted ? null : $request->reason,
        ]);

        return back()->with('success', $customer->is_blacklisted ? 'Customer blacklisted.' : 'Customer removed from blacklist.');
    }
}
