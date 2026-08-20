<?php

namespace Webkul\Admin\DataGrids;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class FollowUpDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('activities')
            ->select(
                'activities.id',
                'activities.title',
                'activities.type',
                'activities.status',
                'activities.priority',
                'activities.schedule_from',
                'activities.schedule_to',
                'leads.title as lead_title',
                'leads.id as lead_id',
                'users.name as user_name'
            )
            ->join('leads', 'activities.lead_id', '=', 'leads.id')
            ->leftJoin('users', 'activities.user_id', '=', 'users.id');

        $currentUser = auth()->guard('user')->user();

        if ($currentUser->view_permission != 'global') {
            if ($currentUser->view_permission == 'group') {
                $queryBuilder->whereIn('activities.user_id', $this->userRepository->getCurrentUserGroupsUserIds());
            } else {
                $queryBuilder->where('activities.user_id', $currentUser->id);
            }
        }

        $this->addFilter('id', 'activities.id');
        $this->addFilter('title', 'activities.title');
        $this->addFilter('type', 'activities.type');
        $this->addFilter('status', 'activities.status');
        $this->addFilter('priority', 'activities.priority');
        $this->addFilter('user_name', 'users.name');
        $this->addFilter('schedule_from', 'activities.schedule_from');
        $this->addFilter('lead_title', 'leads.title');

        return $queryBuilder;
    }

    /**
     * Add columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index' => 'title',
            'label' => trans('admin::app.datagrid.title'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                return '<a href="'.route('admin.leads.view', $row->lead_id).'">'.$row->title.'</a>';
            },
        ]);

        $this->addColumn([
            'index' => 'lead_title',
            'label' => trans('admin::app.datagrid.lead'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'type',
            'label' => trans('admin::app.datagrid.type'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => trans('admin::app.datagrid.status'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'priority',
            'label' => trans('admin::app.datagrid.priority'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'schedule_from',
            'label' => trans('admin::app.datagrid.schedule_from'),
            'type' => 'datetime',
            'searchable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'user_name',
            'label' => trans('admin::app.datagrid.user'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        $this->addAction([
            'title' => trans('admin::app.datagrid.edit'),
            'method' => 'GET',
            'route' => 'admin.activities.edit',
            'icon' => 'icon-edit',
        ]);

        $this->addAction([
            'title' => trans('admin::app.datagrid.delete'),
            'method' => 'DELETE',
            'route' => 'admin.follow_ups.destroy',
            'icon' => 'icon-delete',
        ]);
    }
}
