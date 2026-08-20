<?php

return [
    [
        'key' => 'dashboard',
        'name' => 'admin::app.layouts.dashboard',
        'route' => 'admin.dashboard.index',
        'sort' => 1,
    ], [
        'key' => 'leads',
        'name' => 'admin::app.acl.leads',
        'route' => 'admin.leads.index',
        'sort' => 2,
    ], [
        'key' => 'leads.create',
        'name' => 'admin::app.acl.create',
        'route' => ['admin.leads.create', 'admin.leads.store'],
        'sort' => 1,
    ], [
        'key' => 'leads.create.quick-create',
        'name' => 'admin::app.acl.quick_add',
        'route' => ['admin.leads.create', 'admin.leads.store'],
        'sort' => 1,
    ], [
        'key' => 'leads.view',
        'name' => 'admin::app.acl.view',
        'route' => 'admin.leads.view',
        'sort' => 2,
    ], [
        'key' => 'leads.edit',
        'name' => 'admin::app.acl.edit',
        'route' => ['admin.leads.edit', 'admin.leads.update', 'admin.leads.mass_update'],
        'sort' => 3,
    ], [
        'key' => 'leads.delete',
        'name' => 'admin::app.acl.delete',
        'route' => ['admin.leads.delete', 'admin.leads.mass_delete'],
        'sort' => 4,
    ], [
        'key' => 'leads.view_audit',
        'name' => 'View Audit',
        'route' => ['admin.leads.audits.index'],
        'sort' => 5,
    ], [
        'key' => 'leads.qualify',
        'name' => 'admin::app.acl.qualify',
        'route' => ['admin.leads.qualify'],
        'sort' => 6,
    ], [
        'key' => 'leads.status.change',
        'name' => 'Change Lifecycle Status',
        'route' => ['admin.leads.status.update'],
        'sort' => 6,
    ], [
        'key' => 'leads.disqualify',
        'name' => 'admin::app.acl.disqualify',
        'route' => ['admin.leads.disqualify'],
        'sort' => 6,
    ], [
        'key' => 'activities',
        'name' => 'admin::app.acl.activities',
        'route' => 'admin.activities.index',
        'sort' => 5,
    ], [
        'key' => 'activities.create',
        'name' => 'admin::app.acl.create',
        'route' => ['admin.activities.create', 'admin.activities.store'],
        'sort' => 1,
    ], [
        'key' => 'activities.edit',
        'name' => 'admin::app.acl.edit',
        'route' => ['admin.activities.edit', 'admin.activities.update', 'admin.activities.mass_update'],
        'sort' => 2,
    ], [
        'key' => 'activities.delete',
        'name' => 'admin::app.acl.delete',
        'route' => ['admin.activities.delete', 'admin.activities.mass_delete'],
        'sort' => 3,
    ], [
        'key' => 'settings',
        'name' => 'admin::app.acl.settings',
        'route' => 'admin.settings.index',
        'sort' => 8,
    ], [
        'key' => 'settings.user',
        'name' => 'admin::app.acl.user',
        'route' => ['admin.settings.groups.index', 'admin.settings.roles.index', 'admin.settings.users.index'],
        'sort' => 1,
    ], [
        'key' => 'settings.user.groups',
        'name' => 'admin::app.acl.groups',
        'route' => 'admin.settings.groups.index',
        'sort' => 1,
    ], [
        'key' => 'settings.user.groups.create',
        'name' => 'admin::app.acl.create',
        'route' => ['admin.settings.groups.create', 'admin.settings.groups.store'],
        'sort' => 1,
    ], [
        'key' => 'settings.user.groups.edit',
        'name' => 'admin::app.acl.edit',
        'route' => ['admin.settings.groups.edit', 'admin.settings.groups.update'],
        'sort' => 2,
    ], [
        'key' => 'settings.user.groups.delete',
        'name' => 'admin::app.acl.delete',
        'route' => 'admin.settings.groups.delete',
        'sort' => 3,
    ], [
        'key' => 'settings.user.roles',
        'name' => 'admin::app.acl.roles',
        'route' => 'admin.settings.roles.index',
        'sort' => 2,
    ], [
        'key' => 'settings.user.roles.create',
        'name' => 'admin::app.acl.create',
        'route' => ['admin.settings.roles.create', 'admin.settings.roles.store'],
        'sort' => 1,
    ], [
        'key' => 'settings.user.roles.edit',
        'name' => 'admin::app.acl.edit',
        'route' => ['admin.settings.roles.edit', 'admin.settings.roles.update'],
        'sort' => 2,
    ], [
        'key' => 'settings.user.roles.delete',
        'name' => 'admin::app.acl.delete',
        'route' => 'admin.settings.roles.delete',
        'sort' => 3,
    ], [
        'key' => 'settings.user.users',
        'name' => 'admin::app.acl.users',
        'route' => 'admin.settings.users.index',
        'sort' => 3,
    ], [
        'key' => 'settings.user.users.create',
        'name' => 'admin::app.acl.create',
        'route' => ['admin.settings.users.create', 'admin.settings.users.store'],
        'sort' => 1,
    ], [
        'key' => 'settings.user.users.edit',
        'name' => 'admin::app.acl.edit',
        'route' => ['admin.settings.users.edit', 'admin.settings.users.update', 'admin.settings.users.mass_update'],
        'sort' => 2,
    ], [
        'key' => 'settings.user.users.delete',
        'name' => 'admin::app.acl.delete',
        'route' => ['admin.settings.users.delete', 'admin.settings.users.mass_delete'],
        'sort' => 3,
    ], [
        'key' => 'settings.lead',
        'name' => 'admin::app.acl.lead',
        'route' => ['admin.settings.pipelines.index', 'admin.settings.sources.index', 'admin.settings.types.index'],
        'sort' => 2,
    ], [
        'key' => 'settings.lead.pipelines',
        'name' => 'admin::app.acl.pipelines',
        'route' => 'admin.settings.pipelines.index',
        'sort' => 1,
    ], [
        'key' => 'settings.lead.pipelines.create',
        'name' => 'admin::app.acl.create',
        'route' => ['admin.settings.pipelines.create', 'admin.settings.pipelines.store'],
        'sort' => 1,
    ], [
        'key' => 'settings.lead.pipelines.edit',
        'name' => 'admin::app.acl.edit',
        'route' => ['admin.settings.pipelines.edit', 'admin.settings.pipelines.update'],
        'sort' => 2,
    ], [
        'key' => 'settings.lead.pipelines.delete',
        'name' => 'admin::app.acl.delete',
        'route' => 'admin.settings.pipelines.delete',
        'sort' => 3,
    ], [
        'key' => 'settings.lead.sources',
        'name' => 'admin::app.acl.sources',
        'route' => 'admin.settings.sources.index',
        'sort' => 2,
    ], [
        'key' => 'settings.lead.sources.create',
        'name' => 'admin::app.acl.create',
        'route' => ['admin.settings.sources.store'],
        'sort' => 1,
    ], [
        'key' => 'settings.lead.sources.edit',
        'name' => 'admin::app.acl.edit',
        'route' => ['admin.settings.sources.edit', 'admin.settings.sources.update'],
        'sort' => 2,
    ], [
        'key' => 'settings.lead.sources.delete',
        'name' => 'admin::app.acl.delete',
        'route' => 'admin.settings.sources.delete',
        'sort' => 3,
    ], [
        'key' => 'settings.lead.types',
        'name' => 'admin::app.acl.types',
        'route' => 'admin.settings.types.index',
        'sort' => 3,
    ], [
        'key' => 'settings.lead.types.create',
        'name' => 'admin::app.acl.create',
        'route' => ['admin.settings.types.store'],
        'sort' => 1,
    ], [
        'key' => 'settings.lead.types.edit',
        'name' => 'admin::app.acl.edit',
        'route' => ['admin.settings.types.edit', 'admin.settings.types.update'],
        'sort' => 2,
    ], [
        'key' => 'settings.lead.types.delete',
        'name' => 'admin::app.acl.delete',
        'route' => 'admin.settings.types.delete',
        'sort' => 3,
    ], [
        'key' => 'settings.inventory',
        'name' => 'admin::app.acl.inventory',
        'route' => ['admin.settings.warehouses.index'],
        'sort' => 3,
    ], [
        'key' => 'settings.inventory.warehouse',
        'name' => 'admin::app.acl.warehouses',
        'route' => ['admin.settings.warehouses.index'],
        'sort' => 1,
    ], [
        'key' => 'settings.inventory.warehouse.create',
        'name' => 'admin::app.acl.create',
        'route' => ['admin.settings.warehouses.create', 'admin.settings.warehouses.store'],
        'sort' => 1,
    ], [
        'key' => 'settings.inventory.warehouse.edit',
        'name' => 'admin::app.acl.edit',
        'route' => ['admin.settings.warehouses.edit', 'admin.settings.warehouses.update'],
        'sort' => 2,
    ], [
        'key' => 'settings.inventory.warehouse.delete',
        'name' => 'admin::app.acl.delete',
        'route' => 'admin.settings.warehouses.delete',
        'sort' => 3,
    ], [
        'key' => 'settings.automation',
        'name' => 'admin::app.acl.automation',
        'route' => ['admin.settings.attributes.index', 'admin.settings.email_templates.index', 'admin.settings.workflows.index'],
        'sort' => 4,
    ], [
        'key' => 'settings.automation.moldable_builder',
        'name' => 'admin::app.acl.moldable-builder',
        'route' => 'admin.moldable.builder.index',
        'sort' => 0,
    ], [
        'key' => 'settings.automation.attributes',
        'name' => 'admin::app.acl.attributes',
        'route' => 'admin.settings.attributes.index',
        'sort' => 1,
    ], [
        'key' => 'settings.automation.attributes.create',
        'name' => 'admin::app.acl.create',
        'route' => ['admin.settings.attributes.create', 'admin.settings.attributes.store'],
        'sort' => 1,
    ], [
        'key' => 'settings.automation.attributes.edit',
        'name' => 'admin::app.acl.edit',
        'route' => ['admin.settings.attributes.edit', 'admin.settings.attributes.update', 'admin.settings.attributes.mass_update'],
        'sort' => 2,
    ], [
        'key' => 'settings.automation.attributes.delete',
        'name' => 'admin::app.acl.delete',
        'route' => 'admin.settings.attributes.delete',
        'sort' => 3,
    ], [
        'key' => 'settings.automation.email_templates',
        'name' => 'admin::app.acl.email-templates',
        'route' => 'admin.settings.email_templates.index',
        'sort' => 2,
    ], [
        'key' => 'settings.automation.email_templates.create',
        'name' => 'admin::app.acl.create',
        'route' => ['admin.settings.email_templates.create', 'admin.settings.email_templates.store'],
        'sort' => 1,
    ], [
        'key' => 'settings.automation.email_templates.edit',
        'name' => 'admin::app.acl.edit',
        'route' => ['admin.settings.email_templates.edit', 'admin.settings.email_templates.update'],
        'sort' => 2,
    ], [
        'key' => 'settings.automation.email_templates.delete',
        'name' => 'admin::app.acl.delete',
        'route' => 'admin.settings.email_templates.delete',
        'sort' => 3,
    ], [
        'key' => 'settings.automation.workflows',
        'name' => 'admin::app.acl.workflows',
        'route' => 'admin.settings.workflows.index',
        'sort' => 3,
    ], [
        'key' => 'settings.automation.workflows.create',
        'name' => 'admin::app.acl.create',
        'route' => ['admin.settings.workflows.create', 'admin.settings.workflows.store'],
        'sort' => 1,
    ], [
        'key' => 'settings.automation.workflows.edit',
        'name' => 'admin::app.acl.edit',
        'route' => ['admin.settings.workflows.edit', 'admin.settings.workflows.update'],
        'sort' => 2,
    ], [
        'key' => 'settings.automation.workflows.delete',
        'name' => 'admin::app.acl.delete',
        'route' => 'admin.settings.workflows.delete',
        'sort' => 3,
    ], [
        'key' => 'settings.automation.events',
        'name' => 'admin::app.acl.event',
        'route' => 'admin.settings.marketing.events.index',
        'sort' => 4,
    ], [
        'key' => 'settings.automation.events.create',
        'name' => 'admin::app.acl.create',
        'route' => ['admin.settings.marketing.events.create', 'admin.settings.marketing.events.store'],
        'sort' => 1,
    ], [
        'key' => 'settings.automation.events.edit',
        'name' => 'admin::app.acl.edit',
        'route' => ['admin.settings.marketing.events.edit', 'admin.settings.marketing.events.update'],
        'sort' => 2,
    ], [
        'key' => 'settings.automation.events.delete',
        'name' => 'admin::app.acl.delete',
        'route' => ['admin.settings.marketing.events.delete', 'admin.settings.marketing.events.mass_delete'],
        'sort' => 3,
    ], [
        'key' => 'settings.automation.campaigns',
        'name' => 'admin::app.acl.campaigns',
        'route' => 'admin.settings.marketing.campaigns.index',
        'sort' => 5,
    ], [
        'key' => 'settings.automation.campaigns.create',
        'name' => 'admin::app.acl.create',
        'route' => ['admin.settings.marketing.campaigns.create', 'admin.settings.marketing.campaigns.store'],
        'sort' => 1,
    ], [
        'key' => 'settings.automation.campaigns.edit',
        'name' => 'admin::app.acl.edit',
        'route' => ['admin.settings.marketing.campaigns.edit', 'admin.settings.marketing.campaigns.update'],
        'sort' => 2,
    ], [
        'key' => 'settings.automation.campaigns.delete',
        'name' => 'admin::app.acl.delete',
        'route' => ['admin.settings.marketing.campaigns.delete', 'admin.settings.marketing.campaigns.mass_delete'],
        'sort' => 3,
    ], [
        'key' => 'settings.automation.webhooks',
        'name' => 'admin::app.acl.webhook',
        'route' => 'admin.settings.webhooks.index',
        'sort' => 6,
    ], [
        'key' => 'settings.automation.webhooks.create',
        'name' => 'admin::app.acl.create',
        'route' => ['admin.settings.webhooks.create', 'admin.settings.webhooks.store'],
        'sort' => 1,
    ], [
        'key' => 'settings.automation.webhooks.edit',
        'name' => 'admin::app.acl.edit',
        'route' => ['admin.settings.webhooks.edit', 'admin.settings.webhooks.update'],
        'sort' => 2,
    ], [
        'key' => 'settings.automation.webhooks.delete',
        'name' => 'admin::app.acl.delete',
        'route' => 'admin.settings.webhooks.delete',
        'sort' => 3,
    ], [
        'key' => 'settings.automation.data_transfer',
        'name' => 'admin::app.acl.data-transfer',
        'route' => 'admin.settings.data_transfer.imports.index',
        'sort' => 7,
    ], [
        'key' => 'settings.automation.data_transfer.imports',
        'name' => 'admin::app.acl.imports',
        'route' => 'admin.settings.data_transfer.imports.index',
        'sort' => 1,
    ], [
        'key' => 'settings.automation.data_transfer.imports.create',
        'name' => 'admin::app.acl.create',
        'route' => 'admin.settings.data_transfer.imports.create',
        'sort' => 1,
    ], [
        'key' => 'settings.automation.data_transfer.imports.edit',
        'name' => 'admin::app.acl.edit',
        'route' => 'admin.settings.data_transfer.imports.edit',
        'sort' => 2,
    ], [
        'key' => 'settings.automation.data_transfer.imports.delete',
        'name' => 'admin::app.acl.delete',
        'route' => 'admin.settings.data_transfer.imports.delete',
        'sort' => 3,
    ], [
        'key' => 'settings.automation.data_transfer.imports.import',
        'name' => 'admin::app.acl.import',
        'route' => 'admin.settings.data_transfer.imports.imports',
        'sort' => 4,
    ], [
        'key' => 'settings.other_settings',
        'name' => 'admin::app.acl.other-settings',
        'route' => 'admin.settings.tags.index',
        'sort' => 5,
    ], [
        'key' => 'settings.other_settings.tags',
        'name' => 'admin::app.acl.tags',
        'route' => 'admin.settings.tags.index',
        'sort' => 1,
    ], [
        'key' => 'settings.other_settings.tags.create',
        'name' => 'admin::app.acl.create',
        'route' => ['admin.settings.tags.create', 'admin.settings.tags.store', 'admin.leads.tags.attach'],
        'sort' => 1,
    ], [
        'key' => 'settings.other_settings.tags.edit',
        'name' => 'admin::app.acl.edit',
        'route' => ['admin.settings.tags.edit', 'admin.settings.tags.update'],
        'sort' => 1,
    ], [
        'key' => 'settings.other_settings.tags.delete',
        'name' => 'admin::app.acl.delete',
        'route' => ['admin.settings.tags.delete', 'admin.settings.tags.mass_delete', 'admin.leads.tags.detach'],
        'sort' => 2,
    ], [
        'key' => 'configuration',
        'name' => 'admin::app.acl.configuration',
        'route' => 'admin.configuration.index',
        'sort' => 9,
    ], [
        'key' => 'help',
        'name' => 'admin::app.acl.help',
        'route' => 'admin.help.index',
        'sort' => 10,
    ],
];
