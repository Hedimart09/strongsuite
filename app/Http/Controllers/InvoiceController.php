<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
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
