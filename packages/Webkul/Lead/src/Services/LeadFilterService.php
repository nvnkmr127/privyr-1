<?php

namespace Webkul\Lead\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\Attribute\Repositories\AttributeRepository;

class LeadFilterService
{
    public function __construct(
        protected AttributeRepository $attributeRepository
    ) {}

    /**
     * Apply a set of advanced filters to a Lead query builder.
     * 
     * @param Builder $query The base query builder (from LeadDataGrid or Kanban).
     * @param array $filters The structured filters payload.
     * @param string $matchType 'all' or 'any' (AND vs OR).
     * @param array $availableColumns The definition of columns from the DataGrid.
     */
    public function applyAdvancedFilters(Builder $query, array $filters, string $matchType = 'all', array $availableColumns = [])
    {
        $booleanFn = $matchType === 'any' ? 'orWhere' : 'where';
        
        $query->where(function ($groupQuery) use ($filters, $booleanFn, $availableColumns) {
            foreach ($filters as $columnName => $requestedValues) {
                // Determine if this is a custom attribute
                $attribute = $this->attributeRepository->findOneWhere([
                    'entity_type' => 'leads',
                    'code' => $columnName
                ]);

                if ($attribute) {
                    $this->applyEavFilter($groupQuery, $attribute->id, $attribute->type, $requestedValues, $booleanFn);
                } elseif ($columnName === 'all') {
                    // Global Search
                    $this->applyGlobalSearch($groupQuery, $requestedValues, $booleanFn, $availableColumns);
                } else {
                    // Normal column filtering
                    $this->applyStandardFilter($groupQuery, $columnName, $requestedValues, $booleanFn, $availableColumns);
                }
            }
        });

        return $query;
    }

    /**
     * Apply EAV filter using EXISTS to prevent JOIN proliferation.
     */
    protected function applyEavFilter(Builder $query, $attributeId, $attributeType, $values, $booleanFn)
    {
        $valueColumn = \Webkul\Attribute\Models\AttributeValue::$attributeTypeFields[$attributeType] ?? 'text_value';
        
        // E.g. whereExists (SELECT 1 FROM attribute_values WHERE entity_id = leads.id AND attribute_id = X AND text_value IN (...))
        $query->{$booleanFn . 'Exists'}(function ($subQuery) use ($attributeId, $valueColumn, $values) {
            $subQuery->select(DB::raw(1))
                ->from('attribute_values')
                ->whereColumn('attribute_values.entity_id', 'leads.id')
                ->where('attribute_values.entity_type', 'leads')
                ->where('attribute_values.attribute_id', $attributeId);

            // Handle operators if present (e.g., ['operator' => 'contains', 'value' => 'foo'])
            // For now, assume simple arrays or single values as passed by DataGrid
            if (is_array($values) && isset($values['operator'])) {
                $this->applyOperator($subQuery, 'attribute_values.' . $valueColumn, $values['operator'], $values['value']);
            } elseif (is_array($values)) {
                $subQuery->whereIn('attribute_values.' . $valueColumn, $values);
            } else {
                $subQuery->where('attribute_values.' . $valueColumn, $values);
            }
        });
    }

    /**
     * Apply global search across multiple core fields.
     */
    protected function applyGlobalSearch(Builder $query, $requestedValues, $booleanFn, $availableColumns)
    {
        $query->{$booleanFn}(function ($searchQuery) use ($requestedValues, $availableColumns) {
            foreach ($requestedValues as $value) {
                $searchQuery->where(function ($subQuery) use ($value, $availableColumns) {
                    // Normalized phone search
                    $cleanPhone = preg_replace('/[^0-9]/', '', $value);
                    if ($cleanPhone) {
                        $subQuery->orWhere('leads.contact_numbers', 'LIKE', '%"value":"%' . $cleanPhone . '%"%');
                    }
                    
                    // Case-insensitive email search handled natively by LIKE in most SQL
                    $subQuery->orWhere('leads.emails', 'LIKE', '%"value":"%' . $value . '%"%');
                    
                    // Standard columns
                    foreach ($availableColumns as $col) {
                        if ($col['searchable'] && !($col['is_custom'] ?? false)) {
                            // Extract actual db column mapping if available, else use index
                            $dbColumn = $col['index']; 
                            if ($dbColumn === 'person_name' || $dbColumn === 'title') {
                                $dbColumn = 'leads.' . $dbColumn;
                            }
                            // Avoid searching on computed columns or complex relationships during global text search if not mapped properly,
                            // But fallback to standard DataGrid behavior where appropriate.
                            if (strpos($dbColumn, '.') !== false || in_array($dbColumn, ['leads.title', 'leads.person_name'])) {
                                $subQuery->orWhere($dbColumn, 'LIKE', '%' . $value . '%');
                            }
                        }
                    }
                });
            }
        });
    }

    /**
     * Standard column filtering.
     */
    protected function applyStandardFilter(Builder $query, $columnName, $values, $booleanFn, $availableColumns)
    {
        // Simple mapping to prevent SQL injection and map index to real DB columns
        $dbColumn = $columnName;
        // DataGrid normally handles this via `$column->processFilter`. 
        // Here we can either instantiate the Column object or apply simple logic.
        
        if (strpos($dbColumn, '.') === false && !in_array($dbColumn, ['id', 'rotten_lead', 'sales_person', 'team_name'])) {
            $dbColumn = 'leads.' . $dbColumn; // Fallback
        }

        if (is_array($values) && isset($values['operator'])) {
            $query->{$booleanFn}(function ($q) use ($dbColumn, $values) {
                $this->applyOperator($q, $dbColumn, $values['operator'], $values['value']);
            });
        } else {
            // Check if it's a date range
            if (is_array($values) && (isset($values[0]) || isset($values['from']))) {
                if (isset($values['from']) || isset($values['to'])) {
                    $query->{$booleanFn}(function($q) use ($dbColumn, $values) {
                        if (!empty($values['from'])) {
                            $q->where($dbColumn, '>=', $values['from']);
                        }
                        if (!empty($values['to'])) {
                            $q->where($dbColumn, '<=', $values['to']);
                        }
                    });
                } else {
                    $query->{$booleanFn . 'In'}($dbColumn, $values);
                }
            } else {
                $query->{$booleanFn}($dbColumn, is_array($values) ? implode(',', $values) : $values);
            }
        }
    }

    protected function applyOperator(Builder $query, $column, $operator, $value)
    {
        switch (strtolower($operator)) {
            case 'contains':
                $query->where($column, 'LIKE', '%' . $value . '%');
                break;
            case 'starts_with':
                $query->where($column, 'LIKE', $value . '%');
                break;
            case 'equals':
                $query->where($column, '=', $value);
                break;
            case 'not_equals':
                $query->where($column, '!=', $value);
                break;
            case 'greater_than':
                $query->where($column, '>', $value);
                break;
            case 'less_than':
                $query->where($column, '<', $value);
                break;
            case 'is_empty':
                $query->where(function($q) use ($column) {
                    $q->whereNull($column)->orWhere($column, '=', '');
                });
                break;
            case 'is_not_empty':
                $query->whereNotNull($column)->where($column, '!=', '');
                break;
            case 'in':
                $query->whereIn($column, is_array($value) ? $value : explode(',', $value));
                break;
            default:
                $query->where($column, '=', $value);
        }
    }
}
