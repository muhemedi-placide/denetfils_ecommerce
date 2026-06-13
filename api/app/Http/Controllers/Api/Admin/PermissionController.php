<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Permission::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->values(),
        ]);
    }
}
