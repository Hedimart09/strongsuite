<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Member;
use App\Services\InvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::query()
            ->with('member')
            ->latest('issue_date');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('member', function ($memberQuery) use ($search) {
                        $memberQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('member_id', 'like', "%{$search}%");
                    });
            });
        }

        $invoices = $query->paginate(20);

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['member', 'subscription.membershipPlan', 'payments']);

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice,
        ]);
    }

    public function create(Request $request)
    {
        $members = Member::active()->get();
        $memberId = $request->query('member_id');

        return Inertia::render('Invoices/Create', [
            'members' => $members,
            'selected_member_id' => $memberId ? (int) $memberId : null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'subscription_id' => ['nullable', 'exists:subscriptions,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.amount' => ['required', 'integer', 'min:0'],
            'tax_rate' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'due_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $invoice = DB::transaction(function () use ($validated) {
            $invoiceService = app(InvoiceService::class);

            // Calculate amounts
            $subtotal = collect($validated['items'])->sum('amount');
            $taxRate = $validated['tax_rate'] ?? 0;
            $taxAmount = (int) ($subtotal * ($taxRate / 100));
            $totalAmount = $subtotal + $taxAmount;

            // Generate unique invoice number
            do {
                $number = 'INV-'.str_pad((string) random_int(1, 99999999), 8, '0', STR_PAD_LEFT);
            } while (Invoice::where('invoice_number', $number)->exists());

            $invoice = Invoice::create([
                'invoice_number' => $number,
                'member_id' => $validated['member_id'],
                'subscription_id' => $validated['subscription_id'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'currency' => 'GHS',
                'issue_date' => now(),
                'due_date' => $validated['due_date'],
                'status' => 'sent',
                'notes' => $validated['notes'] ?? null,
                'metadata' => [
                    'items' => $validated['items'],
                    'tax_rate' => $taxRate,
                    'created_by' => auth()->id(),
                ],
            ]);

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice created successfully');
    }

    public function downloadPdf(Invoice $invoice)
    {
        $invoice->load(['member', 'subscription.membershipPlan']);

        $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $invoice]);

        return $pdf->download($invoice->invoice_number.'.pdf');
    }

    public function markAsPaid(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return redirect()->back()
                ->with('error', 'Invoice is already paid');
        }

        $invoice->markAsPaid();

        return redirect()->back()
            ->with('success', 'Invoice marked as paid');
    }
}
