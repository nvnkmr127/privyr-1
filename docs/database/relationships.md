# Database Relationships

This document describes all foreign key relationships in the system.

## `users` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `role_id` | `roles` | `id` |

## `leads` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `follow_up_owner_id` | `users` | `id` |
| `lead_pipeline_id` | `lead_pipelines` | `id` |
| `lead_pipeline_stage_id` | `lead_pipeline_stages` | `id` |
| `lead_source_id` | `lead_sources` | `id` |
| `lead_type_id` | `lead_types` | `id` |
| `user_id` | `users` | `id` |

## `activities` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `lead_id` | `leads` | `id` |
| `user_id` | `users` | `id` |

## `activity_files` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `activity_id` | `activities` | `id` |

## `activity_participants` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `activity_id` | `activities` | `id` |
| `user_id` | `users` | `id` |

## `attribute_options` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `attribute_id` | `attributes` | `id` |

## `attribute_values` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `attribute_id` | `attributes` | `id` |

## `attributes` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `lead_pipeline_id` | `lead_pipelines` | `id` |

## `country_states` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `country_id` | `countries` | `id` |

## `device_tokens` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `user_id` | `users` | `id` |

## `email_attachments` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `email_id` | `emails` | `id` |

## `email_tags` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `email_id` | `emails` | `id` |
| `tag_id` | `tags` | `id` |

## `emails` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `lead_id` | `leads` | `id` |
| `parent_id` | `emails` | `id` |

## `import_batches` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `import_id` | `imports` | `id` |

## `lead_assignment_rule_conditions` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `rule_id` | `lead_assignment_rules` | `id` |

## `lead_assignment_rule_users` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `rule_id` | `lead_assignment_rules` | `id` |
| `user_id` | `users` | `id` |

## `lead_assignments` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `assigned_by` | `users` | `id` |
| `assigned_to` | `users` | `id` |
| `lead_id` | `leads` | `id` |
| `previous_owner` | `users` | `id` |

## `lead_capture_logs` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `connector_id` | `lead_source_connectors` | `id` |
| `lead_id` | `leads` | `id` |

## `lead_nurture_enrollments` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `current_step_id` | `lead_nurture_steps` | `id` |
| `lead_id` | `leads` | `id` |
| `sequence_id` | `lead_nurture_sequences` | `id` |

## `lead_nurture_steps` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `sequence_id` | `lead_nurture_sequences` | `id` |

## `lead_pipeline_stage_actions` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `lead_pipeline_stage_id` | `lead_pipeline_stages` | `id` |

## `lead_pipeline_stages` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `lead_pipeline_id` | `lead_pipelines` | `id` |

## `lead_pipeline_user` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `lead_pipeline_id` | `lead_pipelines` | `id` |
| `user_id` | `users` | `id` |

## `lead_qualifications` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `lead_id` | `leads` | `id` |
| `user_id` | `users` | `id` |

## `lead_score_logs` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `lead_id` | `leads` | `id` |
| `rule_id` | `lead_score_rules` | `id` |

## `lead_source_connectors` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `default_lead_pipeline_id` | `lead_pipelines` | `id` |
| `default_lead_pipeline_stage_id` | `lead_pipeline_stages` | `id` |
| `default_user_id` | `users` | `id` |
| `lead_source_id` | `lead_sources` | `id` |

## `lead_tags` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `lead_id` | `leads` | `id` |
| `tag_id` | `tags` | `id` |

## `marketing_campaigns` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `marketing_event_id` | `marketing_events` | `id` |
| `marketing_template_id` | `email_templates` | `id` |

## `tags` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `user_id` | `users` | `id` |

## `user_groups` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `group_id` | `groups` | `id` |
| `user_id` | `users` | `id` |

## `web_form_attributes` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `attribute_id` | `attributes` | `id` |
| `web_form_id` | `web_forms` | `id` |

## `web_forms` Relationships

| Local Column | Foreign Table | Foreign Column |
|--------------|---------------|----------------|
| `lead_pipeline_id` | `lead_pipelines` | `id` |

