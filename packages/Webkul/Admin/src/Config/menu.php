<?php

return [
    /**
     * Dashboard.
     */
    [
        'key' => 'dashboard',
        'name' => 'admin::app.layouts.dashboard',
        'route' => 'admin.dashboard.index',
        'sort' => 1,
        'icon-class' => 'icon-dashboard',
    ],

    /**
     * Leads.
     */
    [
        'key' => 'leads',
        'name' => 'admin::app.layouts.leads',
        'route' => 'admin.leads.index',
        'sort' => 2,
        'icon-class' => 'icon-leads',
    ], [
        'key' => 'analytics',
        'name' => 'Analytics & Reports',
        'route' => 'admin.analytics.index',
        'sort' => 3,
        'icon-class' => 'icon-activity',
    ],

    /**
     * Activities.
     */
    [
        'key' => 'activities',
        'name' => 'admin::app.layouts.activities',
        'route' => 'admin.activities.index',
        'sort' => 5,
        'icon-class' => 'icon-activity',
    ],

    /**
     * Settings.
     */
    [
        'key' => 'settings',
        'name' => 'admin::app.layouts.settings',
        'route' => 'admin.settings.index',
        'sort' => 8,
        'icon-class' => 'icon-setting',
    ], [
        'key' => 'settings.user',
        'name' => 'admin::app.layouts.user',
        'route' => 'admin.settings.groups.index',
        'info' => 'admin::app.layouts.user-info',
        'sort' => 1,
        'icon-class' => 'icon-settings-group',
    ], [
        'key' => 'settings.user.groups',
        'name' => 'admin::app.layouts.groups',
        'info' => 'admin::app.layouts.groups-info',
        'route' => 'admin.settings.groups.index',
        'sort' => 1,
        'icon-class' => 'icon-settings-group',
    ], [
        'key' => 'settings.user.roles',
        'name' => 'admin::app.layouts.roles',
        'info' => 'admin::app.layouts.roles-info',
        'route' => 'admin.settings.roles.index',
        'sort' => 2,
        'icon-class' => 'icon-role',
    ], [
        'key' => 'settings.user.users',
        'name' => 'admin::app.layouts.users',
        'info' => 'admin::app.layouts.users-info',
        'route' => 'admin.settings.users.index',
        'sort' => 3,
        'icon-class' => 'icon-user',
    ], [
        'key' => 'settings.lead',
        'name' => 'admin::app.layouts.lead',
        'info' => 'admin::app.layouts.lead-info',
        'route' => 'admin.settings.pipelines.index',
        'sort' => 2,
        'icon-class' => '',
    ], [
        'key' => 'settings.lead.pipelines',
        'name' => 'admin::app.layouts.pipelines',
        'info' => 'admin::app.layouts.pipelines-info',
        'route' => 'admin.settings.pipelines.index',
        'sort' => 1,
        'icon-class' => 'icon-settings-pipeline',
    ], [
        'key' => 'settings.lead.sources',
        'name' => 'admin::app.layouts.sources',
        'info' => 'admin::app.layouts.sources-info',
        'route' => 'admin.settings.sources.index',
        'sort' => 2,
        'icon-class' => 'icon-settings-sources',
    ], [
        'key' => 'settings.lead.types',
        'name' => 'admin::app.layouts.types',
        'info' => 'admin::app.layouts.types-info',
        'route' => 'admin.settings.types.index',
        'sort' => 3,
        'icon-class' => 'icon-settings-type',
    ], [
        'key' => 'settings.lead.web_forms',
        'name' => 'admin::app.layouts.web-forms',
        'info' => 'admin::app.layouts.web-forms-info',
        'route' => 'admin.settings.web_forms.index',
        'sort' => 4,
        'icon-class' => 'icon-form',
    ], [
        'key' => 'settings.automation',
        'name' => 'admin::app.layouts.automation',
        'info' => 'admin::app.layouts.automation-info',
        'route' => 'admin.settings.attributes.index',
        'sort' => 3,
        'icon-class' => '',

    ], [
        'key' => 'settings.automation.attributes',
        'name' => 'admin::app.layouts.attributes',
        'info' => 'admin::app.layouts.attributes-info',
        'route' => 'admin.settings.attributes.index',
        'sort' => 1,
        'icon-class' => 'icon-attribute',
    ], [
        'key' => 'settings.automation.events',
        'name' => 'admin::app.layouts.events',
        'info' => 'admin::app.layouts.events-info',
        'route' => 'admin.settings.marketing.events.index',
        'sort' => 2,
        'icon-class' => 'icon-calendar',
    ], [
        'key' => 'settings.automation.campaigns',
        'name' => 'admin::app.layouts.campaigns',
        'info' => 'admin::app.layouts.campaigns-info',
        'route' => 'admin.settings.marketing.campaigns.index',
        'sort' => 2,
        'icon-class' => 'icon-note',
    ], [
        'key' => 'settings.automation.lead_capture',
        'name' => 'Lead Capture Integrations',
        'info' => 'Manage Facebook, Google Ads, IndiaMART, WhatsApp & Webhook endpoints',
        'route' => 'admin.lead_capture.integrations',
        'sort' => 1,
        'icon-class' => 'icon-settings-webhooks',
    ], [
        'key' => 'settings.automation.drip_sequences',
        'name' => 'Visual Drip Builder',
        'info' => 'Configure automated multi-step WhatsApp & brochure follow-up timelines',
        'route' => 'admin.settings.drip_sequences',
        'sort' => 2,
        'icon-class' => 'icon-settings-flow',
    ], [
        'key' => 'settings.automation.lead_assignment_rules',
        'name' => 'Lead Assignment Rules',
        'info' => 'Configure lead assignment rules by value, source, and team capacity',
        'route' => 'admin.settings.lead_assignment_rules.index',
        'sort' => 3,
        'icon-class' => 'icon-settings-user',
    ], [
        'key' => 'settings.automation.webhooks',
        'name' => 'admin::app.layouts.webhooks',
        'info' => 'admin::app.layouts.webhooks-info',
        'route' => 'admin.settings.webhooks.index',
        'sort' => 2,
        'icon-class' => 'icon-settings-webhooks',
    ], [
        'key' => 'settings.automation.workflows',
        'name' => 'admin::app.layouts.workflows',
        'info' => 'admin::app.layouts.workflows-info',
        'route' => 'admin.settings.workflows.index',
        'sort' => 3,
        'icon-class' => 'icon-settings-flow',
    ], [
        'key' => 'settings.automation.email_templates',
        'name' => 'admin::app.layouts.email-templates',
        'info' => 'admin::app.layouts.email-templates-info',
        'route' => 'admin.settings.email_templates.index',
        'sort' => 4,
        'icon-class' => 'icon-mail',

    ], [
        'key' => 'settings.automation.data_transfer',
        'name' => 'admin::app.layouts.data_transfer',
        'info' => 'admin::app.layouts.data_transfer_info',
        'route' => 'admin.settings.data_transfer.imports.index',
        'sort' => 6,
        'icon-class' => 'icon-download',
    ], [
        'key' => 'settings.other_settings',
        'name' => 'admin::app.layouts.other-settings',
        'info' => 'admin::app.layouts.other-settings-info',
        'route' => 'admin.settings.tags.index',
        'sort' => 4,
        'icon-class' => 'icon-settings',
    ], [
        'key' => 'settings.other_settings.tags',
        'name' => 'admin::app.layouts.tags',
        'info' => 'admin::app.layouts.tags-info',
        'route' => 'admin.settings.tags.index',
        'sort' => 1,
        'icon-class' => 'icon-settings-tag',
    ],

    /**
     * Configuration.
     */
    [
        'key' => 'configuration',
        'name' => 'admin::app.layouts.configuration',
        'route' => 'admin.configuration.index',
        'sort' => 9,
        'icon-class' => 'icon-configuration',
    ],

    /**
     * Help.
     */
    [
        'key' => 'help',
        'name' => 'admin::app.layouts.help',
        'route' => 'admin.help.index',
        'sort' => 10,
        'icon-class' => 'icon-help',
    ],
];
