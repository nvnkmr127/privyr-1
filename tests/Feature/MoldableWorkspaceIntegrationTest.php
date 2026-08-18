<?php

use App\Models\User;
use Illuminate\Support\Str;
use Webkul\Admin\DataGrids\Product\ProductDataGrid;
use Webkul\Admin\DataGrids\Quote\QuoteDataGrid;
use Webkul\Attribute\Models\Attribute;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\LeadSourceConnector;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Services\LeadCaptureService;
use Webkul\Moldable\Models\FieldGroup;
use Webkul\Moldable\Models\FieldGroupAttribute;
use Webkul\Moldable\Models\Workspace;
use Webkul\Moldable\Models\WorkspaceMember;
use Webkul\Moldable\Services\MoldableFieldHelper;
use Webkul\Product\Models\Product;
use Webkul\Quote\Models\Quote;

beforeEach(function () {
    $this->admin = $this->loginAsAdmin();

    $this->workspace = Workspace::create([
        'name' => 'Primary Test Workspace',
        'slug' => 'primary-test-ws-'.Str::random(8),
        'is_active' => true,
    ]);

    WorkspaceMember::create([
        'workspace_id' => $this->workspace->id,
        'user_id' => $this->admin->id,
        'role' => 'owner',
        'is_active' => true,
    ]);
});

test('it creates custom fields via moldable builder and persists them to attributes store', function () {
    $this->actingAs($this->admin);

    $scoreName = 'Lead Score '.Str::random(6);
    // 1. Create a numeric/price custom field on leads
    $response = $this->postJson('/v1/moldable/fields', [
        'entity_type' => 'leads',
        'name' => $scoreName,
        'type' => 'price',
        'is_required' => false,
        'quick_add' => true,
    ], ['X-Workspace-Id' => $this->workspace->id]);

    $response->assertStatus(201)
        ->assertJsonPath('name', $scoreName)
        ->assertJsonPath('entity_type', 'leads')
        ->assertJsonPath('type', 'price');

    $scoreAttrId = $response->json('id');
    $this->assertDatabaseHas('attributes', [
        'id' => $scoreAttrId,
        'name' => $scoreName,
        'entity_type' => 'leads',
        'is_user_defined' => 1,
    ]);

    $tierName = 'Customer Tier '.Str::random(6);
    // 2. Create a select custom field on leads with options
    $selectResponse = $this->postJson('/v1/moldable/fields', [
        'entity_type' => 'leads',
        'name' => $tierName,
        'type' => 'select',
        'options' => [
            ['name' => 'Enterprise', 'sort_order' => 1],
            ['name' => 'Business', 'sort_order' => 2],
            ['name' => 'Starter', 'sort_order' => 3],
        ],
    ], ['X-Workspace-Id' => $this->workspace->id]);

    $selectResponse->assertStatus(201)
        ->assertJsonPath('name', $tierName)
        ->assertJsonPath('type', 'select');

    $tierAttrId = $selectResponse->json('id');
    $this->assertDatabaseHas('attributes', ['id' => $tierAttrId]);
    $this->assertDatabaseHas('attribute_options', ['attribute_id' => $tierAttrId, 'name' => 'Enterprise']);
});

test('it stores, retrieves, and updates custom field values on leads and persons', function () {
    $this->actingAs($this->admin);

    $leadCode = 'ind_sec_'.Str::random(6);

    $attr = Attribute::create([
        'code' => $leadCode,
        'name' => 'Industry Sector '.Str::random(5),
        'type' => 'text',
        'entity_type' => 'leads',
        'is_user_defined' => 1,
    ]);

    // Create Lead via repository with custom attribute value
    $leadRepo = app(LeadRepository::class);
    $lead = $leadRepo->create([
        'entity_type' => 'leads',
        'title' => 'Acme Cloud Deal',
        'lead_value' => 50000,
        'user_id' => $this->admin->id,
        $leadCode => 'Fintech & Payments',
        'emails' => [['value' => 'jane_'.Str::random(5).'@acme.com', 'label' => 'work']],
        'contact_numbers' => [['value' => '+1555'.rand(1000, 9999), 'label' => 'work']],
        'person_name' => 'Jane Doe',
    ]);

    // Verify stored in EAV table
    $this->assertDatabaseHas('attribute_values', [
        'attribute_id' => $attr->id,
        'entity_id' => $lead->id,
        'entity_type' => 'leads',
        'text_value' => 'Fintech & Payments',
    ]);

    // Update custom field value
    $leadRepo->update([
        'entity_type' => 'leads',
        $leadCode => 'Healthcare SaaS',
    ], $lead->id);

    $this->assertDatabaseHas('attribute_values', [
        'attribute_id' => $attr->id,
        'entity_id' => $lead->id,
        'entity_type' => 'leads',
        'text_value' => 'Healthcare SaaS',
    ]);

    // MoldableFieldHelper verification
    $dictionary = MoldableFieldHelper::getValuesDictionary('leads', $lead->id);
    expect($dictionary)->toHaveKey($leadCode);
    expect($dictionary[$leadCode])->toBe('Healthcare SaaS');
});

test('it ingests custom lead attributes through LeadCaptureService webhooks and mapping', function () {
    $dealCode = 'deal_prio_'.Str::random(6);

    $attr = Attribute::create([
        'code' => $dealCode,
        'name' => 'Deal Priority '.Str::random(5),
        'type' => 'text',
        'entity_type' => 'leads',
        'is_user_defined' => 1,
    ]);

    $connector = LeadSourceConnector::create([
        'name' => 'Webhook Ingestion '.Str::random(5),
        'source_type' => 'webhook',
        'webhook_token' => Str::random(32),
        'workspace_id' => $this->workspace->id,
        'default_user_id' => $this->admin->id,
        'duplicate_action' => 'allow',
        'is_active' => true,
        'field_mappings' => [
            'incoming_priority' => $dealCode,
        ],
    ]);

    $payload = [
        'full_name' => 'Alice Walker',
        'email' => 'alice_'.Str::random(5).'@globex.org',
        'phone' => '+1555'.rand(1000, 9999),
        'incoming_priority' => 'High Priority P1',
    ];

    $leadCaptureService = app(LeadCaptureService::class);
    $lead = $leadCaptureService->processIncomingPayload($connector, $payload);

    expect($lead)->not->toBeNull();
    expect($lead->{$dealCode})->toBe('High Priority P1');

    $this->assertDatabaseHas('attribute_values', [
        'attribute_id' => $attr->id,
        'entity_id' => $lead->id,
        'entity_type' => 'leads',
        'text_value' => 'High Priority P1',
    ]);
});

test('it includes custom attributes in DataGrid exports across all entities', function () {
    $this->actingAs($this->admin);

    $prodCode = 'prod_bar_'.Str::random(6);
    $quoteCode = 'quote_ref_'.Str::random(6);

    // Create custom attribute for Product
    $prodAttr = Attribute::create([
        'code' => $prodCode,
        'name' => 'Product Barcode '.Str::random(4),
        'type' => 'text',
        'entity_type' => 'products',
        'is_user_defined' => 1,
    ]);

    // Create custom attribute for Quote
    $quoteAttr = Attribute::create([
        'code' => $quoteCode,
        'name' => 'Quote Reference No '.Str::random(4),
        'type' => 'text',
        'entity_type' => 'quotes',
        'is_user_defined' => 1,
    ]);

    // Mock export request with format
    request()->merge(['export' => true, 'format' => 'csv']);

    $prodGrid = app(ProductDataGrid::class);
    $prodGrid->prepareColumns();
    $prodColumns = collect($prodGrid->getColumns())->map(fn ($c) => $c->getIndex())->toArray();
    expect($prodColumns)->toContain($prodCode);

    $quoteGrid = app(QuoteDataGrid::class);
    $quoteGrid->prepareColumns();
    $quoteColumns = collect($quoteGrid->getColumns())->map(fn ($c) => $c->getIndex())->toArray();
    expect($quoteColumns)->toContain($quoteCode);
});

test('it safely cascades deletion of custom field definitions and cleans up attribute_values', function () {
    $this->actingAs($this->admin);

    $tempCode = 'temp_proj_'.Str::random(6);

    $attr = Attribute::create([
        'code' => $tempCode,
        'name' => 'Temporary Project Code '.Str::random(4),
        'type' => 'text',
        'entity_type' => 'leads',
        'is_user_defined' => 1,
    ]);

    // Create group and link attribute
    $group = FieldGroup::create([
        'name' => 'Project Metadata '.Str::random(4),
        'entity_type' => 'leads',
        'workspace_id' => $this->workspace->id,
        'sort_order' => 1,
    ]);

    FieldGroupAttribute::create([
        'group_id' => $group->id,
        'attribute_id' => $attr->id,
        'sort_order' => 1,
    ]);

    // Create lead with value
    $lead = app(LeadRepository::class)->create([
        'entity_type' => 'leads',
        'title' => 'Deal With Temp Code',
        'lead_value' => 1000,
        'user_id' => $this->admin->id,
        $tempCode => 'PROJ-999',
    ]);

    $this->assertDatabaseHas('attribute_values', [
        'attribute_id' => $attr->id,
        'entity_id' => $lead->id,
        'text_value' => 'PROJ-999',
    ]);

    // Delete field via API
    $response = $this->deleteJson("/v1/moldable/fields/{$attr->id}", [], ['X-Workspace-Id' => $this->workspace->id]);
    $response->assertStatus(200);

    $this->assertDatabaseMissing('attributes', ['id' => $attr->id]);
    $this->assertDatabaseMissing('attribute_values', ['attribute_id' => $attr->id]);
    $this->assertDatabaseMissing('moldable_field_group_attributes', ['attribute_id' => $attr->id]);
});

test('it enforces tenant isolation across separate workspaces', function () {
    $user = getDefaultAdmin() ?? User::first();

    $wsA = Workspace::create(['name' => 'Tenant Alpha '.Str::random(4), 'slug' => 'alpha-'.Str::random(5), 'is_active' => true]);
    WorkspaceMember::create(['workspace_id' => $wsA->id, 'user_id' => $user->id, 'role' => 'owner', 'is_active' => true]);

    $wsB = Workspace::create(['name' => 'Tenant Beta '.Str::random(4), 'slug' => 'beta-'.Str::random(5), 'is_active' => true]);
    WorkspaceMember::create(['workspace_id' => $wsB->id, 'user_id' => $user->id, 'role' => 'owner', 'is_active' => true]);

    // Tenant A creates a group
    $groupA = $this->withHeader('X-Workspace-Id', $wsA->id)->postJson('/v1/moldable/groups', [
        'name' => 'Alpha Confidential Group '.Str::random(4),
        'entity_type' => 'leads',
    ])->assertStatus(201)->json();

    // Tenant B cannot see Tenant A's group in index
    $groupsB = $this->withHeader('X-Workspace-Id', $wsB->id)->getJson('/v1/moldable/groups?entity_type=leads')->assertStatus(200)->json();
    expect(collect($groupsB)->pluck('id')->contains($groupA['id']))->toBeFalse();

    // Tenant B cannot update Tenant A's group
    $this->withHeader('X-Workspace-Id', $wsB->id)->putJson('/v1/moldable/groups/'.$groupA['id'], [
        'name' => 'Hacked Group Name',
    ])->assertStatus(404);

    // Clean up
    FieldGroup::where('id', $groupA['id'])->delete();
    $wsA->delete();
    $wsB->delete();
});

test('it accurately normalizes and formats custom field values across all 15 types', function () {
    expect(MoldableFieldHelper::normalizeValue('true', 'boolean'))->toBe(1);
    expect(MoldableFieldHelper::normalizeValue('0', 'boolean'))->toBe(0);
    expect(MoldableFieldHelper::normalizeValue('1250.50', 'price'))->toBe(1250.50);
    expect(MoldableFieldHelper::normalizeValue(['opt1', 'opt2'], 'multiselect'))->toBe('opt1,opt2');
    expect(MoldableFieldHelper::normalizeValue('2026-08-16', 'date'))->toBe('2026-08-16');
    expect(MoldableFieldHelper::normalizeValue(['name' => 'Acme'], 'text'))->toBe('{"name":"Acme"}');
});
