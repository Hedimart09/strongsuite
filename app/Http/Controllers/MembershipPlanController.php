<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMembershipPlanRequest;
use App\Http\Requests\UpdateMembershipPlanRequest;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MembershipPlanController extends Controller
{
    public function index(Request $request)
    {
        $query = MembershipPlan::query()->withCount('subscriptions');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->where('is_active', true);
            } else {
                $query->where('is_active', false);
            }
        }

        $plans = $query->latest()->paginate(15);

        return Inertia::render('MembershipPlans/Index', [
            'plans' => $plans,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('MembershipPlans/Create');
    }

    public function store(StoreMembershipPlanRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active') ? (bool) $request->input('is_active') : true;

        MembershipPlan::create($data);

        return redirect()->route('membership-plans.index')
            ->with('success', 'Membership plan created successfully');
    }

    public function show(MembershipPlan $membershipPlan)
    {
        $membershipPlan->loadCount('subscriptions');
        $membershipPlan->load(['subscriptions' => function ($q) {
            $q->with('member')->latest()->limit(10);
        }]);

        return Inertia::render('MembershipPlans/Show', [
            'plan' => $membershipPlan,
        ]);
    }

    public function edit(MembershipPlan $membershipPlan)
    {
        return Inertia::render('MembershipPlans/Edit', [
            'plan' => $membershipPlan,
        ]);
    }

    public function update(UpdateMembershipPlanRequest $request, MembershipPlan $membershipPlan)
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active') ? (bool) $request->input('is_active') : $membershipPlan->is_active;

        $membershipPlan->update($data);

        return redirect()->route('membership-plans.show', $membershipPlan)
            ->with('success', 'Membership plan updated successfully');
    }

    public function destroy(MembershipPlan $membershipPlan)
    {
        if ($membershipPlan->subscriptions()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete plan with existing subscriptions');
        }

        $membershipPlan->delete();

        return redirect()->route('membership-plans.index')
            ->with('success', 'Membership plan deleted successfully');
    }
}
