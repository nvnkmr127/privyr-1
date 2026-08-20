<?php

namespace Webkul\Admin\Http\Controllers\Settings;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Webkul\Admin\DataGrids\Settings\LeadAssignmentRuleDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Lead\Repositories\LeadAssignmentRuleRepository;

class LeadAssignmentRuleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected LeadAssignmentRuleRepository $leadAssignmentRuleRepository) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(LeadAssignmentRuleDataGrid::class)->process();
        }

        return view('admin::settings.lead-assignment-rules.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin::settings.lead-assignment-rules.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): JsonResponse|RedirectResponse
    {
        $this->validate(request(), [
            'name' => 'required',
            'condition_type' => 'required|in:and,or',
            'conditions' => 'required',
            'assignment_type' => 'required|in:user,team',
            'entity_id' => 'required|integer',
            'sort_order' => 'required|integer',
        ]);

        $data = request()->all();
        $data['status'] = $data['is_active'] ?? 0;
        $data['type'] = $data['condition_type'];

        Event::dispatch('settings.lead_assignment_rules.create.before');

        $rule = $this->leadAssignmentRuleRepository->create($data);

        // Decode JSON conditions
        $conditions = is_string($data['conditions']) ? json_decode($data['conditions'], true) : $data['conditions'];
        if (is_array($conditions)) {
            foreach ($conditions as $condition) {
                $rule->conditions()->create([
                    'attribute' => $condition['attribute'] ?? 'source_id',
                    'operator' => $condition['operator'] ?? '==',
                    'value' => $condition['value'] ?? '',
                ]);
            }
        }

        // Attach assignment
        if ($data['assignment_type'] === 'user') {
            $rule->users()->attach([$data['entity_id']]);
        } else {
            $rule->groups()->attach([$data['entity_id']]);
        }

        Event::dispatch('settings.lead_assignment_rules.create.after', $rule);

        session()->flash('success', 'Lead Assignment Rule created successfully.');

        return redirect()->route('admin.settings.lead_assignment_rules.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View|JsonResponse
    {
        $rule = $this->leadAssignmentRuleRepository->findOrFail($id);

        $rule->condition_type = $rule->type;
        $rule->is_active = $rule->status;
        $rule->conditions_json = $rule->conditions;

        if ($rule->users->count() > 0) {
            $rule->assignment_type = 'user';
            $rule->entity_id = $rule->users->first()->id;
        } elseif ($rule->groups->count() > 0) {
            $rule->assignment_type = 'team';
            $rule->entity_id = $rule->groups->first()->id;
        }

        return view('admin::settings.lead-assignment-rules.edit', compact('rule'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id): JsonResponse|RedirectResponse
    {
        $this->validate(request(), [
            'name' => 'required',
            'condition_type' => 'required|in:and,or',
            'conditions' => 'required',
            'assignment_type' => 'required|in:user,team',
            'entity_id' => 'required|integer',
            'sort_order' => 'required|integer',
        ]);

        $data = request()->all();
        $data['status'] = $data['is_active'] ?? 0;
        $data['type'] = $data['condition_type'];

        Event::dispatch('settings.lead_assignment_rules.update.before', $id);

        $rule = $this->leadAssignmentRuleRepository->update($data, $id);

        // Update conditions
        $rule->conditions()->delete();
        $conditions = is_string($data['conditions']) ? json_decode($data['conditions'], true) : $data['conditions'];
        if (is_array($conditions)) {
            foreach ($conditions as $condition) {
                $rule->conditions()->create([
                    'attribute' => $condition['attribute'] ?? 'source_id',
                    'operator' => $condition['operator'] ?? '==',
                    'value' => $condition['value'] ?? '',
                ]);
            }
        }

        // Update assignments
        $rule->users()->detach();
        $rule->groups()->detach();
        if ($data['assignment_type'] === 'user') {
            $rule->users()->attach([$data['entity_id']]);
        } else {
            $rule->groups()->attach([$data['entity_id']]);
        }

        Event::dispatch('settings.lead_assignment_rules.update.after', $rule);

        session()->flash('success', 'Lead Assignment Rule updated successfully.');

        return redirect()->route('admin.settings.lead_assignment_rules.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            Event::dispatch('settings.lead_assignment_rules.delete.before', $id);

            $this->leadAssignmentRuleRepository->delete($id);

            Event::dispatch('settings.lead_assignment_rules.delete.after', $id);

            return response()->json([
                'message' => 'Lead Assignment Rule deleted successfully.',
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => 'Cannot delete this rule.',
            ], 400);
        }
    }
}
