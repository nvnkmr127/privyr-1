<?php

use Illuminate\Support\Str;
use Webkul\Attribute\Models\Attribute;

/*
 * End-to-end coverage for the Moldable Field Builder API. Unlike the older unit
 * tests that re-implemented the controller logic inline, these drive the real
 * HTTP routes through the session ('user') guard the builder page uses.
 */

it('creates a user-defined field through the builder API', function () {
    $this->loginAsAdmin();

    $name = 'QA Field '.Str::random(6);

    $response = $this->postJson('/v1/moldable/fields', [
        'entity_type' => 'leads',
        'name'        => $name,
        'type'        => 'text',
        'quick_add'   => true,
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('attributes', [
        'name'            => $name,
        'entity_type'     => 'leads',
        'is_user_defined' => 1,
    ]);

    Attribute::where('name', $name)->delete();
});

it('rejects a duplicate display name for the same entity', function () {
    $this->loginAsAdmin();

    $name = 'Dup Field '.Str::random(6);

    $this->postJson('/v1/moldable/fields', [
        'entity_type' => 'leads',
        'name'        => $name,
        'type'        => 'text',
    ])->assertStatus(201);

    $this->postJson('/v1/moldable/fields', [
        'entity_type' => 'leads',
        'name'        => $name,
        'type'        => 'text',
    ])->assertStatus(422);

    Attribute::where('name', $name)->delete();
});

it('lists and deletes a user-defined field', function () {
    $this->loginAsAdmin();

    $name = 'Temp Field '.Str::random(6);

    $created = $this->postJson('/v1/moldable/fields', [
        'entity_type' => 'leads',
        'name'        => $name,
        'type'        => 'text',
    ])->assertStatus(201)->json();

    $this->getJson('/v1/moldable/fields?entity_type=leads')
        ->assertStatus(200)
        ->assertJsonFragment(['id' => $created['id']]);

    $this->deleteJson('/v1/moldable/fields/'.$created['id'])->assertStatus(200);

    $this->assertDatabaseMissing('attributes', ['id' => $created['id']]);
});

it('refuses to delete a system attribute', function () {
    $this->loginAsAdmin();

    $system = Attribute::where('is_user_defined', false)->first();

    if (! $system) {
        $this->markTestSkipped('No system attribute present to test against.');
    }

    $this->deleteJson('/v1/moldable/fields/'.$system->id)->assertStatus(403);

    $this->assertDatabaseHas('attributes', ['id' => $system->id]);
});
