# Database Entities

This document describes all tables in the system.

## `activity_logs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `approval_chain_steps`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `approval_chains`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `approval_checklists`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `cache`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `cache_locks`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `clients`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `coupon_codes`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `decline_reasons`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `estimate_analytics`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `estimate_approvals`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `estimate_comments`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `estimate_items`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `estimate_sections`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `estimates`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `failed_jobs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `bigint unsigned` | No | NULL |
| `uuid` | `varchar(255)` | No | NULL |
| `connection` | `text` | No | NULL |
| `queue` | `text` | No | NULL |
| `payload` | `longtext` | No | NULL |
| `exception` | `longtext` | No | NULL |
| `failed_at` | `timestamp` | No | CURRENT_TIMESTAMP |

## `item_packages`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `job_batches`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `varchar(255)` | No | NULL |
| `name` | `varchar(255)` | No | NULL |
| `total_jobs` | `int` | No | NULL |
| `pending_jobs` | `int` | No | NULL |
| `failed_jobs` | `int` | No | NULL |
| `failed_job_ids` | `text` | No | NULL |
| `options` | `mediumtext` | Yes | NULL |
| `cancelled_at` | `int` | Yes | NULL |
| `created_at` | `int` | No | NULL |
| `finished_at` | `int` | Yes | NULL |

## `jobs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `bigint unsigned` | No | NULL |
| `queue` | `varchar(255)` | No | NULL |
| `payload` | `longtext` | No | NULL |
| `attempts` | `tinyint unsigned` | No | NULL |
| `reserved_at` | `int unsigned` | Yes | NULL |
| `available_at` | `int unsigned` | No | NULL |
| `created_at` | `int unsigned` | No | NULL |

## `migrations`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `migration` | `varchar(255)` | No | NULL |
| `batch` | `int` | No | NULL |

## `notifications`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `password_reset_tokens`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `pdf_template_versions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `pdf_templates`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `product_categories`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `product_images`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `product_option_values`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `product_options`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `products`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `reminders`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `room_templates`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `sessions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `settings`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tasks`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `users`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `name` | `varchar(255)` | No | NULL |
| `email` | `varchar(255)` | No | NULL |
| `password` | `varchar(255)` | Yes | NULL |
| `status` | `tinyint(1)` | No | 0 |
| `view_permission` | `varchar(255)` | Yes | global |
| `role_id` | `int unsigned` | No | NULL |
| `remember_token` | `varchar(100)` | Yes | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |
| `image` | `varchar(255)` | Yes | NULL |

## `access_tokens`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `ad_accounts`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `ad_creatives`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `ad_insights`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `ad_sets`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `ads`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `ai_insights`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `alerts`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `amazon_sp_daily_metrics`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `audience_insights`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `audit_logs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `automation_actions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `automation_logs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `automation_rules`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `brand_kits`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `briefing_action_items`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `broadcasts`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `budget_alerts`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `campaign_refs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `campaigns`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `client_channel_connections`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `client_competitors`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `client_deliverables`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `client_health_scores`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `client_onboarding_checklists`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `client_playbook_runs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `client_service_packages`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `contacts`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `conversations`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `conversion_events`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `creative_assets`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `creative_feedback`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `creative_requests`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `creatives`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `daily_briefings`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `daily_metrics`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `dashboard_layouts`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `deliverable_templates`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `domain_expiry_checks`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `employees`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `facebook_connections`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `facebook_leads`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `facebook_users`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `feedback`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `form_submissions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `forms`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `funnel_metrics`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `google_analytics_daily_metrics`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `google_business_profile_daily_metrics`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `google_business_profile_monthly_keywords`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `google_merchant_center_daily_metrics`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `google_search_console_daily_metrics`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `inbound_webhooks`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `instagram_daily_metrics`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `integration_credentials`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `integration_sync_runs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `invoice_items`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `invoices`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `lead_sync_logs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `leads`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `title` | `varchar(255)` | No | NULL |
| `description` | `text` | Yes | NULL |
| `lead_value` | `decimal(12,4)` | Yes | NULL |
| `status` | `tinyint(1)` | Yes | NULL |
| `lost_reason` | `text` | Yes | NULL |
| `closed_at` | `datetime` | Yes | NULL |
| `user_id` | `int unsigned` | Yes | NULL |
| `person_name` | `varchar(255)` | Yes | NULL |
| `emails` | `json` | Yes | NULL |
| `contact_numbers` | `json` | Yes | NULL |
| `organization_name` | `varchar(255)` | Yes | NULL |
| `lead_source_id` | `int unsigned` | Yes | NULL |
| `lead_type_id` | `int unsigned` | Yes | NULL |
| `lead_pipeline_id` | `int unsigned` | Yes | NULL |
| `lead_pipeline_stage_id` | `int unsigned` | Yes | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |
| `expected_close_date` | `date` | Yes | NULL |
| `priority` | `enum('low','medium','high','urgent')` | No | medium |
| `is_unread` | `tinyint(1)` | No | 1 |
| `last_contacted_at` | `timestamp` | Yes | NULL |
| `next_follow_up_at` | `timestamp` | Yes | NULL |
| `is_archived` | `tinyint(1)` | No | 0 |
| `lead_score` | `int` | No | 0 |
| `utm_source` | `varchar(255)` | Yes | NULL |
| `utm_medium` | `varchar(255)` | Yes | NULL |
| `utm_campaign` | `varchar(255)` | Yes | NULL |
| `location` | `varchar(255)` | Yes | NULL |
| `qualification_status` | `varchar(255)` | Yes | NULL |
| `next_action` | `varchar(255)` | Yes | NULL |
| `follow_up_owner_id` | `int unsigned` | Yes | NULL |

## `leave_requests`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `linkedin_organization_daily_metrics`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `messages`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `meta_ad_library_ads`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `meta_ad_library_daily_summaries`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `meta_page_daily_metrics`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `opportunities`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `order_items`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `orders`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `organizations`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `page_speed_daily_metrics`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `payments`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `performance_anomalies`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `performance_snapshots`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `pipeline_stages`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `pipelines`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `playbook_run_tasks`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `playbook_templates`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `productivity_daily_summaries`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `project_assignments`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `projects`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `proposals`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `recommendations`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `reports`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `search_console_dimension_rows`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `seo_opportunities`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `seo_site_audit_issues`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `seo_site_audits`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `service_packages`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `shopify_daily_metrics`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `social_channels`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `social_listening_events`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `social_listening_sources`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `social_posts`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `task_attachments`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `task_comments`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `time_entries`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `twitter_daily_metrics`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `webhook_deliveries`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `webhook_mappings`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `webhooks`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `bigint unsigned` | No | NULL |
| `name` | `varchar(255)` | No | NULL |
| `entity_type` | `varchar(255)` | No | NULL |
| `description` | `varchar(255)` | Yes | NULL |
| `method` | `varchar(255)` | No | NULL |
| `end_point` | `varchar(255)` | No | NULL |
| `query_params` | `json` | Yes | NULL |
| `headers` | `json` | Yes | NULL |
| `payload_type` | `varchar(255)` | No | NULL |
| `raw_payload_type` | `varchar(255)` | No | NULL |
| `payload` | `json` | Yes | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `woocommerce_daily_metrics`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `workflow_actions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `workflow_events`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `workflow_rules`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `workload_entries`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `banners`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `admission_bed_histories`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `admissions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `appointments`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `beds`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `bill_discounts`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `bill_items`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `bill_payments`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `bills`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `clinical_templates`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `consultations`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `cron_job_runs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `departments`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `diagnoses`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `discharge_medications`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `discharge_summaries`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `doctors`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `hospital_owners`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `inventory_categories`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `inventory_items`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `inventory_suppliers`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `inventory_transactions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `ip_services`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `ipd_medication_administrations`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `ipd_medication_charts`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `ipd_notes`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `ipd_vitals`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `lab_orders`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `lab_parameters`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `lab_result_values`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `lab_results`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `lab_tests`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `medicine_stock_transactions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `medicines`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `model_has_permissions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `model_has_roles`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `number_sequences`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `patient_consents`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `patient_vaccinations`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `patient_vitals`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `patients`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `permissions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `personal_access_tokens`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `bigint unsigned` | No | NULL |
| `tokenable_type` | `varchar(255)` | No | NULL |
| `tokenable_id` | `bigint unsigned` | No | NULL |
| `name` | `varchar(255)` | No | NULL |
| `token` | `varchar(64)` | No | NULL |
| `abilities` | `text` | Yes | NULL |
| `last_used_at` | `timestamp` | Yes | NULL |
| `expires_at` | `timestamp` | Yes | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `prescription_items`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `prescriptions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `procedure_charges`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `procedures`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `refunds`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `report_schedules`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `role_has_permissions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `roles`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `name` | `varchar(255)` | No | NULL |
| `description` | `varchar(255)` | Yes | NULL |
| `permission_type` | `varchar(255)` | No | NULL |
| `permissions` | `json` | Yes | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `saved_reports`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `services`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `sync_audit_log`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `sync_conflicts`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `sync_devices`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `sync_outbox`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `telescope_entries`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `telescope_entries_tags`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `telescope_monitoring`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `vaccines`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `wards`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `webhook_endpoints`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `webhook_events`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `webhook_logs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `webhook_outbox`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `webhook_sources`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `academic_years`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `activity_log`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `admission_follow_ups`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `alumni`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `asset_categories`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `assets`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `attendance_caches`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `attendances`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `audit_items`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `audits`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `batches`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `biometric_devices`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `biometric_logs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `certificate_templates`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `classrooms`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `component_payment_items`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `component_payments`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `course_subject`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `course_terms`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `courses`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `daily_diaries`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `dashboard_templates`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `dashboards`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `enquiries`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `etimeoffice_sync_logs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `events`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `exam_schedules`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `exams`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `expense_categories`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `expenses`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `fee_categories`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `fee_collections`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `fee_installments`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `fee_structure_fee_category`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `fee_structures`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `follow_ups`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `holidays`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `homework`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `id_card_templates`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `import_log_details`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `import_logs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `inbound_webhook_logs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `invoice_edit_logs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `leave_applications`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `leave_balances`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `leave_types`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `marks`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `notification_logs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `notification_preferences`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `parent_contacts`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `payment_defaulters`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `payment_edit_logs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `payment_reminder_logs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `payment_reminder_templates`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `payment_reminders`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `payslips`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `practical_group_student`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `practical_groups`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `pulse_aggregates`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `pulse_entries`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `pulse_values`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `salary_components`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `student_concessions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `student_fees`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `student_portal_activity_logs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `student_profile_requests`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `students`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `subject_user`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `subjects`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `substitute_assignments`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `system_notifications`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `time_slots`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `timetable_generation_logs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `timetable_requirement_validations`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `timetables`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `user_dashboard_preferences`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `user_salary_structures`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `visitors`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `webhook_calls`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `activities`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `title` | `varchar(255)` | Yes | NULL |
| `type` | `varchar(255)` | No | NULL |
| `comment` | `text` | Yes | NULL |
| `additional` | `json` | Yes | NULL |
| `schedule_from` | `datetime` | Yes | NULL |
| `schedule_to` | `datetime` | Yes | NULL |
| `is_done` | `tinyint(1)` | No | 0 |
| `user_id` | `int unsigned` | Yes | NULL |
| `lead_id` | `int unsigned` | Yes | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |
| `location` | `varchar(255)` | Yes | NULL |

## `activity_files`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `name` | `varchar(255)` | No | NULL |
| `path` | `varchar(255)` | No | NULL |
| `activity_id` | `int unsigned` | No | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `activity_participants`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `activity_id` | `int unsigned` | No | NULL |
| `user_id` | `int unsigned` | Yes | NULL |
| `person_id` | `int unsigned` | Yes | NULL |

## `attribute_options`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `name` | `varchar(255)` | Yes | NULL |
| `sort_order` | `int` | Yes | NULL |
| `attribute_id` | `int unsigned` | No | NULL |

## `attribute_values`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `entity_type` | `varchar(255)` | No | leads |
| `text_value` | `text` | Yes | NULL |
| `boolean_value` | `tinyint(1)` | Yes | NULL |
| `integer_value` | `int` | Yes | NULL |
| `float_value` | `double` | Yes | NULL |
| `datetime_value` | `datetime` | Yes | NULL |
| `date_value` | `date` | Yes | NULL |
| `json_value` | `json` | Yes | NULL |
| `entity_id` | `int unsigned` | No | NULL |
| `attribute_id` | `int unsigned` | No | NULL |
| `unique_id` | `varchar(255)` | Yes | NULL |

## `attributes`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `code` | `varchar(255)` | No | NULL |
| `name` | `varchar(255)` | No | NULL |
| `type` | `varchar(255)` | No | NULL |
| `lookup_type` | `varchar(255)` | Yes | NULL |
| `entity_type` | `varchar(255)` | No | NULL |
| `lead_pipeline_id` | `int unsigned` | Yes | NULL |
| `sort_order` | `int` | Yes | NULL |
| `validation` | `varchar(255)` | Yes | NULL |
| `is_required` | `tinyint(1)` | No | 0 |
| `is_unique` | `tinyint(1)` | No | 0 |
| `quick_add` | `tinyint(1)` | No | 0 |
| `is_user_defined` | `tinyint(1)` | No | 1 |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `core_config`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `code` | `varchar(255)` | No | NULL |
| `value` | `text` | Yes | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `countries`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `code` | `varchar(255)` | No | NULL |
| `name` | `varchar(255)` | No | NULL |

## `country_states`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `country_code` | `varchar(255)` | No | NULL |
| `code` | `varchar(255)` | No | NULL |
| `name` | `varchar(255)` | No | NULL |
| `country_id` | `int unsigned` | No | NULL |

## `datagrid_saved_filters`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `bigint unsigned` | No | NULL |
| `user_id` | `int unsigned` | No | NULL |
| `name` | `varchar(255)` | No | NULL |
| `src` | `varchar(255)` | No | NULL |
| `applied` | `json` | No | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `device_tokens`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `bigint unsigned` | No | NULL |
| `user_id` | `int unsigned` | No | NULL |
| `token` | `varchar(255)` | No | NULL |
| `platform` | `varchar(255)` | No | android |
| `device_name` | `varchar(255)` | Yes | NULL |
| `last_used_at` | `timestamp` | Yes | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `drip_sequence_steps`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `bigint unsigned` | No | NULL |
| `name` | `varchar(255)` | No | NULL |
| `day_offset` | `int unsigned` | No | 0 |
| `content` | `text` | No | NULL |
| `is_active` | `tinyint(1)` | No | 1 |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `email_attachments`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `name` | `varchar(255)` | Yes | NULL |
| `path` | `varchar(255)` | No | NULL |
| `size` | `int` | Yes | NULL |
| `content_type` | `varchar(255)` | Yes | NULL |
| `content_id` | `varchar(255)` | Yes | NULL |
| `email_id` | `int unsigned` | No | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `email_tags`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `tag_id` | `int unsigned` | No | NULL |
| `email_id` | `int unsigned` | No | NULL |

## `email_templates`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `name` | `varchar(255)` | No | NULL |
| `subject` | `varchar(255)` | No | NULL |
| `content` | `text` | No | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `emails`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `subject` | `varchar(255)` | Yes | NULL |
| `source` | `varchar(255)` | No | NULL |
| `user_type` | `varchar(255)` | No | NULL |
| `name` | `varchar(255)` | Yes | NULL |
| `reply` | `text` | Yes | NULL |
| `is_read` | `tinyint(1)` | No | 0 |
| `folders` | `json` | Yes | NULL |
| `from` | `json` | Yes | NULL |
| `sender` | `json` | Yes | NULL |
| `reply_to` | `json` | Yes | NULL |
| `cc` | `json` | Yes | NULL |
| `bcc` | `json` | Yes | NULL |
| `unique_id` | `varchar(255)` | Yes | NULL |
| `message_id` | `varchar(255)` | No | NULL |
| `reference_ids` | `json` | Yes | NULL |
| `lead_id` | `int unsigned` | Yes | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |
| `parent_id` | `int unsigned` | Yes | NULL |

## `groups`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `name` | `varchar(255)` | No | NULL |
| `description` | `varchar(255)` | Yes | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `import_batches`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `state` | `varchar(255)` | No | pending |
| `data` | `json` | No | NULL |
| `summary` | `json` | Yes | NULL |
| `import_id` | `int unsigned` | No | NULL |

## `imports`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `state` | `varchar(255)` | No | pending |
| `process_in_queue` | `tinyint(1)` | No | 1 |
| `type` | `varchar(255)` | No | NULL |
| `action` | `varchar(255)` | No | NULL |
| `validation_strategy` | `varchar(255)` | No | NULL |
| `allowed_errors` | `int` | No | 0 |
| `processed_rows_count` | `int` | No | 0 |
| `invalid_rows_count` | `int` | No | 0 |
| `errors_count` | `int` | No | 0 |
| `errors` | `json` | Yes | NULL |
| `field_separator` | `varchar(255)` | No | NULL |
| `file_path` | `varchar(255)` | No | NULL |
| `error_file_path` | `varchar(255)` | Yes | NULL |
| `summary` | `json` | Yes | NULL |
| `started_at` | `datetime` | Yes | NULL |
| `completed_at` | `datetime` | Yes | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `lead_assignment_rule_conditions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `bigint unsigned` | No | NULL |
| `rule_id` | `bigint unsigned` | No | NULL |
| `attribute` | `varchar(255)` | No | NULL |
| `operator` | `varchar(255)` | No | NULL |
| `value` | `text` | Yes | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `lead_assignment_rule_users`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `rule_id` | `bigint unsigned` | No | NULL |
| `user_id` | `int unsigned` | No | NULL |
| `weight` | `int` | No | 1 |
| `last_assigned_at` | `timestamp` | Yes | NULL |

## `lead_assignment_rules`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `bigint unsigned` | No | NULL |
| `name` | `varchar(255)` | No | NULL |
| `type` | `varchar(255)` | No | NULL |
| `status` | `tinyint(1)` | No | 1 |
| `sort_order` | `int` | No | 0 |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `lead_assignments`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `bigint unsigned` | No | NULL |
| `lead_id` | `int unsigned` | No | NULL |
| `assigned_to` | `int unsigned` | No | NULL |
| `assigned_by` | `int unsigned` | Yes | NULL |
| `previous_owner` | `int unsigned` | Yes | NULL |
| `reason` | `varchar(255)` | Yes | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `lead_capture_logs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `bigint unsigned` | No | NULL |
| `connector_id` | `bigint unsigned` | Yes | NULL |
| `raw_payload` | `json` | Yes | NULL |
| `status` | `varchar(255)` | No | success |
| `lead_id` | `int unsigned` | Yes | NULL |
| `error_message` | `text` | Yes | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `lead_nurture_enrollments`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `lead_id` | `int unsigned` | No | NULL |
| `sequence_id` | `int unsigned` | No | NULL |
| `current_step_id` | `int unsigned` | Yes | NULL |
| `status` | `varchar(255)` | No | active |
| `resume_at` | `timestamp` | Yes | NULL |
| `context` | `json` | Yes | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `lead_nurture_sequences`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `name` | `varchar(255)` | No | NULL |
| `description` | `text` | Yes | NULL |
| `is_active` | `tinyint(1)` | No | 1 |
| `stop_condition` | `json` | Yes | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `lead_nurture_steps`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `sequence_id` | `int unsigned` | No | NULL |
| `type` | `varchar(255)` | No | NULL |
| `config` | `json` | Yes | NULL |
| `next_step_id` | `int unsigned` | Yes | NULL |
| `alt_next_step_id` | `int unsigned` | Yes | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `lead_pipeline_stage_actions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `bigint unsigned` | No | NULL |
| `lead_pipeline_stage_id` | `int unsigned` | No | NULL |
| `type` | `varchar(255)` | No | NULL |
| `payload` | `json` | Yes | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `lead_pipeline_stages`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `code` | `varchar(255)` | Yes | NULL |
| `name` | `varchar(255)` | Yes | NULL |
| `probability` | `int` | No | 0 |
| `sort_order` | `int` | No | 0 |
| `color` | `varchar(255)` | Yes | NULL |
| `lead_pipeline_id` | `int unsigned` | No | NULL |

## `lead_pipeline_user`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `bigint unsigned` | No | NULL |
| `lead_pipeline_id` | `int unsigned` | No | NULL |
| `user_id` | `int unsigned` | No | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `lead_pipelines`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `name` | `varchar(255)` | No | NULL |
| `is_default` | `tinyint(1)` | No | 0 |
| `rotten_days` | `int` | No | 30 |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `lead_qualifications`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `lead_id` | `int unsigned` | No | NULL |
| `user_id` | `int unsigned` | Yes | NULL |
| `status` | `varchar(255)` | No | NULL |
| `reason` | `text` | Yes | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `lead_routing_rules`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `bigint unsigned` | No | NULL |
| `name` | `varchar(255)` | No | NULL |
| `condition_type` | `varchar(255)` | No | NULL |
| `condition_value` | `varchar(255)` | No | NULL |
| `user_id` | `bigint unsigned` | Yes | NULL |
| `sort_order` | `int` | No | 1 |
| `status` | `tinyint(1)` | No | 1 |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `lead_score_logs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `bigint unsigned` | No | NULL |
| `lead_id` | `int unsigned` | No | NULL |
| `rule_id` | `bigint unsigned` | No | NULL |
| `points` | `int` | No | NULL |
| `reason` | `varchar(255)` | No | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `lead_score_rules`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `bigint unsigned` | No | NULL |
| `name` | `varchar(255)` | No | NULL |
| `type` | `varchar(255)` | No | attribute |
| `conditions` | `json` | No | NULL |
| `points` | `int` | No | NULL |
| `is_active` | `tinyint(1)` | No | 1 |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `lead_source_connectors`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `bigint unsigned` | No | NULL |
| `name` | `varchar(255)` | No | NULL |
| `source_type` | `varchar(255)` | No | NULL |
| `webhook_token` | `varchar(255)` | No | NULL |
| `api_key` | `varchar(255)` | Yes | NULL |
| `meta_page_id` | `varchar(255)` | Yes | NULL |
| `meta_page_name` | `varchar(255)` | Yes | NULL |
| `meta_page_access_token` | `text` | Yes | NULL |
| `is_active` | `tinyint(1)` | No | 1 |
| `duplicate_action` | `varchar(255)` | No | update |
| `lead_source_id` | `int unsigned` | Yes | NULL |
| `field_mappings` | `json` | Yes | NULL |
| `embed_config` | `json` | Yes | NULL |
| `default_lead_pipeline_id` | `int unsigned` | Yes | NULL |
| `default_lead_pipeline_stage_id` | `int unsigned` | Yes | NULL |
| `default_user_id` | `int unsigned` | Yes | NULL |
| `captured_count` | `int unsigned` | No | 0 |
| `last_received_at` | `timestamp` | Yes | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `lead_sources`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `name` | `varchar(255)` | No | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `lead_stages`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `code` | `varchar(255)` | No | NULL |
| `name` | `varchar(255)` | No | NULL |
| `is_user_defined` | `tinyint(1)` | No | 1 |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `lead_tags`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `tag_id` | `int unsigned` | No | NULL |
| `lead_id` | `int unsigned` | No | NULL |

## `lead_types`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `name` | `varchar(255)` | No | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `marketing_campaigns`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `name` | `varchar(255)` | No | NULL |
| `subject` | `varchar(255)` | No | NULL |
| `status` | `tinyint(1)` | No | 0 |
| `type` | `varchar(255)` | No | NULL |
| `mail_to` | `varchar(255)` | No | NULL |
| `spooling` | `varchar(255)` | Yes | NULL |
| `marketing_template_id` | `int unsigned` | Yes | NULL |
| `marketing_event_id` | `int unsigned` | Yes | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `marketing_events`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `name` | `varchar(255)` | No | NULL |
| `description` | `varchar(255)` | No | NULL |
| `date` | `date` | No | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `message_templates`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `bigint unsigned` | No | NULL |
| `name` | `varchar(255)` | No | NULL |
| `category` | `varchar(255)` | Yes | NULL |
| `content` | `text` | No | NULL |
| `is_active` | `tinyint(1)` | No | 1 |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `tags`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `name` | `varchar(255)` | No | NULL |
| `color` | `varchar(255)` | Yes | NULL |
| `user_id` | `int unsigned` | No | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `user_groups`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `group_id` | `int unsigned` | No | NULL |
| `user_id` | `int unsigned` | No | NULL |

## `user_password_resets`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `email` | `varchar(255)` | No | NULL |
| `token` | `varchar(255)` | No | NULL |
| `created_at` | `timestamp` | Yes | NULL |

## `web_form_attributes`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `name` | `varchar(255)` | Yes | NULL |
| `placeholder` | `varchar(255)` | Yes | NULL |
| `is_required` | `tinyint(1)` | No | 0 |
| `is_hidden` | `tinyint(1)` | No | 0 |
| `sort_order` | `int` | Yes | NULL |
| `attribute_id` | `int unsigned` | No | NULL |
| `web_form_id` | `int unsigned` | No | NULL |

## `web_forms`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `form_id` | `varchar(255)` | No | NULL |
| `title` | `varchar(255)` | No | NULL |
| `description` | `text` | Yes | NULL |
| `submit_button_label` | `text` | No | NULL |
| `submit_success_action` | `varchar(255)` | No | NULL |
| `submit_success_content` | `varchar(255)` | No | NULL |
| `create_lead` | `tinyint(1)` | No | 0 |
| `lead_pipeline_id` | `int unsigned` | Yes | NULL |
| `background_color` | `varchar(255)` | Yes | NULL |
| `form_background_color` | `varchar(255)` | Yes | NULL |
| `form_title_color` | `varchar(255)` | Yes | NULL |
| `form_submit_button_color` | `varchar(255)` | Yes | NULL |
| `attribute_label_color` | `varchar(255)` | Yes | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `workflows`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|
| `id` | `int unsigned` | No | NULL |
| `name` | `varchar(255)` | No | NULL |
| `description` | `varchar(255)` | Yes | NULL |
| `entity_type` | `varchar(255)` | No | NULL |
| `event` | `varchar(255)` | No | NULL |
| `condition_type` | `varchar(255)` | No | and |
| `conditions` | `json` | Yes | NULL |
| `actions` | `json` | Yes | NULL |
| `created_at` | `timestamp` | Yes | NULL |
| `updated_at` | `timestamp` | Yes | NULL |

## `cards`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `salaries`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `webhook_subscriptions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblactivity_log`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblannouncements`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblclients`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblconsent_purposes`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblconsents`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblcontact_permissions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblcontacts`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblcontract_comments`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblcontract_renewals`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblcontracts`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblcontracts_types`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblcountries`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblcreditnote_refunds`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblcreditnotes`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblcredits`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblcurrencies`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblcustomer_admins`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblcustomer_groups`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblcustomers_groups`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblcustomfields`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblcustomfieldsvalues`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbldepartments`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbldevice_tokens`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbldismissed_announcements`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblemailtemplates`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblestimate_request_forms`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblestimate_request_status`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblestimate_requests`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblestimates`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblevents`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblexpenses`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblexpenses_categories`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblfiles`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblfilter_defaults`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblfilters`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblform_question_box`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblform_question_box_description`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblform_questions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblform_results`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblgdpr_requests`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblinvoicepaymentrecords`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblinvoices`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblitem_tax`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblitemable`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblitems`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblitems_groups`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblknowedge_base_article_feedback`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblknowledge_base`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblknowledge_base_groups`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbllead_activity_log`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbllead_integration_emails`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblleads`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblleads_email_integration`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblleads_sources`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblleads_status`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblmail_queue`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblmcp_audit_log`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblmcp_tokens`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblmigrations`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblmilestones`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblmodules`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblnewsfeed_comment_likes`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblnewsfeed_post_comments`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblnewsfeed_post_likes`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblnewsfeed_posts`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblnotes`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblnotifications`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbloptions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblpayment_attempts`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblpayment_modes`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblpinned_projects`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblproject_activity`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblproject_files`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblproject_members`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblproject_notes`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblproject_settings`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblprojectdiscussioncomments`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblprojectdiscussions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblprojects`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblproposal_comments`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblproposals`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblrelated_items`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblreminders`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblroles`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblsales_activity`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblscheduled_emails`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblservices`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblsessions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblshared_customer_files`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblspam_filters`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblsso_nonces`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblstaff`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblstaff_departments`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblstaff_permissions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblsubscriptions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbltaggables`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbltags`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbltask_assigned`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbltask_checklist_items`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbltask_comments`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbltask_followers`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbltasks`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbltasks_checklist_templates`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbltaskstimers`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbltaxes`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbltemplates`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblticket_attachments`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblticket_replies`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbltickets`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbltickets_pipe_log`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbltickets_predefined_replies`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbltickets_priorities`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbltickets_status`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbltodos`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbltracked_mails`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbltwocheckout_log`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbluser_api_sessions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbluser_auto_login`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tbluser_meta`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblvault`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblviews_tracking`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tblweb_to_lead`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `albums`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `disk_allocations`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `equipment`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `equipment_allocations`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `estimate_logs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `hard_disks`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `project_deliverables`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `project_events`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `project_links`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `project_team`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `quote_items`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `quotes`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `team_members`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `time_logs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `transactions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `vendors`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `faculty_attendances`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `id_sequences`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `payslip_items`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `salary_template_components`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `salary_templates`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `affiliates`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `ai_usage_logs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `alert_logs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `alert_rules`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `automation_runs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `automation_step_ledger`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `automation_steps`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `automations`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `billing_overrides`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `broadcast_events`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `call_permissions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `call_quality_metrics`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `call_settings`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `calling_consent_log`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `campaign_details`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `campaign_snapshot_contacts`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `campaign_snapshots`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `canned_messages`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `carts`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `categories`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `companies`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `consent_logs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `consent_registry`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `contact_events`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `contact_fields`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `contact_merge_logs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `contact_relationships`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `contact_tag_pivot`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `contact_tags`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `conversation_locks`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `crm_activities`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `crm_segments`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `crm_tasks`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `csat_ratings`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `customer_events`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `deal_activities`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `deal_products`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `deals`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `duplicate_detection_queue`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `ecommerce_tables`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `email_logs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `flow_sessions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `flows`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `identity_fingerprints`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `integrations`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `internal_notes`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `knowledge_base_gaps`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `knowledge_base_sources`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `lead_capture_widgets`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `message_bots`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `notes`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `onboarding_statuses`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `order_events`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `plans`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `quality_rating_history`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `referrals`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `scheduled_reports`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `segment_cache`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `segment_memberships`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `segments`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `short_link_clicks`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `short_links`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `sla_policies`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `smtp_configs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `sync_sessions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `system_events`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `team_addons`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `team_invitations`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `team_invoice_items`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `team_invoices`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `team_transactions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `team_user`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `team_wallets`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `teams`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `template_bots`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tenant_backups`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `tickets`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `trial_extension_requests`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `user_fcm_tokens`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `user_identities`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `webhook_payloads`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `webhook_workflows`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `whatsapp_calls`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `whatsapp_conversations`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `whatsapp_flow_responses`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `whatsapp_flow_versions`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `whatsapp_flows`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `whatsapp_health_alerts`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `whatsapp_health_snapshots`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `whatsapp_setup_audit`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `whatsapp_templates`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `workflow_logs`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `conversation_tag_pivot`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `conversation_tags`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

## `oauth_clients`

| Column | Type | Nullable | Default |
|--------|------|----------|---------|

