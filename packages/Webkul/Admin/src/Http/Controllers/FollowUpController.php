<?php

namespace Webkul\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Webkul\Activity\Repositories\ActivityRepository;
use Webkul\Lead\Repositories\LeadRepository;

class FollowUpController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ActivityRepository $activityRepository,
        protected LeadRepository $leadRepository
    ) {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return app(\Webkul\Admin\DataGrids\FollowUpDataGrid::class)->toJson();
        }

        return view('admin::follow-ups.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'type'          => 'required',
            'lead_id'       => 'required|exists:leads,id',
            'title'         => 'required',
            'schedule_from' => 'required|date',
            'schedule_to'   => 'nullable|date|after_or_equal:schedule_from',
        ]);

        Event::dispatch('followup.create.before');

        $data = $request->all();
        $data['user_id'] = auth()->user()->id;
        $data['status'] = 'pending';

        $followUp = $this->activityRepository->create($data);

        Event::dispatch('followup.create.after', $followUp);

        return response()->json([
            'message' => trans('admin::app.follow-ups.create-success'),
            'data'    => $followUp,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'type'          => 'required',
            'title'         => 'required',
            'schedule_from' => 'required|date',
        ]);

        Event::dispatch('followup.update.before', $id);

        $followUp = $this->activityRepository->update($request->all(), $id);

        Event::dispatch('followup.update.after', $followUp);

        return response()->json([
            'message' => trans('admin::app.follow-ups.update-success'),
            'data'    => $followUp,
        ]);
    }

    /**
     * Mark the follow up as completed.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function complete($id)
    {
        Event::dispatch('followup.complete.before', $id);

        $followUp = $this->activityRepository->findOrFail($id);
        
        if ($followUp->status === 'completed') {
            return response()->json([
                'message' => trans('admin::app.follow-ups.already-completed'),
            ], 400);
        }

        $followUp->update([
            'status'          => 'completed',
            'is_done'         => 1, // for backward compatibility
            'completed_at'    => now(),
            'completed_by_id' => auth()->user()->id,
        ]);

        Event::dispatch('followup.complete.after', $followUp);

        return response()->json([
            'message' => trans('admin::app.follow-ups.complete-success'),
            'data'    => $followUp,
        ]);
    }

    /**
     * Cancel the follow up.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel($id)
    {
        Event::dispatch('followup.cancel.before', $id);

        $followUp = $this->activityRepository->findOrFail($id);

        if ($followUp->status === 'completed') {
            return response()->json([
                'message' => trans('admin::app.follow-ups.cannot-cancel-completed'),
            ], 400);
        }

        $followUp->update([
            'status' => 'cancelled',
        ]);

        Event::dispatch('followup.cancel.after', $followUp);

        return response()->json([
            'message' => trans('admin::app.follow-ups.cancel-success'),
            'data'    => $followUp,
        ]);
    }

    /**
     * Reschedule the follow up.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function reschedule(Request $request, $id)
    {
        $this->validate($request, [
            'schedule_from' => 'required|date',
            'schedule_to'   => 'nullable|date|after_or_equal:schedule_from',
        ]);

        Event::dispatch('followup.reschedule.before', $id);

        $followUp = $this->activityRepository->findOrFail($id);

        if ($followUp->status === 'completed') {
            return response()->json([
                'message' => trans('admin::app.follow-ups.cannot-reschedule-completed'),
            ], 400);
        }

        $followUp->update([
            'schedule_from' => $request->schedule_from,
            'schedule_to'   => $request->schedule_to,
        ]);

        Event::dispatch('followup.reschedule.after', $followUp);

        return response()->json([
            'message' => trans('admin::app.follow-ups.reschedule-success'),
            'data'    => $followUp,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        Event::dispatch('followup.delete.before', $id);

        $this->activityRepository->delete($id);

        Event::dispatch('followup.delete.after', $id);

        return response()->json([
            'message' => trans('admin::app.follow-ups.delete-success'),
        ]);
    }
}
