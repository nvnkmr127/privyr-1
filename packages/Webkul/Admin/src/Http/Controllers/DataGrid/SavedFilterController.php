<?php

namespace Webkul\Admin\Http\Controllers\DataGrid;

use Illuminate\Support\Facades\Event;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\DataGrid\Repositories\SavedFilterRepository;

class SavedFilterController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected SavedFilterRepository $savedFilterRepository) {}

    /**
     * Save filters to the database.
     */
    public function store()
    {
        $userId = auth()->guard()->user()->id;

        $this->validate(request(), [
            'name' => 'required|unique:datagrid_saved_filters,name,NULL,id,src,'.request('src').',user_id,'.$userId,
        ]);

        Event::dispatch('datagrid.saved_filter.create.before');

        $savedFilter = $this->savedFilterRepository->create([
            'user_id' => $userId,
            'name' => request('name'),
            'src' => request('src'),
            'applied' => request('applied'),
            'visibility' => request('visibility', 'private'),
            'description' => request('description'),
            'columns' => request('columns'),
            'sort_column' => request('sort_column'),
            'sort_direction' => request('sort_direction'),
        ]);

        Event::dispatch('datagrid.saved_filter.create.after', $savedFilter);

        return response()->json([
            'data' => $savedFilter,
            'message' => trans('admin::app.components.datagrid.toolbar.filter.saved-success'),
        ]);
    }

    /**
     * Retrieves the saved filters.
     */
    public function get()
    {
        $savedFilters = $this->savedFilterRepository->getModel()
            ->where('src', request()->get('src'))
            ->where(function($query) {
                $query->where('user_id', auth()->guard()->user()->id)
                      ->orWhere('visibility', 'shared');
            })->get();

        return response()->json(['data' => $savedFilters]);
    }

    /**
     * Update the saved filter.
     */
    public function update(int $id)
    {
        $userId = auth()->guard()->user()->id;

        $this->validate(request(), [
            'name' => 'required|unique:datagrid_saved_filters,name,'.$id.',id,src,'.request('src').',user_id,'.$userId,
        ]);

        $savedFilter = $this->savedFilterRepository->getModel()->where('id', $id)->first();

        // Check if user has permission to update
        if ($savedFilter && $savedFilter->user_id != auth()->guard()->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (! $savedFilter) {
            return response()->json([], 404);
        }

        Event::dispatch('datagrid.saved_filter.update.before', $id);

        $updatedFilter = $this->savedFilterRepository->update(request()->only([
            'name',
            'src',
            'applied',
            'visibility',
            'description',
            'columns',
            'sort_column',
            'sort_direction'
        ]), $id);

        Event::dispatch('datagrid.saved_filter.update.after', $updatedFilter);

        return response()->json([
            'data' => $updatedFilter,
            'message' => trans('admin::app.components.datagrid.toolbar.filter.updated-success'),
        ]);
    }

    /**
     * Delete the saved filter.
     */
    public function destroy(int $id)
    {
        Event::dispatch('datagrid.saved_filter.delete.before', $id);

        $success = $this->savedFilterRepository->deleteWhere([
            'id' => $id,
            'user_id' => auth()->guard()->user()->id,
        ]);

        Event::dispatch('datagrid.saved_filter.delete.after', $id);

        if (! $success) {
            return response()->json([
                'message' => trans('admin::app.components.datagrid.toolbar.filter.delete-error'),
            ]);
        }

        return response()->json([
            'message' => trans('admin::app.components.datagrid.toolbar.filter.delete-success'),
        ]);
    }
}
