<?php

namespace Webkul\Admin\Http\Controllers\Lead;

use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Repositories\LeadAuditRepository;
use Webkul\Admin\DataGrids\Lead\LeadAuditDataGrid;

class AuditController extends Controller
{
    use AuthorizesRequests;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected LeadRepository $leadRepository,
        protected LeadAuditRepository $leadAuditRepository
    ) {
    }

    /**
     * Display a listing of the resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function index($id)
    {
        $lead = $this->leadRepository->findOrFail($id);

        if (! bouncer()->hasPermission('leads.view_audit')) {
            abort(403, 'This action is unauthorized.');
        }

        // Use standard datagrid or return JSON depending on how UI is structured.
        // Assuming we return JSON for a Vue component.
        $audits = $this->leadAuditRepository->where('lead_id', $id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $audits->items(),
            'meta' => [
                'current_page' => $audits->currentPage(),
                'last_page'    => $audits->lastPage(),
                'total'        => $audits->total(),
            ]
        ]);
    }
}
