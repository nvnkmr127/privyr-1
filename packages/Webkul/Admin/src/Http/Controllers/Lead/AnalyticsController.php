<?php

namespace Webkul\Admin\Http\Controllers\Lead;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Lead\Services\LeadAnalyticsService;

class AnalyticsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected LeadAnalyticsService $analyticsService) {}

    /**
     * Display the analytics dashboard.
     *
     * @return View
     */
    public function index(Request $request)
    {
        // Default to last 30 days
        $startDate = $request->input('start_date', now()->subDays(30)->startOfDay()->toDateTimeString());
        $endDate = $request->input('end_date', now()->endOfDay()->toDateTimeString());
        $userId = $request->input('user_id');

        $metrics = $this->analyticsService->getMetrics($startDate, $endDate, $userId);

        return view('admin::analytics.index', compact('metrics', 'startDate', 'endDate', 'userId'));
    }
}
