<?php

namespace Webkul\Admin\Http\Controllers\Settings;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Webkul\Admin\DataGrids\Settings\SourceDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Lead\Repositories\SourceRepository;

class SourceController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected SourceRepository $sourceRepository) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(SourceDataGrid::class)->process();
        }

        $pipelines = app(\Webkul\Lead\Repositories\PipelineRepository::class)->all();
        $users = app(\Webkul\User\Repositories\UserRepository::class)->all();

        return view('admin::settings.sources.index', compact('pipelines', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): JsonResponse
    {
        $this->validate(request(), [
            'name' => ['required', 'unique:lead_sources,name'],
            'is_active' => 'sometimes|boolean',
            'default_lead_pipeline_id' => 'nullable|integer|exists:lead_pipelines,id',
            'default_lead_pipeline_stage_id' => 'nullable|integer|exists:lead_pipeline_stages,id',
            'default_user_id' => 'nullable|integer|exists:users,id',
        ]);

        Event::dispatch('settings.source.create.before');

        $data = request()->only([
            'name', 'is_active', 'default_lead_pipeline_id', 
            'default_lead_pipeline_stage_id', 'default_user_id'
        ]);
        
        $data['is_active'] = request()->has('is_active') ? request('is_active') : 1;

        $source = $this->sourceRepository->create($data);

        Event::dispatch('settings.source.create.after', $source);

        return new JsonResponse([
            'data' => $source,
            'message' => trans('admin::app.settings.sources.index.create-success'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View|JsonResponse
    {
        $source = $this->sourceRepository->findOrFail($id);

        return new JsonResponse([
            'data' => $source,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id): JsonResponse
    {
        $this->validate(request(), [
            'name' => 'required|unique:lead_sources,name,'.$id,
            'is_active' => 'sometimes|boolean',
            'default_lead_pipeline_id' => 'nullable|integer|exists:lead_pipelines,id',
            'default_lead_pipeline_stage_id' => 'nullable|integer|exists:lead_pipeline_stages,id',
            'default_user_id' => 'nullable|integer|exists:users,id',
        ]);

        Event::dispatch('settings.source.update.before', $id);

        $data = request()->only([
            'name', 'is_active', 'default_lead_pipeline_id', 
            'default_lead_pipeline_stage_id', 'default_user_id'
        ]);

        $data['is_active'] = request()->has('is_active') ? request('is_active') : 0;

        $source = $this->sourceRepository->update($data, $id);

        Event::dispatch('settings.source.update.after', $source);

        return new JsonResponse([
            'data' => $source,
            'message' => trans('admin::app.settings.sources.index.update-success'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $source = $this->sourceRepository->findOrFail($id);

        if ($source->leads()->count() > 0) {
            return new JsonResponse([
                'message' => trans('admin::app.settings.sources.index.delete-failed-associated-leads'),
            ], 400);
        }

        try {
            Event::dispatch('settings.source.delete.before', $id);

            $source->delete();

            Event::dispatch('settings.source.delete.after', $id);

            return new JsonResponse([
                'message' => trans('admin::app.settings.sources.index.delete-success'),
            ], 200);
        } catch (Exception $exception) {
            return new JsonResponse([
                'message' => trans('admin::app.settings.sources.index.delete-failed'),
            ], 400);
        }
    }
}
