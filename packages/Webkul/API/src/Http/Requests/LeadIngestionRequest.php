<?php

namespace Webkul\API\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeadIngestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'source' => 'nullable|string|max:255',
            'origin' => 'required|string|max:255',
            'campaign' => 'nullable|string|max:255',
            'medium' => 'nullable|string|max:255',
            'content' => 'nullable|string|max:255',
            'term' => 'nullable|string|max:255',
            'landing_page' => 'nullable|string|max:255',
            'form' => 'nullable|string|max:255',
            'external_source' => 'nullable|string|max:255',
            'external_id' => 'nullable|string|max:255',
            'pipeline' => 'nullable|integer',
            'stage' => 'nullable|integer',
            'owner' => 'nullable|integer',
            'team' => 'nullable|integer',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'custom_attributes' => 'nullable|array',
            'metadata' => 'nullable|array',
        ];
    }
}
