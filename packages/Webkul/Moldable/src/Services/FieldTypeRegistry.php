<?php

namespace Webkul\Moldable\Services;

class FieldTypeRegistry
{
    /**
     * Get all registered field types with their metadata.
     */
    public static function all(): array
    {
        return [
            'text' => [
                'key' => 'text',
                'label' => 'Text',
                'group' => 'Text',
                'icon' => 'icon-text',
                'storage_type' => 'text_value',
                'supports_options' => false,
                'supports_validation' => true,
                'supports_lookup' => false,
                'supports_required' => true,
                'supports_unique' => true,
                'supports_quick_add' => true,
            ],
            'textarea' => [
                'key' => 'textarea',
                'label' => 'Textarea',
                'group' => 'Text',
                'icon' => 'icon-align-left',
                'storage_type' => 'text_value',
                'supports_options' => false,
                'supports_validation' => true,
                'supports_lookup' => false,
                'supports_required' => true,
                'supports_unique' => false,
                'supports_quick_add' => true,
            ],
            'price' => [
                'key' => 'price',
                'label' => 'Price',
                'group' => 'Number',
                'icon' => 'icon-currency-dollar',
                'storage_type' => 'float_value',
                'supports_options' => false,
                'supports_validation' => true,
                'supports_lookup' => false,
                'supports_required' => true,
                'supports_unique' => false,
                'supports_quick_add' => true,
            ],
            'boolean' => [
                'key' => 'boolean',
                'label' => 'Boolean',
                'group' => 'Selection',
                'icon' => 'icon-toggle',
                'storage_type' => 'boolean_value',
                'supports_options' => false,
                'supports_validation' => false,
                'supports_lookup' => false,
                'supports_required' => false,
                'supports_unique' => false,
                'supports_quick_add' => true,
            ],
            'select' => [
                'key' => 'select',
                'label' => 'Select',
                'group' => 'Selection',
                'icon' => 'icon-chevron-down',
                'storage_type' => 'integer_value',
                'supports_options' => true,
                'supports_validation' => false,
                'supports_lookup' => false,
                'supports_required' => true,
                'supports_unique' => false,
                'supports_quick_add' => true,
            ],
            'multiselect' => [
                'key' => 'multiselect',
                'label' => 'Multiselect',
                'group' => 'Selection',
                'icon' => 'icon-list',
                'storage_type' => 'text_value',
                'supports_options' => true,
                'supports_validation' => false,
                'supports_lookup' => false,
                'supports_required' => true,
                'supports_unique' => false,
                'supports_quick_add' => true,
            ],
            'checkbox' => [
                'key' => 'checkbox',
                'label' => 'Checkbox',
                'group' => 'Selection',
                'icon' => 'icon-check-square',
                'storage_type' => 'text_value',
                'supports_options' => true,
                'supports_validation' => false,
                'supports_lookup' => false,
                'supports_required' => true,
                'supports_unique' => false,
                'supports_quick_add' => true,
            ],
            'email' => [
                'key' => 'email',
                'label' => 'Email',
                'group' => 'Contact',
                'icon' => 'icon-mail',
                'storage_type' => 'json_value',
                'supports_options' => false,
                'supports_validation' => true,
                'supports_lookup' => false,
                'supports_required' => true,
                'supports_unique' => true,
                'supports_quick_add' => true,
            ],
            'phone' => [
                'key' => 'phone',
                'label' => 'Phone',
                'group' => 'Contact',
                'icon' => 'icon-phone',
                'storage_type' => 'json_value',
                'supports_options' => false,
                'supports_validation' => true,
                'supports_lookup' => false,
                'supports_required' => true,
                'supports_unique' => true,
                'supports_quick_add' => true,
            ],
            'address' => [
                'key' => 'address',
                'label' => 'Address',
                'group' => 'Contact',
                'icon' => 'icon-map-pin',
                'storage_type' => 'json_value',
                'supports_options' => false,
                'supports_validation' => false,
                'supports_lookup' => false,
                'supports_required' => true,
                'supports_unique' => false,
                'supports_quick_add' => false,
            ],
            'lookup' => [
                'key' => 'lookup',
                'label' => 'Lookup',
                'group' => 'Relationship',
                'icon' => 'icon-search',
                'storage_type' => 'integer_value',
                'supports_options' => false,
                'supports_validation' => false,
                'supports_lookup' => true,
                'supports_required' => true,
                'supports_unique' => false,
                'supports_quick_add' => true,
            ],
            'date' => [
                'key' => 'date',
                'label' => 'Date',
                'group' => 'Date & Time',
                'icon' => 'icon-calendar',
                'storage_type' => 'date_value',
                'supports_options' => false,
                'supports_validation' => false,
                'supports_lookup' => false,
                'supports_required' => true,
                'supports_unique' => false,
                'supports_quick_add' => true,
            ],
            'datetime' => [
                'key' => 'datetime',
                'label' => 'Datetime',
                'group' => 'Date & Time',
                'icon' => 'icon-clock',
                'storage_type' => 'datetime_value',
                'supports_options' => false,
                'supports_validation' => false,
                'supports_lookup' => false,
                'supports_required' => true,
                'supports_unique' => false,
                'supports_quick_add' => true,
            ],
            'file' => [
                'key' => 'file',
                'label' => 'File',
                'group' => 'File',
                'icon' => 'icon-paperclip',
                'storage_type' => 'text_value',
                'supports_options' => false,
                'supports_validation' => false,
                'supports_lookup' => false,
                'supports_required' => true,
                'supports_unique' => false,
                'supports_quick_add' => false,
            ],
            'image' => [
                'key' => 'image',
                'label' => 'Image',
                'group' => 'File',
                'icon' => 'icon-image',
                'storage_type' => 'text_value',
                'supports_options' => false,
                'supports_validation' => false,
                'supports_lookup' => false,
                'supports_required' => true,
                'supports_unique' => false,
                'supports_quick_add' => false,
            ],
        ];
    }

    /**
     * Get field types grouped by category.
     */
    public static function grouped(): array
    {
        $groups = [
            'Text' => [],
            'Number' => [],
            'Date & Time' => [],
            'Selection' => [],
            'Relationship' => [],
            'Contact' => [],
            'File' => [],
            'Advanced' => [],
        ];

        foreach (static::all() as $type) {
            $grp = $type['group'] ?? 'Advanced';
            $groups[$grp][] = $type;
        }

        return array_filter($groups, fn ($items) => ! empty($items));
    }

    /**
     * Get array of all registered type keys.
     */
    public static function keys(): array
    {
        return array_keys(static::all());
    }

    /**
     * Get definition array for a specific type key.
     */
    public static function get(string $key): ?array
    {
        return static::all()[$key] ?? null;
    }

    /**
     * Check if type key is valid.
     */
    public static function isValid(string $key): bool
    {
        return isset(static::all()[$key]);
    }

    /**
     * Get Laravel validation rule for field type.
     */
    public static function validationRule(): string
    {
        return 'in:'.implode(',', static::keys());
    }
}
