<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeadRoutingRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadRoutingRuleController extends Controller
{
    /**
     * Display the lead routing rules from the live table, with add/edit form.
     */
    public function index(Request $request)
    {
        $rules = LeadRoutingRule::orderBy('sort_order')->get();
        $editing = $request->filled('edit') ? LeadRoutingRule::find($request->input('edit')) : null;
        $users = DB::table('users')->where('status', 1)->orderBy('name')->get(['id', 'name']);

        return view('admin::settings.lead_routing', compact('rules', 'editing', 'users'));
    }

    /**
     * Persist a new routing rule.
     */
    public function store(Request $request)
    {
        LeadRoutingRule::create($this->validated($request));

        return redirect()->route('admin.settings.lead_routing');
    }

    /**
     * Update an existing routing rule.
     */
    public function update(Request $request, $id)
    {
        LeadRoutingRule::where('id', $id)->update($this->validated($request));

        return redirect()->route('admin.settings.lead_routing');
    }

    /**
     * Delete a routing rule.
     */
    public function destroy($id)
    {
        LeadRoutingRule::where('id', $id)->delete();

        return redirect()->route('admin.settings.lead_routing');
    }

    /**
     * Shared validation. condition_type limited to what the engine actually evaluates.
     */
    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'condition_type' => 'required|in:value_gte,source_is',
            'condition_value' => 'required|string|max:255',
            'user_id' => 'nullable|integer',
            'sort_order' => 'required|integer|min:0',
        ]);

        $data['status'] = $request->boolean('status');

        return $data;
    }
}
