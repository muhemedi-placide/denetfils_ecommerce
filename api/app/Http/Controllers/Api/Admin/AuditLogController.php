<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::query()
            ->with('actor')
            ->latest('id');

        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . trim((string) $request->query('action')) . '%');
        }

        if ($request->filled('actor_id')) {
            $query->where('actor_id', $request->integer('actor_id'));
        }

        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', $request->query('auditable_type'));
        }

        $perPage = max(10, min(100, $request->integer('per_page', 50)));

        return AuditLogResource::collection(
            $query->paginate($perPage),
        );
    }
}
