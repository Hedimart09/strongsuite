<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        // Date range filters
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Revenue Metrics
        $metrics = [
            // Total revenue (all-time)
            'total_revenue' => Payment::completed()->sum('amount'),

            // Today's revenue
            'today_revenue' => Payment::completed()
                ->whereDate('payment_date', today())
                ->sum('amount'),

            // This month's revenue
            'month_revenue' => Payment::completed()
                ->whereYear('payment_date', now()->year)
                ->whereMonth('payment_date', now()->month)
                ->sum('amount'),

            // Last month's revenue (for growth calculation)
            'last_month_revenue' => Payment::completed()
                ->whereYear('payment_date', now()->subMonth()->year)
                ->whereMonth('payment_date', now()->subMonth()->month)
                ->sum('amount'),

            // Average transaction
            'average_transaction' => (int) Payment::completed()->avg('amount'),

            // Total payments count
            'total_payments' => Payment::completed()->count(),

            // Outstanding invoices
            'outstanding_amount' => Invoice::whereNotIn('status', ['paid', 'cancelled'])
                ->sum('total_amount'),

            // Overdue invoices
            'overdue_count' => Invoice::where('status', '!=', 'paid')
                ->where('due_date', '<', today())
                ->count(),

            'overdue_amount' => Invoice::where('status', '!=', 'paid')
                ->where('due_date', '<', today())
                ->sum('total_amount'),

            // Due soon (next 7 days)
            'due_soon_count' => Invoice::where('status', '!=', 'paid')
                ->whereBetween('due_date', [today(), today()->addDays(7)])
                ->count(),

            // Payment success rates
            'completed_payments' => Payment::where('status', 'completed')->count(),
            'failed_payments' => Payment::where('status', 'failed')->count(),
            'pending_payments' => Payment::where('status', 'pending')->count(),

            // Total paying members
            'paying_members' => Payment::completed()
                ->distinct('member_id')
                ->count('member_id'),

            // Active subscriptions value
            'active_subscriptions_value' => Subscription::where('status', 'active')
                ->join('membership_plans', 'subscriptions.membership_plan_id', '=', 'membership_plans.id')
                ->sum('membership_plans.price'),
        ];

        // Calculate success rate
        $totalAttempts = $metrics['completed_payments'] + $metrics['failed_payments'];
        $metrics['success_rate'] = $totalAttempts > 0
            ? round(($metrics['completed_payments'] / $totalAttempts) * 100, 1)
            : 0;

        // Calculate month-over-month growth
        $metrics['month_growth'] = $metrics['last_month_revenue'] > 0
            ? round((($metrics['month_revenue'] - $metrics['last_month_revenue']) / $metrics['last_month_revenue']) * 100, 1)
            : 0;

        // Calculate average revenue per member
        $metrics['avg_revenue_per_member'] = $metrics['paying_members'] > 0
            ? (int) ($metrics['total_revenue'] / $metrics['paying_members'])
            : 0;

        // Revenue by payment method
        $revenueByMethod = Payment::completed()
            ->select('payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->payment_method => $item->total]);

        // Revenue by gateway
        $revenueByGateway = Payment::completed()
            ->whereNotNull('payment_gateway')
            ->select('payment_gateway', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_gateway')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->payment_gateway => $item->total]);

        // Daily revenue trend (last 30 days)
        $dailyRevenue = Payment::completed()
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->select(DB::raw('DATE(payment_date) as date'), DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date');

        // Fill missing dates with 0
        $period = new \DatePeriod(
            new \DateTime($startDate),
            new \DateInterval('P1D'),
            (new \DateTime($endDate))->modify('+1 day')
        );

        $chartData = [
            'labels' => [],
            'data' => [],
        ];

        foreach ($period as $date) {
            $dateKey = $date->format('Y-m-d');
            $chartData['labels'][] = $date->format('M d');
            $chartData['data'][] = $dailyRevenue->get($dateKey, 0);
        }

        // Gateway performance (success rate by gateway)
        $gatewayPerformance = DB::table('payments')
            ->select(
                'payment_gateway',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed"),
                DB::raw("SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed")
            )
            ->whereNotNull('payment_gateway')
            ->groupBy('payment_gateway')
            ->get()
            ->map(function ($item) {
                $item->success_rate = $item->total > 0
                    ? round(($item->completed / $item->total) * 100, 1)
                    : 0;

                return $item;
            });

        // Recent payments (last 20)
        $recentPayments = Payment::with(['member', 'subscription.membershipPlan'])
            ->latest('payment_date')
            ->limit(20)
            ->get();

        // Overdue invoices (top 10)
        $overdueInvoices = Invoice::with(['member', 'subscription.membershipPlan'])
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', today())
            ->orderBy('due_date')
            ->limit(10)
            ->get()
            ->map(function ($invoice) {
                $invoice->days_overdue = today()->diffInDays($invoice->due_date);

                return $invoice;
            });

        // Top paying members (filtered date range)
        $topPayingMembers = Payment::completed()
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->select('member_id', DB::raw('SUM(amount) as total_paid'), DB::raw('COUNT(*) as payment_count'))
            ->groupBy('member_id')
            ->orderByDesc('total_paid')
            ->limit(10)
            ->with('member')
            ->get();

        // Members with overdue invoices
        $membersWithOverdue = Invoice::with('member')
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', today())
            ->select('member_id', DB::raw('COUNT(*) as overdue_count'), DB::raw('SUM(total_amount) as overdue_amount'))
            ->groupBy('member_id')
            ->orderByDesc('overdue_amount')
            ->limit(10)
            ->get();

        return Inertia::render('Finance/Dashboard', [
            'metrics' => $metrics,
            'revenue_by_method' => $revenueByMethod,
            'revenue_by_gateway' => $revenueByGateway,
            'chart_data' => $chartData,
            'gateway_performance' => $gatewayPerformance,
            'recent_payments' => $recentPayments,
            'overdue_invoices' => $overdueInvoices,
            'top_paying_members' => $topPayingMembers,
            'members_with_overdue' => $membersWithOverdue,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }
}
