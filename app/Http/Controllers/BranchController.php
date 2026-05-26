<?php

namespace App\Http\Controllers;

use App\Http\Requests\BranchRequest;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\QueryBuilderRequest;

class BranchController extends Controller
{
    public function index(): Response
    {
        $branches = QueryBuilderRequest::for(Branch::class)
            ->allowedFilters(['city', 'country', 'is_active'])
            ->withCount(['vehicles', 'pickupRentals'])
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('branches/Index', compact('branches'));
    }

    public function create(): Response
    {
        return Inertia::render('branches/Form');
    }

    public function store(BranchRequest $request): RedirectResponse
    {
        $branch = Branch::create($request->validated());

        if ($request->hasFile('photo')) {
            $branch->addMedia($request->file('photo'))->toMediaCollection('photo');
        }

        return redirect()->route('branches.index')->with('success', 'Branch created.');
    }

    public function show(Branch $branch): Response
    {
        $branch->loadCount(['vehicles', 'pickupRentals']);
        $branch->load(['vehicles' => fn($q) => $q->with('category')->limit(10)]);
        return Inertia::render('branches/Show', compact('branch'));
    }

    public function edit(Branch $branch): Response
    {
        return Inertia::render('branches/Form', ['branch' => $branch->load('media')]);
    }

    public function update(BranchRequest $request, Branch $branch): RedirectResponse
    {
        $branch->update($request->validated());
        if ($request->hasFile('photo')) {
            $branch->clearMediaCollection('photo');
            $branch->addMedia($request->file('photo'))->toMediaCollection('photo');
        }
        return redirect()->route('branches.show', $branch)->with('success', 'Branch updated.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $branch->delete();
        return redirect()->route('branches.index')->with('success', 'Branch deleted.');
    }
}
