<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\LeadAnalyticsController;
use Illuminate\Http\Request;

class LeadAnalyticsDashboardController extends Controller
{
    public function __construct(
        protected LeadAnalyticsController $analyticsApiController
    ) {}

    /**
     * Display Manager Dashboard using native CRM Blade layout.
     */
    public function index(Request $request)
    {
        $data = json_decode($this->analyticsApiController->index($request)->getContent(), true)['data'] ?? [];

        return view('admin::analytics.reports', compact('data'));
    }
}
