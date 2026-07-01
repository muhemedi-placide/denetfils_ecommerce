<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\InvoiceAdminResource;
use App\Models\Invoice;
use App\Support\MoneyFormatter;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::query()->with('order')->latest('id');

        if ($request->filled('q')) {
            $search = '%'.trim((string) $request->query('q')).'%';
            $query->where(fn ($query) => $query
                ->where('invoice_number', 'like', $search)
                ->orWhereHas('order', fn ($order) => $order
                    ->where('order_number', 'like', $search)
                    ->orWhere('customer_name', 'like', $search)
                    ->orWhere('customer_email', 'like', $search)));
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->query('status'));
        }

        if ($request->filled('payment_status')) {
            $query->whereHas('order', fn ($order) => $order->where(
                'payment_status',
                (string) $request->query('payment_status'),
            ));
        }

        if ($request->filled('date_from')) {
            $query->where('issued_at', '>=', (string) $request->query('date_from').' 00:00:00');
        }

        if ($request->filled('date_to')) {
            $query->where('issued_at', '<=', (string) $request->query('date_to').' 23:59:59');
        }

        $summaryQuery = clone $query;
        $locale = in_array($request->query('locale'), ['fr', 'en'], true)
            ? (string) $request->query('locale')
            : 'fr';
        $totalCents = (int) (clone $summaryQuery)->sum('total_cents');
        $perPage = max(5, min(100, $request->integer('per_page', 25)));

        return InvoiceAdminResource::collection($query->paginate($perPage))->additional([
            'summary' => [
                'total_invoices' => (clone $summaryQuery)->count(),
                'draft_invoices' => (clone $summaryQuery)->where('status', 'draft')->count(),
                'issued_invoices' => (clone $summaryQuery)->where('status', 'issued')->count(),
                'paid_invoices' => (clone $summaryQuery)->where('status', 'paid')->count(),
                'total_cents' => $totalCents,
                'formatted_total' => MoneyFormatter::format($totalCents, 'EUR', $locale),
            ],
        ]);
    }

    public function show(Invoice $invoice): InvoiceAdminResource
    {
        return new InvoiceAdminResource($invoice->load([
            'order.customer',
            'order.items',
            'order.addresses',
            'order.payments.paymentMethod',
            'order.conversation.messages',
            'order.shipments.method',
            'order.shipments.pickupPoint',
        ]));
    }
}
