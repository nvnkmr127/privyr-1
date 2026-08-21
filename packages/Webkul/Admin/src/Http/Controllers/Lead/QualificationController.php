<?php

namespace Webkul\Admin\Http\Controllers\Lead;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Services\LeadQualificationService;

class QualificationController extends Controller
{
    /**
     * @var LeadRepository
     */
    protected $leadRepository;

    /**
     * @var LeadQualificationService
     */
    protected $qualificationService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        LeadRepository $leadRepository,
        LeadQualificationService $qualificationService
    ) {
        $this->leadRepository = $leadRepository;
        $this->qualificationService = $qualificationService;
    }

    /**
     * Qualify the given lead.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function qualify($id): JsonResponse
    {
        $lead = $this->leadRepository->findOrFail($id);

        try {
            $this->qualificationService->qualify($lead, auth()->guard('user')->user()?->id);

            return response()->json([
                'status'  => true,
                'message' => trans('admin::app.leads.qualified-success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Disqualify the given lead.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function disqualify(Request $request, $id): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $lead = $this->leadRepository->findOrFail($id);

        try {
            $this->qualificationService->disqualify($lead, $request->reason, auth()->guard('user')->user()?->id);

            return response()->json([
                'status'  => true,
                'message' => trans('admin::app.leads.disqualified-success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Requalify (Move to In Review) the given lead.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function requalify($id): JsonResponse
    {
        $lead = $this->leadRepository->findOrFail($id);

        try {
            $this->qualificationService->requalify($lead, auth()->guard('user')->user()?->id);

            return response()->json([
                'status'  => true,
                'message' => trans('admin::app.leads.requalified-success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
