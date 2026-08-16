<?php

namespace Webkul\Moldable\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Webkul\Attribute\Models\Attribute;
use Webkul\Attribute\Models\AttributeValue;

class MoldableFieldHelper
{
    /**
     * Get all active attribute definitions for a given entity type.
     */
    public static function getFieldsForEntity(string $entityType, bool $onlyUserDefined = false): Collection
    {
        $query = Attribute::query()
            ->with('options')
            ->where('entity_type', $entityType)
            ->orderBy('sort_order')
            ->orderBy('id');

        if ($onlyUserDefined) {
            $query->where('is_user_defined', true);
        }

        return $query->get();
    }

    /**
     * Retrieve all custom field values for an entity instance as a key-value dictionary.
     */
    public static function getValuesDictionary(string $entityType, int $entityId): array
    {
        $attributes = static::getFieldsForEntity($entityType);
        $values = AttributeValue::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->get()
            ->keyBy('attribute_id');

        $result = [];
        foreach ($attributes as $attribute) {
            $valRecord = $values->get($attribute->id);
            if (! $valRecord) {
                $result[$attribute->code] = null;

                continue;
            }

            $column = AttributeValue::$attributeTypeFields[$attribute->type] ?? 'text_value';
            $raw = $valRecord->{$column};

            if (in_array($attribute->type, ['email', 'phone', 'address'], true) && is_string($raw)) {
                $decoded = json_decode($raw, true);
                $result[$attribute->code] = $decoded !== null ? $decoded : $raw;
            } else {
                $result[$attribute->code] = $raw;
            }
        }

        return $result;
    }

    /**
     * Normalize and cast an incoming value to match its Moldable field type.
     */
    public static function normalizeValue(mixed $value, Attribute|string $type): mixed
    {
        $typeKey = $type instanceof Attribute ? $type->type : $type;

        if ($value === null || $value === '') {
            return null;
        }

        return match ($typeKey) {
            'boolean' => in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true) ? 1 : 0,
            'price' => is_numeric($value) ? (float) $value : null,
            'select', 'lookup' => is_numeric($value) ? (int) $value : $value,
            'multiselect', 'checkbox' => is_array($value) ? implode(',', $value) : (string) $value,
            'date' => Carbon::hasFormat($value, 'Y-m-d') ? Carbon::parse($value)->format('Y-m-d') : (string) $value,
            'datetime' => Carbon::parse($value)->format('Y-m-d H:i:s'),
            default => is_array($value) ? json_encode($value) : (string) $value,
        };
    }
}
