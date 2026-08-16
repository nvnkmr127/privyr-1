<?php

namespace Webkul\Moldable\Services;

use Illuminate\Support\Facades\View;
use Webkul\Attribute\Models\Attribute;
use Webkul\Attribute\Models\AttributeValue;

class AttributeRenderer
{
    /**
     * Render an attribute dynamic input field HTML.
     */
    public static function render(Attribute $attribute, mixed $value = null, mixed $entity = null): string
    {
        if ($value instanceof AttributeValue) {
            $value = $value->value;
        } elseif ($value === null && $entity && isset($entity->{$attribute->code})) {
            $value = $entity->{$attribute->code};
        }

        return View::make('moldable::components.attribute-renderer', [
            'attribute' => $attribute,
            'value' => $value,
            'entity' => $entity,
        ])->render();
    }
}
