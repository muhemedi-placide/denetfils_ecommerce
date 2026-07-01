<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\UpdateCustomerRequest;
use App\Http\Resources\Admin\CustomerAdminResource;
use App\Models\Customer;
use App\Services\Core\AuditLogger;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query()
            ->with('role')
            ->withCount(['orders', 'addresses'])
            ->withCount([
                'orders as open_conversations_count' => fn ($query) => $query
                    ->whereHas('conversation', fn ($conversation) => $conversation->where('status', 'open')),
            ])
            ->withSum([
                'orders as total_spent_cents' => fn ($query) => $query->where('payment_status', 'paid'),
            ], 'total_cents')
            ->latest('id');

        if ($request->filled('q')) {
            $search = '%'.trim((string) $request->query('q')).'%';
            $query->where(fn ($query) => $query
                ->where('name', 'like', $search)
                ->orWhere('first_name', 'like', $search)
                ->orWhere('last_name', 'like', $search)
                ->orWhere('email', 'like', $search)
                ->orWhere('phone', 'like', $search));
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->query('status'));
        }

        if ($request->filled('country_code')) {
            $query->where('country_code', strtoupper((string) $request->query('country_code')));
        }

        $perPage = max(5, min(100, $request->integer('per_page', 25)));

        return CustomerAdminResource::collection($query->paginate($perPage));
    }

    public function show(Customer $customer): CustomerAdminResource
    {
        $customer->load([
            'role',
            'customerProfile',
            'addresses' => fn ($query) => $query->orderByDesc('is_default')->latest('id'),
            'orders' => fn ($query) => $query->latest('id')->limit(50),
            'orders.items',
            'orders.addresses',
            'orders.payments.paymentMethod',
            'orders.conversation.messages',
            'orders.shipments.method',
            'orders.shipments.pickupPoint',
        ]);

        $customer->loadCount(['orders', 'addresses']);
        $customer->loadCount([
            'orders as open_conversations_count' => fn ($query) => $query
                ->whereHas('conversation', fn ($conversation) => $conversation->where('status', 'open')),
        ]);
        $customer->loadSum([
            'orders as total_spent_cents' => fn ($query) => $query->where('payment_status', 'paid'),
        ], 'total_cents');

        return new CustomerAdminResource($customer);
    }

    public function update(
        UpdateCustomerRequest $request,
        Customer $customer,
        AuditLogger $auditLogger,
    ): CustomerAdminResource {
        $previousStatus = $customer->status;
        $customer->forceFill(['status' => $request->validated('status')])->save();

        if ($customer->status !== 'active') {
            $customer->tokens()->delete();
        }

        $auditLogger->record($request->user(), 'customers.status_updated', $customer, $request, [
            'previous_status' => $previousStatus,
            'status' => $customer->status,
        ]);

        return $this->show($customer->refresh());
    }
}
