<?php

namespace Webkul\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Attribute\Repositories\AttributeValueRepository;
use Webkul\Lead\Services\LeadDataQualityService;

class LeadForm extends FormRequest
{
    /**
     * @var array
     */
    protected $rules = [];

    /**
     * Create a new form request instance.
     *
     * @return void
     */
    public function __construct(
        protected AttributeRepository $attributeRepository,
        protected AttributeValueRepository $attributeValueRepository
    ) {}

    /**
     * Determine if the product is authorized to make this request.
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
        $qualityService = app(LeadDataQualityService::class);
        $this->rules = $qualityService->getValidationRules(
            request()->all(),
            $this->id,
            request()->has('quick_add')
        );

        return [
            ...$this->rules,
            'products' => 'array',
            'products.*.product_id' => 'sometimes|required|exists:products,id',
            'products.*.name' => 'required_with:products.*.product_id',
            'products.*.price' => 'required_with:products.*.product_id',
            'products.*.quantity' => 'required_with:products.*.product_id',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     */
    public function messages(): array
    {
        return [
            'products.*.product_id.exists' => trans('admin::app.leads.selected-product-not-exist'),
            'products.*.name.required_with' => trans('admin::app.leads.product-name-required'),
            'products.*.price.required_with' => trans('admin::app.leads.product-price-required'),
            'products.*.quantity.required_with' => trans('admin::app.leads.product-quantity-required'),
        ];
    }
}
