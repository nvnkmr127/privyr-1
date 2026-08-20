<?php

namespace Webkul\Admin\DataGrids\Settings;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class LeadAssignmentRuleDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('lead_assignment_rules')
            ->addSelect(
                'id',
                'name',
                'condition_type',
                'assignment_type',
                'is_active',
                'sort_order',
                'created_at'
            );

        $this->addFilter('id', 'id');

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => trans('admin::app.settings.lead-assignment-rules.index.datagrid.id'),
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('admin::app.settings.lead-assignment-rules.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'condition_type',
            'label'      => trans('admin::app.settings.lead-assignment-rules.index.datagrid.condition-type'),
            'type'       => 'string',
            'sortable'   => true,
            'closure'    => fn ($value) => strtoupper($value->condition_type),
        ]);

        $this->addColumn([
            'index'      => 'assignment_type',
            'label'      => trans('admin::app.settings.lead-assignment-rules.index.datagrid.assignment-type'),
            'type'       => 'string',
            'sortable'   => true,
            'closure'    => fn ($value) => ucfirst($value->assignment_type),
        ]);

        $this->addColumn([
            'index'      => 'is_active',
            'label'      => trans('admin::app.settings.lead-assignment-rules.index.datagrid.is-active'),
            'type'       => 'boolean',
            'sortable'   => true,
            'closure'    => fn ($value) => $value->is_active ? 'Yes' : 'No',
        ]);
        
        $this->addColumn([
            'index'      => 'sort_order',
            'label'      => trans('admin::app.settings.lead-assignment-rules.index.datagrid.priority'),
            'type'       => 'string',
            'sortable'   => true,
        ]);
    }

    /**
     * Prepare actions.
     */
    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('settings.automation.lead_assignment_rules.edit')) {
            $this->addAction([
                'index'  => 'edit',
                'icon'   => 'icon-edit',
                'title'  => trans('admin::app.settings.lead-assignment-rules.index.datagrid.edit'),
                'method' => 'GET',
                'url'    => fn ($row) => route('admin.settings.lead_assignment_rules.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('settings.automation.lead_assignment_rules.delete')) {
            $this->addAction([
                'index'  => 'delete',
                'icon'   => 'icon-delete',
                'title'  => trans('admin::app.settings.lead-assignment-rules.index.datagrid.delete'),
                'method' => 'DELETE',
                'url'    => fn ($row) => route('admin.settings.lead_assignment_rules.delete', $row->id),
            ]);
        }
    }
}
