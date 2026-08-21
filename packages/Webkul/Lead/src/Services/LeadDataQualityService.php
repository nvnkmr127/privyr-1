<?php

namespace Webkul\Lead\Services;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Lead\Contracts\Lead;
use Webkul\Lead\Repositories\PipelineRepository;
use Webkul\Lead\Repositories\SourceRepository;
use Webkul\User\Repositories\UserRepository;

class LeadDataQualityService
{
    public function __construct(
        protected AttributeRepository $attributeRepository,
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
        if (!empty($data['person_name'])) {
            $data['person_name'] = preg_replace('/\s+/', ' ', trim($data['person_name']));
        }
        
        // Emails
        if (isset($data['emails']) && is_array($data['emails'])) {
            foreach ($data['emails'] as &$emailItem) {
                if (!empty($emailItem['value'])) {
                    $emailItem['value'] = strtolower(trim($emailItem['value']));
                }
            }
            if (count($data['emails']) > 0) {
                $data['normalized_primary_email'] = $data['emails'][0]['value'] ?? null;
            }
        }

        // Phones
        if (isset($data['contact_numbers']) && is_array($data['contact_numbers'])) {
            foreach ($data['contact_numbers'] as &$phoneItem) {
                if (!empty($phoneItem['value'])) {
                    $phone = $phoneItem['value'];
                    $isPlus = str_starts_with(trim($phone), '+');
                    $normalized = preg_replace('/[^0-9]/', '', $phone);
                    $phoneItem['value'] = $isPlus && !empty($normalized) ? '+' . $normalized : $normalized;
                }
            }
            if (count($data['contact_numbers']) > 0) {
                $data['normalized_primary_phone'] = $data['contact_numbers'][0]['value'] ?? null;
            }
        }

        // Basic Sanitization for string fields (title, description, etc)
        // We'll strip tags from standard string fields to prevent XSS
        $textFields = ['title', 'description', 'organization_name'];
        foreach ($textFields as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = strip_tags($data[$field]);
            }
        }

        return $data;
    }

    /**
     * Validates data against the EAV rules and standard rules.
     * Throws an exception or returns errors.
     */
    public function validate(array $data, ?int $leadId = null): array
    {
        $rules = [];
        $messages = [];

        // Dynamic EAV Rules (similar to LeadForm)
        $attributes = $this->attributeRepository->where('entity_type', 'leads')->get();
        foreach ($attributes as $attribute) {
            if ($attribute->type === 'boolean') {
                continue;
            }
            
            $rule = $attribute->is_required ? ['required'] : ['nullable'];
            
            if ($attribute->type === 'email') {
                $rules[$attribute->code] = $rule;
                $rules[$attribute->code . '.*.value'] = array_merge($rule, ['email']);
            } elseif ($attribute->type === 'phone') {
                $rules[$attribute->code] = $rule;
                $rules[$attribute->code . '.*.value'] = $rule;
            } else {
                if ($attribute->validation) {
                    $rule[] = $attribute->validation;
                }
                $rules[$attribute->code] = $rule;
            }
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
        if (empty($lead->person_name)) {
            $issues[] = 'Missing person name';
        }

        $hasValidEmail = false;
        if (!empty($lead->emails)) {
            foreach ($lead->emails as $email) {
                if (!empty($email['value']) && filter_var($email['value'], FILTER_VALIDATE_EMAIL)) {
                    $hasValidEmail = true;
                    break;
                }
            }
        }
        if (!$hasValidEmail) {
            $issues[] = 'Missing or invalid email';
        }

        $hasPhone = false;
        if (!empty($lead->contact_numbers)) {
            foreach ($lead->contact_numbers as $phone) {
                if (!empty($phone['value'])) {
                    $hasPhone = true;
                    break;
                }
            }
        }
        if (!$hasPhone) {
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
                if (!in_array("Missing required field: {$attribute->name}", $issues)) {
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
                'issues' => $issues
            ]);
        }
    }
}
