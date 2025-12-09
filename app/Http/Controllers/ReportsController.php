<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Member;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $reportType = $request->input('type', 'attendance');
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $reportData = match ($reportType) {
            'attendance' => $this->getAttendanceReport($startDate, $endDate),
            'revenue' => $this->getRevenueReport($startDate, $endDate),
            'members' => $this->getMembersReport($startDate, $endDate),
            default => $this->getAttendanceReport($startDate, $endDate),
        };

        return Inertia::render('Reports/Index', [
            'reportType' => $reportType,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportData' => $reportData,
        ]);
    }

    private function getAttendanceReport(string $startDate, string $endDate): array
    {
        // Total check-ins in period
        $totalCheckIns = Attendance::whereBetween('check_in_time', [$startDate, $endDate.' 23:59:59'])
            ->count();

        // Unique members who checked in
        $uniqueMembers = Attendance::whereBetween('check_in_time', [$startDate, $endDate.' 23:59:59'])
            ->distinct('member_id')
            ->count('member_id');

        // Average check-ins per day
        $days = max(1, now()->parse($startDate)->diffInDays(now()->parse($endDate)) + 1);
        $avgCheckInsPerDay = round($totalCheckIns / $days, 2);

        // Daily breakdown
        $dailyBreakdown = Attendance::whereBetween('check_in_time', [$startDate, $endDate.' 23:59:59'])
            ->select(
                DB::raw('DATE(check_in_time) as date'),
                DB::raw('COUNT(*) as total_check_ins'),
                DB::raw('COUNT(DISTINCT member_id) as unique_members')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top members by check-ins
        $topMembers = Attendance::whereBetween('check_in_time', [$startDate, $endDate.' 23:59:59'])
            ->select('member_id', DB::raw('COUNT(*) as check_in_count'))
            ->groupBy('member_id')
            ->orderByDesc('check_in_count')
            ->limit(10)
            ->with('member:id,name,member_id')
            ->get()
            ->map(function ($attendance) {
                return [
                    'member' => $attendance->member,
                    'check_in_count' => $attendance->check_in_count,
                ];
            });

        // Peak hours
        $peakHours = Attendance::whereBetween('check_in_time', [$startDate, $endDate.' 23:59:59'])
            ->select(
                DB::raw("strftime('%H', check_in_time) as hour"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('hour')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'hour' => $item->hour.':00',
                    'count' => $item->count,
                ];
            });

        return [
            'summary' => [
                'total_check_ins' => $totalCheckIns,
                'unique_members' => $uniqueMembers,
                'avg_check_ins_per_day' => $avgCheckInsPerDay,
                'date_range_days' => $days,
            ],
            'daily_breakdown' => $dailyBreakdown,
            'top_members' => $topMembers,
            'peak_hours' => $peakHours,
        ];
    }

    private function getRevenueReport(string $startDate, string $endDate): array
    {
        // Total revenue in period
        $totalRevenue = Payment::where('status', 'completed')
            ->whereDate('payment_date', '>=', $startDate)
            ->whereDate('payment_date', '<=', $endDate)
            ->sum('amount');

        // Number of payments
        $totalPayments = Payment::where('status', 'completed')
            ->whereDate('payment_date', '>=', $startDate)
            ->whereDate('payment_date', '<=', $endDate)
            ->count();

        // Average payment amount
        $avgPaymentAmount = $totalPayments > 0 ? round($totalRevenue / $totalPayments) : 0;

        // Revenue by payment method
        $revenueByMethod = Payment::where('status', 'completed')
            ->whereDate('payment_date', '>=', $startDate)
            ->whereDate('payment_date', '<=', $endDate)
            ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        // Daily revenue breakdown
        $dailyRevenue = Payment::where('status', 'completed')
            ->whereDate('payment_date', '>=', $startDate)
            ->whereDate('payment_date', '<=', $endDate)
            ->select(
                DB::raw('DATE(payment_date) as date'),
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top paying members
        $topPayingMembers = Payment::where('status', 'completed')
            ->whereDate('payment_date', '>=', $startDate)
            ->whereDate('payment_date', '<=', $endDate)
            ->select('member_id', DB::raw('SUM(amount) as total_paid'), DB::raw('COUNT(*) as payment_count'))
            ->groupBy('member_id')
            ->orderByDesc('total_paid')
            ->limit(10)
            ->with('member:id,name,member_id')
            ->get()
            ->map(function ($payment) {
                return [
                    'member' => $payment->member,
                    'total_paid' => $payment->total_paid,
                    'payment_count' => $payment->payment_count,
                ];
            });

        return [
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_payments' => $totalPayments,
                'avg_payment_amount' => $avgPaymentAmount,
            ],
            'revenue_by_method' => $revenueByMethod,
            'daily_revenue' => $dailyRevenue,
            'top_paying_members' => $topPayingMembers,
        ];
    }

    private function getMembersReport(string $startDate, string $endDate): array
    {
        // New members in period
        $newMembers = Member::whereBetween('created_at', [$startDate, $endDate.' 23:59:59'])
            ->count();

        // Total active members
        $activeMembers = Member::where('status', 'active')->count();

        // Total inactive members
        $inactiveMembers = Member::where('status', 'inactive')->count();

        // Members by status
        $membersByStatus = Member::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // New members daily breakdown
        $dailyNewMembers = Member::whereBetween('created_at', [$startDate, $endDate.' 23:59:59'])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Recently registered members
        $recentMembers = Member::whereBetween('created_at', [$startDate, $endDate.' 23:59:59'])
            ->with('activeSubscription.membershipPlan')
            ->latest()
            ->limit(20)
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

        return [
            'summary' => [
                'new_members' => $newMembers,
                'active_members' => $activeMembers,
                'inactive_members' => $inactiveMembers,
                'total_members' => $activeMembers + $inactiveMembers,
            ],
            'members_by_status' => $membersByStatus,
            'daily_new_members' => $dailyNewMembers,
            'recent_members' => $recentMembers,
        ];
    }
}
