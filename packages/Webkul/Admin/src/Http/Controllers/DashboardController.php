<?php

namespace Webkul\Admin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Webkul\Admin\Helpers\LeadProductivityDashboard;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected LeadProductivityDashboard $dashboardHelper
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {
        return view('admin::dashboard.index')->with([
            'metrics' => $this->dashboardHelper->getMetrics(),
            'nextActions' => $this->dashboardHelper->getMyNextActions(),
        ]);
    }

    /**
     * Endpoint for async stats (if needed).
     *
     * @return JsonResponse
     */
    public function stats()
    {
        return response()->json([
            'metrics' => $this->dashboardHelper->getMetrics(),
        ]);
    }
}
