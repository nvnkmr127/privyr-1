<?php

namespace App\Services;

class MessageTemplateService
{
    /**
     * Parse and replace placeholder variables in message text.
     *
     * @param  object  $lead
     */
    public function parse(string $template, $lead): string
    {
        $personName = $lead->person?->name ?? 'there';
        $phone = collect($lead->person?->contact_numbers ?? [])->pluck('value')->filter()->first() ?? '';
        $email = collect($lead->person?->emails ?? [])->pluck('value')->filter()->first() ?? '';
        $agentName = $lead->user?->name ?? 'Sales Team';
        $source = $lead->source?->name ?? 'Direct';

        $replacements = [
            '{name}' => $personName,
            '{title}' => $lead->title ?? '',
            '{phone}' => $phone,
            '{email}' => $email,
            '{value}' => $lead->lead_value ?? 0,
            '{source}' => $source,
            '{agent_name}' => $agentName,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
