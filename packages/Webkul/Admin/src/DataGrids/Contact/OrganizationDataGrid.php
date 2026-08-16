<?php

namespace Webkul\Admin\DataGrids\Contact;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\Attribute\Models\Attribute;
use Webkul\Attribute\Models\AttributeValue;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\DataGrid\DataGrid;

class OrganizationDataGrid extends DataGrid
{
    /**
     * Create datagrid instance.
     *
     * @return void
     */
    public function __construct(protected PersonRepository $personRepository) {}

    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('organizations')
            ->addSelect(
                'organizations.id',
                'organizations.name',
                'organizations.address',
                'organizations.created_at'
            );

        if (request()->boolean('export')) {
            $tablePrefix = DB::getTablePrefix();

            foreach ($this->getCustomAttributes() as $attribute) {
                $valueColumn = AttributeValue::$attributeTypeFields[$attribute->type] ?? 'text_value';

                $queryBuilder->addSelect(DB::raw(
                    '(SELECT '.$tablePrefix.'attribute_values.'.$valueColumn.
                    ' FROM '.$tablePrefix.'attribute_values'.
                    ' WHERE '.$tablePrefix.'attribute_values.entity_id = '.$tablePrefix.'organizations.id'.
                    ' AND '.$tablePrefix.'attribute_values.attribute_id = '.(int) $attribute->id.
                    ' AND '.$tablePrefix."attribute_values.entity_type = 'organizations'".
                    ' LIMIT 1) as '.$attribute->code
                ));
            }
        }

        if ($userIds = bouncer()->getAuthorizedUserIds()) {
            $queryBuilder->whereIn('organizations.user_id', $userIds);
        }

        $this->addFilter('id', 'organizations.id');

        $this->addFilter('organization', 'organizations.name');

        return $queryBuilder;
    }

    /**
     * Add columns.
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('admin::app.contacts.organizations.index.datagrid.id'),
            'type' => 'integer',
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'name',
            'label' => trans('admin::app.contacts.organizations.index.datagrid.name'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'persons_count',
            'label' => trans('admin::app.contacts.organizations.index.datagrid.persons-count'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => false,
            'filterable' => false,
            'closure' => function ($row) {
                $personsCount = $this->personRepository->findWhere(['organization_id' => $row->id])->count();

                return $personsCount;
            },
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('admin::app.settings.tags.index.datagrid.created-at'),
            'type' => 'date',
            'searchable' => true,
            'filterable' => true,
            'filterable_type' => 'date_range',
            'sortable' => true,
            'closure' => fn ($row) => core()->formatDate($row->created_at),
        ]);

        if (request()->boolean('export')) {
            foreach ($this->getCustomAttributes() as $attribute) {
                $this->addColumn([
                    'index' => $attribute->code,
                    'label' => $attribute->name,
                    'type' => 'string',
                    'searchable' => false,
                    'sortable' => false,
                    'filterable' => false,
                    'visibility' => false,
                ]);
            }
        }
    }

    /**
     * Retrieve the user defined attributes for organizations.
     */
    protected function getCustomAttributes(): Collection
    {
        return Attribute::query()
            ->where('entity_type', 'organizations')
            ->where('is_user_defined', 1)
            ->get();
    }

    /**
     * Prepare actions.
     */
    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('contacts.organizations.edit')) {
            $this->addAction([
                'icon' => 'icon-edit',
                'title' => trans('admin::app.contacts.organizations.index.datagrid.edit'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.contacts.organizations.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('contacts.organizations.delete')) {
            $this->addAction([
                'icon' => 'icon-delete',
                'title' => trans('admin::app.contacts.organizations.index.datagrid.delete'),
                'method' => 'DELETE',
                'url' => fn ($row) => route('admin.contacts.organizations.delete', $row->id),
            ]);
        }
    }

    /**
     * Prepare mass actions.
     */
    public function prepareMassActions(): void
    {
        $this->addMassAction([
            'icon' => 'icon-delete',
            'title' => trans('admin::app.contacts.organizations.index.datagrid.delete'),
            'method' => 'PUT',
            'url' => route('admin.contacts.organizations.mass_delete'),
        ]);
    }
}
