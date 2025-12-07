<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubscriptionRequest;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Subscription;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function create(Member $member)
    {
        $activePlans = MembershipPlan::active()->get();

        return Inertia::render('Subscriptions/Create', [
            'member' => $member,
            'plans' => $activePlans,
        ]);
    }

    public function store(StoreSubscriptionRequest $request)
    {
        $data = $request->validated();

        $plan = MembershipPlan::findOrFail($data['membership_plan_id']);

        $startDate = $data['start_date'];
        $endDate = now()->parse($startDate)->addDays($plan->duration_in_days);

        $subscription = Subscription::create([
            'member_id' => $data['member_id'],
            'membership_plan_id' => $data['membership_plan_id'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
            'auto_renew' => $data['auto_renew'] ?? false,
        ]);

        return redirect()->route('members.show', $subscription->member_id)
            ->with('success', 'Subscription created successfully');
    }

    public function renew(Subscription $subscription)
    {
        if ($subscription->status !== 'active') {
            return redirect()->back()
                ->with('error', 'Cannot renew inactive subscription');
        }

        $plan = $subscription->membershipPlan;
        $newStartDate = $subscription->end_date->addDay();
        $newEndDate = $newStartDate->copy()->addDays($plan->duration_in_days);

        $newSubscription = Subscription::create([
            'member_id' => $subscription->member_id,
            'membership_plan_id' => $subscription->membership_plan_id,
            'start_date' => $newStartDate,
            'end_date' => $newEndDate,
            'status' => 'active',
            'auto_renew' => $subscription->auto_renew,
        ]);

        $subscription->update(['status' => 'expired']);

        return redirect()->route('members.show', $subscription->member_id)
            ->with('success', 'Subscription renewed successfully');
    }

    public function cancel(Subscription $subscription)
    {
        if ($subscription->status !== 'active') {
            return redirect()->back()
                ->with('error', 'Subscription is already cancelled or expired');
        }

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'auto_renew' => false,
        ]);

        return redirect()->route('members.show', $subscription->member_id)
            ->with('success', 'Subscription cancelled successfully');
    }
}
