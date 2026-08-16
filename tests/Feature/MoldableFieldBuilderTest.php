<?php

use Illuminate\Support\Str;
use Webkul\Attribute\Models\Attribute;
use Webkul\Moldable\Models\FieldGroup;
use Webkul\Moldable\Models\Workspace;
use Webkul\Moldable\Models\WorkspaceMember;
use Webkul\User\Models\User;

beforeEach(function () {
    $this->loginAsAdmin();
});

it('creates and validates all supported field types through the builder API', function () {
    $types = [
        'text' => ['name' => 'Custom Text '.Str::random(5), 'type' => 'text'],
        'textarea' => ['name' => 'Custom Textarea '.Str::random(5), 'type' => 'textarea'],
        'price' => ['name' => 'Custom Price '.Str::random(5), 'type' => 'price'],
        'date' => ['name' => 'Custom Date '.Str::random(5), 'type' => 'date'],
        'datetime' => ['name' => 'Custom Datetime '.Str::random(5), 'type' => 'datetime'],
        'select' => ['name' => 'Custom Select '.Str::random(5), 'type' => 'select', 'options' => [['name' => 'Option A'], ['name' => 'Option B']]],
        'multiselect' => ['name' => 'Custom Multiselect '.Str::random(5), 'type' => 'multiselect', 'options' => [['name' => 'Choice 1'], ['name' => 'Choice 2']]],
        'checkbox' => ['name' => 'Custom Checkbox '.Str::random(5), 'type' => 'checkbox', 'options' => [['name' => 'Opt 1']]],
        'boolean' => ['name' => 'Custom Boolean '.Str::random(5), 'type' => 'boolean'],
        'lookup' => ['name' => 'Custom Lookup '.Str::random(5), 'type' => 'lookup', 'lookup_type' => 'persons'],
        'email' => ['name' => 'Custom Email '.Str::random(5), 'type' => 'email'],
        'phone' => ['name' => 'Custom Phone '.Str::random(5), 'type' => 'phone'],
        'address' => ['name' => 'Custom Address '.Str::random(5), 'type' => 'address'],
        'file' => ['name' => 'Custom File '.Str::random(5), 'type' => 'file'],
        'image' => ['name' => 'Custom Image '.Str::random(5), 'type' => 'image'],
    ];

    $createdIds = [];

    foreach ($types as $typeKey => $payload) {
        $response = $this->postJson('/v1/moldable/fields', array_merge([
            'entity_type' => 'leads',
            'quick_add' => true,
            'is_required' => false,
        ], $payload));

        $response->assertStatus(201);
        $data = $response->json();
        expect($data['type'])->toBe($typeKey);
        expect($data['name'])->toBe($payload['name']);
        $createdIds[] = $data['id'];

        if (isset($payload['options'])) {
            expect(count($data['options']))->toBe(count($payload['options']));
        }
    }

    // Clean up
    Attribute::whereIn('id', $createdIds)->delete();
});

it('requires at least one option for select, multiselect, and checkbox fields', function () {
    foreach (['select', 'multiselect', 'checkbox'] as $choiceType) {
        $res = $this->postJson('/v1/moldable/fields', [
            'entity_type' => 'leads',
            'name' => 'Empty Choices '.Str::random(5),
            'type' => $choiceType,
            'options' => [],
        ]);

        $res->assertStatus(422);
        expect($res->json('message'))->toContain('option is required');
    }
});

it('rejects duplicate display names for the same entity', function () {
    $name = 'Dup Field '.Str::random(6);

    $this->postJson('/v1/moldable/fields', [
        'entity_type' => 'leads',
        'name' => $name,
        'type' => 'text',
    ])->assertStatus(201);

    $this->postJson('/v1/moldable/fields', [
        'entity_type' => 'leads',
        'name' => $name,
        'type' => 'text',
    ])->assertStatus(422);

    Attribute::where('name', $name)->delete();
});

it('updates an existing field while preserving option IDs and settings', function () {
    $name = 'Editable Field '.Str::random(5);

    $created = $this->postJson('/v1/moldable/fields', [
        'entity_type' => 'leads',
        'name' => $name,
        'type' => 'select',
        'options' => [
            ['name' => 'Red', 'sort_order' => 0],
            ['name' => 'Blue', 'sort_order' => 1],
        ],
    ])->assertStatus(201)->json();

    $originalOptId = $created['options'][0]['id'];

    $updated = $this->putJson('/v1/moldable/fields/'.$created['id'], [
        'name' => $name.' Updated',
        'is_required' => true,
        'options' => [
            ['id' => $originalOptId, 'name' => 'Crimson Red', 'sort_order' => 0],
            ['name' => 'Emerald Green', 'sort_order' => 1],
        ],
    ])->assertStatus(200)->json();

    expect($updated['name'])->toBe($name.' Updated');
    expect($updated['is_required'])->toBeTrue();

    // Verify original option was updated, not re-created with a new ID
    $updatedOptIds = collect($updated['options'])->pluck('id')->all();
    expect(in_array($originalOptId, $updatedOptIds))->toBeTrue();

    Attribute::where('id', $created['id'])->delete();
});

it('reorders user-defined fields via the reorder API', function () {
    $fieldA = $this->postJson('/v1/moldable/fields', [
        'entity_type' => 'leads',
        'name' => 'Field A '.Str::random(4),
        'type' => 'text',
        'sort_order' => 0,
    ])->json();

    $fieldB = $this->postJson('/v1/moldable/fields', [
        'entity_type' => 'leads',
        'name' => 'Field B '.Str::random(4),
        'type' => 'text',
        'sort_order' => 1,
    ])->json();

    $this->postJson('/v1/moldable/fields/reorder', [
        'orders' => [
            ['id' => $fieldA['id'], 'sort_order' => 10],
            ['id' => $fieldB['id'], 'sort_order' => 5],
        ],
    ])->assertStatus(200);

    expect(Attribute::find($fieldA['id'])->sort_order)->toBe(10);
    expect(Attribute::find($fieldB['id'])->sort_order)->toBe(5);

    Attribute::whereIn('id', [$fieldA['id'], $fieldB['id']])->delete();
});

it('manages presentation groups (create, rename, reorder, assign, delete)', function () {
    $groupRes = $this->postJson('/v1/moldable/groups', [
        'name' => 'Project Timeline '.Str::random(4),
        'entity_type' => 'leads',
    ]);
    $groupRes->assertStatus(201);
    $group = $groupRes->json();

    // Rename group
    $renamedRes = $this->putJson('/v1/moldable/groups/'.$group['id'], [
        'name' => 'Updated Timeline Group',
    ]);
    $renamedRes->assertStatus(200);
    expect($renamedRes->json('name'))->toBe('Updated Timeline Group');

    // Create a field and assign to group
    $field = $this->postJson('/v1/moldable/fields', [
        'entity_type' => 'leads',
        'name' => 'Timeline Date '.Str::random(4),
        'type' => 'date',
    ])->json();

    $assignRes = $this->postJson('/v1/moldable/groups/'.$group['id'].'/assign', [
        'attribute_ids' => [$field['id']],
    ]);
    $assignRes->assertStatus(200);
    expect(count($assignRes->json('group_attributes') ?? $assignRes->json('groupAttributes')))->toBe(1);

    // Delete group (attributes must not be deleted)
    $this->deleteJson('/v1/moldable/groups/'.$group['id'])->assertStatus(200);
    $this->assertDatabaseMissing('moldable_field_groups', ['id' => $group['id']]);
    $this->assertDatabaseHas('attributes', ['id' => $field['id']]);

    Attribute::where('id', $field['id'])->delete();
});

it('enforces tenant isolation across workspaces for presentation groups', function () {
    $user = getDefaultAdmin() ?? User::first();

    // Create Workspace A
    $wsA = Workspace::create(['name' => 'Tenant Alpha '.Str::random(4), 'slug' => 'alpha-'.Str::random(5), 'is_active' => true]);
    WorkspaceMember::create(['workspace_id' => $wsA->id, 'user_id' => $user->id, 'role' => 'owner', 'is_active' => true]);

    // Create Workspace B
    $wsB = Workspace::create(['name' => 'Tenant Beta '.Str::random(4), 'slug' => 'beta-'.Str::random(5), 'is_active' => true]);
    WorkspaceMember::create(['workspace_id' => $wsB->id, 'user_id' => $user->id, 'role' => 'owner', 'is_active' => true]);

    // Tenant A creates a group
    $groupA = $this->withHeader('X-Workspace-Id', $wsA->id)->postJson('/v1/moldable/groups', [
        'name' => 'Alpha Confidential Group',
        'entity_type' => 'leads',
    ])->assertStatus(201)->json();

    // Tenant B cannot see Tenant A's group in index
    $groupsB = $this->withHeader('X-Workspace-Id', $wsB->id)->getJson('/v1/moldable/groups?entity_type=leads')->assertStatus(200)->json();
    expect(collect($groupsB)->pluck('id')->contains($groupA['id']))->toBeFalse();

    // Tenant B cannot update Tenant A's group
    $this->withHeader('X-Workspace-Id', $wsB->id)->putJson('/v1/moldable/groups/'.$groupA['id'], [
        'name' => 'Hacked Group Name',
    ])->assertStatus(404);

    // Tenant B cannot delete Tenant A's group
    $this->withHeader('X-Workspace-Id', $wsB->id)->deleteJson('/v1/moldable/groups/'.$groupA['id'])->assertStatus(404);

    // Clean up
    FieldGroup::where('id', $groupA['id'])->delete();
    $wsA->delete();
    $wsB->delete();
});

it('refuses to delete a system attribute', function () {
    $system = Attribute::where('is_user_defined', false)->first();

    if (! $system) {
        $this->markTestSkipped('No system attribute present to test against.');
    }

    $this->deleteJson('/v1/moldable/fields/'.$system->id)->assertStatus(403);
    $this->assertDatabaseHas('attributes', ['id' => $system->id]);
});

it('lists fields per entity and isolates entity scope', function () {
    $leadField = $this->postJson('/v1/moldable/fields', [
        'entity_type' => 'leads',
        'name' => 'Lead Unique '.Str::random(4),
        'type' => 'text',
    ])->json();

    $personField = $this->postJson('/v1/moldable/fields', [
        'entity_type' => 'persons',
        'name' => 'Person Unique '.Str::random(4),
        'type' => 'text',
    ])->json();

    $orgField = $this->postJson('/v1/moldable/fields', [
        'entity_type' => 'organizations',
        'name' => 'Org Unique '.Str::random(4),
        'type' => 'text',
    ])->json();

    $leadRes = $this->getJson('/v1/moldable/fields?entity_type=leads')->json();
    $leadIds = collect($leadRes)->pluck('id');
    expect($leadIds->contains($leadField['id']))->toBeTrue();
    expect($leadIds->contains($personField['id']))->toBeFalse();
    expect($leadIds->contains($orgField['id']))->toBeFalse();

    $personRes = $this->getJson('/v1/moldable/fields?entity_type=persons')->json();
    $personIds = collect($personRes)->pluck('id');
    expect($personIds->contains($personField['id']))->toBeTrue();
    expect($personIds->contains($leadField['id']))->toBeFalse();

    Attribute::whereIn('id', [$leadField['id'], $personField['id'], $orgField['id']])->delete();
});

it('reorders groups via the groups reorder endpoint', function () {
    $grpA = $this->postJson('/v1/moldable/groups', ['name' => 'Group A '.Str::random(4), 'entity_type' => 'leads'])->json();
    $grpB = $this->postJson('/v1/moldable/groups', ['name' => 'Group B '.Str::random(4), 'entity_type' => 'leads'])->json();

    $this->postJson('/v1/moldable/groups/reorder', [
        'orders' => [
            ['id' => $grpA['id'], 'sort_order' => 15],
            ['id' => $grpB['id'], 'sort_order' => 5],
        ],
    ])->assertStatus(200);

    expect(FieldGroup::find($grpA['id'])->sort_order)->toBe(15);
    expect(FieldGroup::find($grpB['id'])->sort_order)->toBe(5);

    FieldGroup::whereIn('id', [$grpA['id'], $grpB['id']])->delete();
});

it('rejects assigning fields from a different entity to a group', function () {
    $leadGroup = $this->postJson('/v1/moldable/groups', [
        'name' => 'Lead Only Group '.Str::random(4),
        'entity_type' => 'leads',
    ])->json();

    $personField = $this->postJson('/v1/moldable/fields', [
        'entity_type' => 'persons',
        'name' => 'Person Field '.Str::random(4),
        'type' => 'text',
    ])->json();

    $this->postJson('/v1/moldable/groups/'.$leadGroup['id'].'/assign', [
        'attribute_ids' => [$personField['id']],
    ])->assertStatus(422);

    FieldGroup::where('id', $leadGroup['id'])->delete();
    Attribute::where('id', $personField['id'])->delete();
});

it('renders the builder web interface with 200 OK and expected components', function () {
    $res = $this->get('/admin/moldable/builder');
    $res->assertStatus(200);
    $res->assertSee('Field Builder');
    $res->assertSee('Presentation Groups');
    $res->assertSee('Quick Field Types');
    $res->assertSee('entity-pill-bar', false);
    $res->assertSee('add-field-drawer', false);
});
