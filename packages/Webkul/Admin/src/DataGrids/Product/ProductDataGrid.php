<?php

namespace Webkul\Admin\DataGrids\Product;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\Attribute\Models\Attribute;
use Webkul\Attribute\Models\AttributeValue;
use Webkul\DataGrid\DataGrid;
use Webkul\Tag\Repositories\TagRepository;

class ProductDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder(): Builder
    {
        $tablePrefix = DB::getTablePrefix();

        $queryBuilder = DB::table('products')
            ->leftJoin('product_inventories', 'products.id', '=', 'product_inventories.product_id')
            ->leftJoin('product_tags', 'products.id', '=', 'product_tags.product_id')
            ->leftJoin('tags', 'tags.id', '=', 'product_tags.tag_id')
            ->select(
                'products.id',
                'products.sku',
                'products.name',
                'products.price',
                'tags.name as tag_name',
            )
            ->addSelect(DB::raw('SUM('.$tablePrefix.'product_inventories.in_stock) as total_in_stock'))
            ->addSelect(DB::raw('SUM('.$tablePrefix.'product_inventories.allocated) as total_allocated'))
            ->addSelect(DB::raw('SUM('.$tablePrefix.'product_inventories.in_stock - '.$tablePrefix.'product_inventories.allocated) as total_on_hand'))
            ->groupBy('products.id');

        if (request()->boolean('export')) {
            foreach ($this->getCustomAttributes() as $attribute) {
                $valueColumn = AttributeValue::$attributeTypeFields[$attribute->type] ?? 'text_value';

                $queryBuilder->addSelect(DB::raw(
                    '(SELECT '.$tablePrefix.'attribute_values.'.$valueColumn.
                    ' FROM '.$tablePrefix.'attribute_values'.
                    ' WHERE '.$tablePrefix.'attribute_values.entity_id = '.$tablePrefix.'products.id'.
                    ' AND '.$tablePrefix.'attribute_values.attribute_id = '.(int) $attribute->id.
                    ' AND '.$tablePrefix."attribute_values.entity_type = 'products'".
                    ' LIMIT 1) as '.$attribute->code
                ));
            }
        }

        if (request()->route('id')) {
            $queryBuilder->where('product_inventories.warehouse_id', request()->route('id'));
        }

        $this->addFilter('id', 'products.id');
        $this->addFilter('sku', 'products.sku');
        $this->addFilter('name', 'products.name');
        $this->addFilter('price', 'products.price');
        $this->addFilter('total_in_stock', DB::raw('SUM('.$tablePrefix.'product_inventories.in_stock'));
        $this->addFilter('total_allocated', DB::raw('SUM('.$tablePrefix.'product_inventories.allocated'));
        $this->addFilter('total_on_hand', DB::raw('SUM('.$tablePrefix.'product_inventories.in_stock - '.$tablePrefix.'product_inventories.allocated'));
        $this->addFilter('tag_name', 'tags.name');

        return $queryBuilder;
    }

    /**
     * Add columns.
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'sku',
            'label' => trans('admin::app.products.index.datagrid.sku'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'name',
            'label' => trans('admin::app.products.index.datagrid.name'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'price',
            'label' => trans('admin::app.products.index.datagrid.price'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
            'closure' => function ($row) {
                return core()->formatBasePrice($row->price, 2);
            },
        ]);

        $this->addColumn([
            'index' => 'total_in_stock',
            'label' => trans('admin::app.products.index.datagrid.in-stock'),
            'type' => 'string',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'total_allocated',
            'label' => trans('admin::app.products.index.datagrid.allocated'),
            'type' => 'string',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'total_on_hand',
            'label' => trans('admin::app.products.index.datagrid.on-hand'),
            'type' => 'string',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'tag_name',
            'label' => trans('admin::app.products.index.datagrid.tag-name'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'searchable_dropdown',
            'closure' => fn ($row) => $row->tag_name ?? '--',
            'filterable_options' => [
                'repository' => TagRepository::class,
                'column' => [
                    'label' => 'name',
                    'value' => 'name',
                ],
            ],
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
     * Retrieve the user defined attributes for products.
     */
    protected function getCustomAttributes(): Collection
    {
        return Attribute::query()
            ->where('entity_type', 'products')
            ->where('is_user_defined', 1)
            ->get();
    }

    /**
     * Prepare actions.
     */
    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('products.view')) {
            $this->addAction([
                'index' => 'view',
                'icon' => 'icon-eye',
                'title' => trans('admin::app.products.index.datagrid.view'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.products.view', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('products.edit')) {
            $this->addAction([
                'index' => 'edit',
                'icon' => 'icon-edit',
                'title' => trans('admin::app.products.index.datagrid.edit'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.products.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('products.delete')) {
            $this->addAction([
                'index' => 'delete',
                'icon' => 'icon-delete',
                'title' => trans('admin::app.products.index.datagrid.delete'),
                'method' => 'DELETE',
                'url' => fn ($row) => route('admin.products.delete', $row->id),
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
            'title' => trans('admin::app.products.index.datagrid.delete'),
            'method' => 'POST',
            'url' => route('admin.products.mass_delete'),
        ]);
    }
}
