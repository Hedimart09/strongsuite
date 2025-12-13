<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class MemberDashboardController extends Controller
{
    public function index()
    {
        $member = auth('member')->user();

        $member->load([
            'subscriptions' => fn ($q) => $q->latest()->limit(1),
            'attendances' => fn ($q) => $q->latest()->limit(10),
        ]);

        $activeSubscription = $member->subscriptions()
            ->where('status', 'active')
            ->where('end_date', '>=', now())
            ->with('membershipPlan')
            ->first();

        $activeCheckIn = $member->attendances()
            ->whereNull('check_out_time')
            ->first();

        return Inertia::render('Member/Dashboard', [
            'member' => $member,
            'activeSubscription' => $activeSubscription,
            'activeCheckIn' => $activeCheckIn,
        ]);
    }
}
