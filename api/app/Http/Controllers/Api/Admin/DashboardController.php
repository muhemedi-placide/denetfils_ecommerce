<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\BackOffice\DashboardResource;
use App\Services\Admin\BackOfficeMetricsService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, BackOfficeMetricsService $metrics): DashboardResource
    {
        $threshold = max(0, min(100, $request->integer('threshold', 5)));
        $locale = in_array($request->query('locale'), ['fr', 'en'], true) ? $request->query('locale') : 'fr';

        return new DashboardResource($metrics->dashboard($threshold, $locale));
    }
}
