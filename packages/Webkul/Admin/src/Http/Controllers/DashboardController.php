<?php

namespace Webkul\Admin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Webkul\Admin\Helpers\LeadProductivityDashboard;
use Webkul\Admin\Http\Requests\DashboardFilterRequest;
use Webkul\Lead\Repositories\LeadRepository;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected LeadProductivityDashboard $dashboardHelper,
        protected LeadRepository $leadRepository
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(DashboardFilterRequest $request)
    {
        $filters = $request->validated();
        
        // Include new leads list for the dashboard if needed, or rely on existing routes for full grids.
        // We will fetch new and unassigned leads for the quick views.
        
        $newLeads = $this->leadRepository->getInboxLeads('new', null, $filters, 5);
        $unassignedLeads = $this->leadRepository->getInboxLeads('unassigned', null, $filters, 5);

        return view('admin::dashboard.index')->with([
            'metrics' => $this->dashboardHelper->getMetrics($filters),
            'nextActions' => $this->dashboardHelper->getMyNextActions($filters),
            'newLeads' => $newLeads,
            'unassignedLeads' => $unassignedLeads,
            'filters' => $filters,
        ]);
    }

    /**
     * Endpoint for async stats (if needed).
     *
     * @return JsonResponse
     */
    public function stats(DashboardFilterRequest $request)
    {
        return response()->json([
            'metrics' => $this->dashboardHelper->getMetrics($request->validated()),
        ]);
    }
}
