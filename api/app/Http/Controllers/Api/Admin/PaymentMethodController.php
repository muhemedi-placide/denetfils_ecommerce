<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\PaymentMethodRequest;
use App\Http\Resources\Admin\PaymentMethodAdminResource;
use App\Models\PaymentMethod;
use App\Services\Payments\PaymentMethodManagementService;
use App\Support\PaymentProviderCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function schemas(): JsonResponse
    {
        return response()->json([
            'data' => PaymentProviderCatalog::providers(),
        ]);
    }

    public function index(Request $request)
    {
        $query = PaymentMethod::query()
            ->orderBy('sort_order')
            ->orderBy('id');

        if ($request->filled('provider')) {
            $query->where('provider', (string) $request->query('provider'));
        }

        if ($request->filled('environment')) {
            $query->where('environment', (string) $request->query('environment'));
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->query('status'));
        }

        if ($request->filled('is_enabled')) {
            $query->where('is_enabled', filter_var($request->query('is_enabled'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('q')) {
            $search = '%'.trim((string) $request->query('q')).'%';

            $query->where(function ($query) use ($search) {
                $query
                    ->where('code', 'like', $search)
                    ->orWhere('provider', 'like', $search)
                    ->orWhere('display_name->fr', 'like', $search)
                    ->orWhere('display_name->en', 'like', $search);
            });
        }

        $perPage = max(5, min(100, $request->integer('per_page', 25)));

        return PaymentMethodAdminResource::collection($query->paginate($perPage));
    }

    public function store(PaymentMethodRequest $request, PaymentMethodManagementService $payments): JsonResponse
    {
        $paymentMethod = $payments->create($request->validated(), $request->user(), $request);

        return response()->json([
            'data' => new PaymentMethodAdminResource($paymentMethod),
        ], 201);
    }

    public function show(PaymentMethod $paymentMethod): PaymentMethodAdminResource
    {
        return new PaymentMethodAdminResource($paymentMethod);
    }

    public function update(PaymentMethodRequest $request, PaymentMethod $paymentMethod, PaymentMethodManagementService $payments): PaymentMethodAdminResource
    {
        return new PaymentMethodAdminResource(
            $payments->update($paymentMethod, $request->validated(), $request->user(), $request),
        );
    }

    public function activate(Request $request, PaymentMethod $paymentMethod, PaymentMethodManagementService $payments): PaymentMethodAdminResource
    {
        abort_unless($request->user()?->can('payments.manage'), 403);

        return new PaymentMethodAdminResource(
            $payments->setEnabled($paymentMethod, true, $request->user(), $request),
        );
    }

    public function deactivate(Request $request, PaymentMethod $paymentMethod, PaymentMethodManagementService $payments): PaymentMethodAdminResource
    {
        abort_unless($request->user()?->can('payments.manage'), 403);

        return new PaymentMethodAdminResource(
            $payments->setEnabled($paymentMethod, false, $request->user(), $request),
        );
    }

    public function testConnection(Request $request, PaymentMethod $paymentMethod, PaymentMethodManagementService $payments): JsonResponse
    {
        abort_unless($request->user()?->can('payments.manage'), 403);

        return response()->json([
            'data' => $payments->testConfiguration($paymentMethod, $request->user(), $request),
        ]);
    }
}
