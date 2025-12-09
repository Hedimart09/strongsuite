<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Active members count
        $activeMembersCount = Member::where('status', 'active')->count();

        // Total members count
        $totalMembersCount = Member::count();

        // Today's check-ins
        $todayCheckIns = Attendance::whereDate('check_in_time', today())
            ->distinct('member_id')
            ->count('member_id');

        // This month's check-ins
        $monthCheckIns = Attendance::whereMonth('check_in_time', now()->month)
            ->whereYear('check_in_time', now()->year)
            ->distinct('member_id')
            ->count('member_id');

        // Today's revenue (completed payments)
        $todayRevenue = Payment::where('status', 'completed')
            ->whereDate('payment_date', today())
            ->sum('amount');

        // This month's revenue
        $monthRevenue = Payment::where('status', 'completed')
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        // Recent member registrations (last 10)
        $recentMembers = Member::with('activeSubscription.membershipPlan')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'member_id' => $member->member_id,
                    'email' => $member->email,
                    'status' => $member->status,
                    'created_at' => $member->created_at->toISOString(),
                    'subscription' => $member->activeSubscription->first(),
                ];
            });

        // Active subscriptions
        $activeSubscriptions = Subscription::where('status', 'active')
            ->where('end_date', '>=', now())
            ->count();

        // Expiring soon (within 7 days)
        $expiringSoon = Subscription::where('status', 'active')
            ->whereBetween('end_date', [now(), now()->addDays(7)])
            ->count();

        // Revenue by day (last 7 days)
        $revenueByDay = Payment::where('status', 'completed')
            ->where('payment_date', '>=', now()->subDays(6)->startOfDay())
            ->select(
                DB::raw('DATE(payment_date) as date'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->date => $item->total];
            });

        // Fill in missing days with 0
        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $last7Days[$date] = $revenueByDay->get($date, 0);
        }

        // Check-ins by day (last 7 days)
        $checkInsByDay = Attendance::where('check_in_time', '>=', now()->subDays(6)->startOfDay())
            ->select(
                DB::raw('DATE(check_in_time) as date'),
                DB::raw('COUNT(DISTINCT member_id) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->date => $item->count];
            });

        // Fill in missing days
        $last7DaysCheckIns = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $last7DaysCheckIns[$date] = $checkInsByDay->get($date, 0);
        }

        return Inertia::render('Dashboard', [
            'metrics' => [
                'active_members' => $activeMembersCount,
                'total_members' => $totalMembersCount,
                'today_check_ins' => $todayCheckIns,
                'month_check_ins' => $monthCheckIns,
                'today_revenue' => $todayRevenue,
                'month_revenue' => $monthRevenue,
                'active_subscriptions' => $activeSubscriptions,
                'expiring_soon' => $expiringSoon,
            ],
            'recent_members' => $recentMembers,
            'charts' => [
                'revenue_by_day' => [
                    'labels' => $last7Days->keys()->toArray(),
                    'data' => $last7Days->values()->toArray(),
                ],
                'check_ins_by_day' => [
                    'labels' => $last7DaysCheckIns->keys()->toArray(),
                    'data' => $last7DaysCheckIns->values()->toArray(),
                ],
            ],
        ]);
    }
}
