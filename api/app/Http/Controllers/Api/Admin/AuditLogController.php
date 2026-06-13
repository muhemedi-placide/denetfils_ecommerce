<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index()
    {
        return AuditLogResource::collection(
            AuditLog::query()
                ->with('actor')
                ->latest('id')
                ->paginate(50),
        );
    }
}
