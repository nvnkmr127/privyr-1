<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DripSequenceStep;
use App\Services\LeadFollowUpSequenceService;
use Illuminate\Http\Request;

class LeadDripSequenceController extends Controller
{
    public function __construct(
        protected LeadFollowUpSequenceService $sequenceService
    ) {}

    /**
     * Display Visual Drip Builder interface using native CRM Blade layout.
     */
    public function index(Request $request)
    {
        $steps = $this->sequenceService->getSequenceSteps();
        $editing = $request->filled('edit') ? DripSequenceStep::find($request->input('edit')) : null;

        return view('admin::settings.drip_sequences', compact('steps', 'editing'));
    }

    /**
     * Persist a new drip step.
     */
    public function store(Request $request)
    {
        DripSequenceStep::create($this->validated($request) + ['is_active' => true]);

        return redirect()->route('admin.settings.drip_sequences');
    }

    /**
     * Update an existing drip step.
     */
    public function update(Request $request, $id)
    {
        DripSequenceStep::where('id', $id)->update($this->validated($request));

        return redirect()->route('admin.settings.drip_sequences');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'day_offset' => 'required|integer|min:0',
            'content' => 'required|string',
        ]);
    }

    /**
     * Delete a drip step.
     */
    public function destroy($id)
    {
        DripSequenceStep::where('id', $id)->delete();

        return redirect()->route('admin.settings.drip_sequences');
    }
}
