<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\ShippingCarrierRequest;
use App\Http\Resources\Admin\ShippingCarrierAdminResource;
use App\Models\ShippingCarrier;
use App\Services\Logistics\ShippingCarrierManagementService;
use App\Support\ShippingCarrierCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingCarrierController extends Controller
{
    public function schemas(): JsonResponse
    {
        return response()->json(['data' => ShippingCarrierCatalog::providers()]);
    }

    public function index(Request $request)
    {
        $query = ShippingCarrier::query()->orderBy('sort_order')->orderBy('id');

        foreach (['provider', 'environment', 'status'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, (string) $request->query($filter));
            }
        }

        if ($request->filled('is_enabled')) {
            $query->where('is_enabled', filter_var($request->query('is_enabled'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('q')) {
            $search = '%'.trim((string) $request->query('q')).'%';
            $query->where(fn ($query) => $query
                ->where('code', 'like', $search)
                ->orWhere('provider', 'like', $search)
                ->orWhere('display_name->fr', 'like', $search)
                ->orWhere('display_name->en', 'like', $search));
        }

        return ShippingCarrierAdminResource::collection(
            $query->paginate(max(5, min(100, $request->integer('per_page', 25))))
        );
    }

    public function store(ShippingCarrierRequest $request, ShippingCarrierManagementService $carriers): JsonResponse
    {
        $carrier = $carriers->create($request->validated(), $request->user(), $request);

        return response()->json(['data' => new ShippingCarrierAdminResource($carrier)], 201);
    }

    public function show(ShippingCarrier $shippingCarrier): ShippingCarrierAdminResource
    {
        return new ShippingCarrierAdminResource($shippingCarrier);
    }

    public function update(ShippingCarrierRequest $request, ShippingCarrier $shippingCarrier, ShippingCarrierManagementService $carriers): ShippingCarrierAdminResource
    {
        return new ShippingCarrierAdminResource($carriers->update($shippingCarrier, $request->validated(), $request->user(), $request));
    }

    public function activate(Request $request, ShippingCarrier $shippingCarrier, ShippingCarrierManagementService $carriers): ShippingCarrierAdminResource
    {
        abort_unless($request->user()?->can('payments.manage'), 403);

        return new ShippingCarrierAdminResource($carriers->setEnabled($shippingCarrier, true, $request->user(), $request));
    }

    public function deactivate(Request $request, ShippingCarrier $shippingCarrier, ShippingCarrierManagementService $carriers): ShippingCarrierAdminResource
    {
        abort_unless($request->user()?->can('payments.manage'), 403);

        return new ShippingCarrierAdminResource($carriers->setEnabled($shippingCarrier, false, $request->user(), $request));
    }

    public function testConnection(Request $request, ShippingCarrier $shippingCarrier, ShippingCarrierManagementService $carriers): JsonResponse
    {
        abort_unless($request->user()?->can('payments.manage'), 403);

        return response()->json(['data' => $carriers->testConfiguration($shippingCarrier, $request->user(), $request)]);
    }
}
