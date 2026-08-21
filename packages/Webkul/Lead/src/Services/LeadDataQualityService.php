<?php

namespace Webkul\Lead\Services;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Attribute\Repositories\AttributeValueRepository;
use Webkul\Core\Contracts\Validations\Decimal;
use Webkul\Lead\Contracts\Lead;
use Webkul\Lead\Repositories\PipelineRepository;
use Webkul\Lead\Repositories\SourceRepository;
use Webkul\User\Repositories\UserRepository;

class LeadDataQualityService
{
    public function __construct(
        protected AttributeRepository $attributeRepository,
        protected AttributeValueRepository $attributeValueRepository,
        protected PipelineRepository $pipelineRepository,
        protected SourceRepository $sourceRepository,
        protected UserRepository $userRepository
    ) {}

    /**
     * Normalizes lead data (phone, email, name, sanitization).
     */
    public function normalize(array $data): array
    {
        // Name
        if (! empty($data['name'])) {
            $data['name'] = preg_replace('/\s+/', ' ', trim($data['name']));
        }

        // Emails
        if (isset($data['emails']) && is_array($data['emails'])) {
            foreach ($data['emails'] as &$emailItem) {
                if (! empty($emailItem['value'])) {
                    $emailItem['value'] = strtolower(trim($emailItem['value']));
                }
            }
            if (count($data['emails']) > 0) {
                $data['normalized_primary_email'] = $data['emails'][0]['value'] ?? null;
            }
        }

        // Phones
        if (isset($data['phones']) && is_array($data['phones'])) {
            foreach ($data['phones'] as &$phoneItem) {
                if (! empty($phoneItem['value'])) {
                    $phoneItem['value'] = app(LeadDuplicateService::class)->normalizePhone($phoneItem['value']);
                }
            }
            if (count($data['phones']) > 0) {
                $data['normalized_primary_phone'] = $data['phones'][0]['value'] ?? null;
            }
        }

        // Basic Sanitization for string fields (title, description, etc)
        // We'll strip tags from standard string fields to prevent XSS
        $textFields = ['title', 'description', 'organization'];
        foreach ($textFields as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = strip_tags($data[$field]);
            }
        }

        return $data;
    }

    /**
     * Centralized validation rules generator for Leads.
     */
    public function getValidationRules(array $data, ?int $leadId = null, bool $quickAdd = false): array
    {
        $rules = [];

        // Build base query for attributes
        $query = $this->attributeRepository->where('entity_type', 'leads');

        if ($quickAdd) {
            $query = $query->where('quick_add', 1);
        }

        $attributes = $query->get();

        foreach ($attributes as $attribute) {
            $validations = [];

            if ($attribute->type === 'boolean') {
                continue;
            } elseif ($attribute->type === 'address') {
                if (! $attribute->is_required) {
                    continue;
                }
                $validations = [
                    $attribute->code.'.address' => 'required',
                    $attribute->code.'.country' => 'required',
                    $attribute->code.'.state' => 'required',
                    $attribute->code.'.city' => 'required',
                    $attribute->code.'.postcode' => 'required',
                ];
            } elseif ($attribute->type === 'email') {
                $validations = [
                    $attribute->code => [$attribute->is_required ? 'required' : 'nullable'],
                    $attribute->code.'.*.value' => [$attribute->is_required ? 'required' : 'nullable', 'email'],
                    $attribute->code.'.*.label' => $attribute->is_required ? 'required' : 'nullable',
                ];
            } elseif ($attribute->type === 'phone') {
                $validations = [
                    $attribute->code => [$attribute->is_required ? 'required' : 'nullable'],
                    $attribute->code.'.*.value' => [$attribute->is_required ? 'required' : 'nullable'],
                    $attribute->code.'.*.label' => $attribute->is_required ? 'required' : 'nullable',
                ];
            } else {
                $validations[$attribute->code] = [$attribute->is_required ? 'required' : 'nullable'];

                if ($attribute->type === 'text' && $attribute->validation) {
                    array_push($validations[$attribute->code],
                        $attribute->validation === 'decimal'
                        ? new Decimal
                        : $attribute->validation
                    );
                }

                if ($attribute->type === 'price') {
                    array_push($validations[$attribute->code], new Decimal);
                }
            }

            if ($attribute->is_unique) {
                $uniqueField = in_array($attribute->type, ['email', 'phone'])
                    ? $attribute->code.'.*.value'
                    : $attribute->code;

                array_push($validations[$uniqueField], function ($field, $value, $fail) use ($attribute, $leadId) {
                    if (! $this->attributeValueRepository->isValueUnique(
                        $leadId,
                        $attribute->entity_type,
                        $attribute,
                        $value
                    )) {
                        $fail('The value has already been taken.');
                    }
                });
            }

            $rules = array_merge($rules, $validations);
        }

        // Basic relational rules
        if (isset($data['lead_source_id'])) {
            $rules['lead_source_id'][] = 'exists:lead_sources,id';
        }
        if (isset($data['user_id'])) {
            $rules['user_id'][] = 'exists:users,id';
        }
        if (isset($data['lead_pipeline_id'])) {
            $rules['lead_pipeline_id'][] = 'exists:lead_pipelines,id';
        }
        if (isset($data['lead_pipeline_stage_id'])) {
            $rules['lead_pipeline_stage_id'][] = 'exists:lead_pipeline_stages,id';
        }

        return $rules;
    }

    /**
     * Validates data against the EAV rules and standard rules.
     * Throws an exception or returns errors.
     */
    public function validate(array $data, ?int $leadId = null): array
    {
        $rules = $this->getValidationRules($data, $leadId);
        $messages = [];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }

        return [];
    }

    /**
     * Evaluates data quality state for a Lead and updates it.
     */
    public function calculateQualityState($lead): void
    {
        $issues = [];

        // Example dynamic checks based on required fields and basic presence
        if (empty($lead->name)) {
            $issues[] = 'Missing person name';
        }

        $hasValidEmail = false;
        if (! empty($lead->emails)) {
            foreach ($lead->emails as $email) {
                if (! empty($email['value']) && filter_var($email['value'], FILTER_VALIDATE_EMAIL)) {
                    $hasValidEmail = true;
                    break;
                }
            }
        }
        if (! $hasValidEmail) {
            $issues[] = 'Missing or invalid email';
        }

        $hasPhone = false;
        if (! empty($lead->phones)) {
            foreach ($lead->phones as $phone) {
                if (! empty($phone['value'])) {
                    $hasPhone = true;
                    break;
                }
            }
        }
        if (! $hasPhone) {
            $issues[] = 'Missing phone number';
        }

        if (empty($lead->lead_source_id)) {
            $issues[] = 'Missing source';
        }

        if (empty($lead->user_id)) {
            $issues[] = 'Missing owner';
        }

        // Check required EAV fields
        $requiredAttributes = $this->attributeRepository->where('entity_type', 'leads')->where('is_required', 1)->get();
        foreach ($requiredAttributes as $attribute) {
            $code = $attribute->code;
            if (empty($lead->{$code})) {
                if (! in_array("Missing required field: {$attribute->name}", $issues)) {
                    $issues[] = "Missing required field: {$attribute->name}";
                }
            }
        }

        $newState = 'complete';
        if (count($issues) > 0) {
            $newState = count($issues) > 3 ? 'incomplete' : 'needs_review';
        }

        $oldState = $lead->data_quality_state;

        // Compare and update if changed
        if ($oldState !== $newState || $lead->data_quality_issues !== $issues) {
            // Bypass events for this specific save to prevent infinite loops
            $lead->newQuery()->where('id', $lead->id)->update([
                'data_quality_state' => $newState,
                'data_quality_issues' => json_encode($issues),
            ]);

            $lead->data_quality_state = $newState;
            $lead->data_quality_issues = $issues;

            Event::dispatch('lead.data_quality.changed', [
                'lead' => $lead,
                'old_state' => $oldState,
                'new_state' => $newState,
                'issues' => $issues,
            ]);
        }
    }
}
