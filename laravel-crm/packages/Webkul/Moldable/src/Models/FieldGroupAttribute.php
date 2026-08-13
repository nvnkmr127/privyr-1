<?php

namespace Webkul\Moldable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Attribute\Models\Attribute;

class FieldGroupAttribute extends Model
{
    protected $table = 'moldable_field_group_attributes';

    protected $fillable = [
        'group_id',
        'attribute_id',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(FieldGroup::class, 'group_id');
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }
}
