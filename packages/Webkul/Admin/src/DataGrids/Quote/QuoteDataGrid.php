<?php

namespace Webkul\Admin\DataGrids\Quote;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\Attribute\Models\Attribute;
use Webkul\Attribute\Models\AttributeValue;
use Webkul\DataGrid\DataGrid;
use Webkul\User\Repositories\UserRepository;

class QuoteDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder(): Builder
    {
        $tablePrefix = DB::getTablePrefix();

        $queryBuilder = DB::table('quotes')
            ->addSelect(
                'quotes.id',
                'quotes.subject',
                'quotes.expired_at',
                'quotes.sub_total',
                'quotes.discount_amount',
                'quotes.tax_amount',
                'quotes.adjustment_amount',
                'quotes.grand_total',
                'quotes.created_at',
                'users.id as user_id',
                'users.name as sales_person',
                'quotes.expired_at as expired_quotes'
            )
            ->leftJoin('users', 'quotes.user_id', '=', 'users.id');

        if (request()->boolean('export')) {
            foreach ($this->getCustomAttributes() as $attribute) {
                $valueColumn = AttributeValue::$attributeTypeFields[$attribute->type] ?? 'text_value';

                $queryBuilder->addSelect(DB::raw(
                    '(SELECT '.$tablePrefix.'attribute_values.'.$valueColumn.
                    ' FROM '.$tablePrefix.'attribute_values'.
                    ' WHERE '.$tablePrefix.'attribute_values.entity_id = '.$tablePrefix.'quotes.id'.
                    ' AND '.$tablePrefix.'attribute_values.attribute_id = '.(int) $attribute->id.
                    ' AND '.$tablePrefix."attribute_values.entity_type = 'quotes'".
                    ' LIMIT 1) as '.$attribute->code
                ));
            }
        }

        if ($userIds = bouncer()->getAuthorizedUserIds()) {
            $queryBuilder->whereIn('quotes.user_id', $userIds);
        }

        $this->addFilter('id', 'quotes.id');
        $this->addFilter('user', 'quotes.user_id');
        $this->addFilter('sales_person', 'users.name');
        $this->addFilter('expired_at', 'quotes.expired_at');
        $this->addFilter('created_at', 'quotes.created_at');

        if (request()->input('expired_quotes.in') == 1) {
            $this->addFilter('expired_quotes', DB::raw('DATEDIFF(NOW(), '.$tablePrefix.'quotes.expired_at) >= '.$tablePrefix.'NOW()'));
        } else {
            $this->addFilter('expired_quotes', DB::raw('DATEDIFF(NOW(), '.$tablePrefix.'quotes.expired_at) < '.$tablePrefix.'NOW()'));
        }

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'subject',
            'label' => trans('admin::app.quotes.index.datagrid.subject'),
            'type' => 'string',
            'filterable' => true,
            'searchable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'sales_person',
            'label' => trans('admin::app.quotes.index.datagrid.sales-person'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
            'filterable_type' => 'searchable_dropdown',
            'filterable_options' => [
                'repository' => UserRepository::class,
                'column' => [
                    'label' => 'name',
                    'value' => 'name',
                ],
            ],
        ]);



        $this->addColumn([
            'index' => 'sub_total',
            'label' => trans('admin::app.quotes.index.datagrid.subtotal'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'closure' => fn ($row) => core()->formatBasePrice($row->sub_total, 2),
        ]);

        $this->addColumn([
            'index' => 'discount_amount',
            'label' => trans('admin::app.quotes.index.datagrid.discount'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'closure' => fn ($row) => core()->formatBasePrice($row->discount_amount, 2),
        ]);

        $this->addColumn([
            'index' => 'tax_amount',
            'label' => trans('admin::app.quotes.index.datagrid.tax'),
            'type' => 'string',
            'filterable' => true,
            'sortable' => true,
            'closure' => fn ($row) => core()->formatBasePrice($row->tax_amount, 2),
        ]);

        $this->addColumn([
            'index' => 'adjustment_amount',
            'label' => trans('admin::app.quotes.index.datagrid.adjustment'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => false,
            'closure' => fn ($row) => core()->formatBasePrice($row->adjustment_amount, 2),
        ]);

        $this->addColumn([
            'index' => 'grand_total',
            'label' => trans('admin::app.quotes.index.datagrid.grand-total'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'closure' => fn ($row) => core()->formatBasePrice($row->grand_total, 2),
        ]);

        $this->addColumn([
            'index' => 'expired_at',
            'label' => trans('admin::app.quotes.index.datagrid.expired-at'),
            'type' => 'date',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'closure' => fn ($row) => core()->formatDate($row->expired_at, 'd M Y'),
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('admin::app.quotes.index.datagrid.created-at'),
            'type' => 'date',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
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
     * Retrieve the user defined attributes for quotes.
     */
    protected function getCustomAttributes(): Collection
    {
        return Attribute::query()
            ->where('entity_type', 'quotes')
            ->where('is_user_defined', 1)
            ->get();
    }

    /**
     * Prepare actions.
     */
    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('quotes.edit')) {
            $this->addAction([
                'index' => 'edit',
                'icon' => 'icon-edit',
                'title' => trans('admin::app.quotes.index.datagrid.edit'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.quotes.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('quotes.print')) {
            $this->addAction([
                'index' => 'print',
                'icon' => 'icon-print',
                'title' => trans('admin::app.quotes.index.datagrid.print'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.quotes.print', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('quotes.mail')) {
            $this->addAction([
                'index' => 'mail',
                'icon' => 'icon-mail',
                'title' => trans('admin::app.quotes.index.datagrid.mail'),
                'method' => 'POST',
                'url' => fn ($row) => route('admin.leads.quotes.mail', ['quote_id' => $row->id]),
            ]);
        }

        if (bouncer()->hasPermission('quotes.delete')) {
            $this->addAction([
                'index' => 'delete',
                'icon' => 'icon-delete',
                'title' => trans('admin::app.quotes.index.datagrid.delete'),
                'method' => 'DELETE',
                'url' => fn ($row) => route('admin.quotes.delete', $row->id),
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
            'title' => trans('admin::app.quotes.index.datagrid.delete'),
            'method' => 'POST',
            'url' => route('admin.quotes.mass_delete'),
        ]);

        $this->addMassAction([
            'icon' => 'icon-delete',
            'title' => trans('admin::app.quotes.index.datagrid.delete'),
            'method' => 'POST',
            'url' => route('admin.quotes.mass_delete'),
        ]);
    }
}
