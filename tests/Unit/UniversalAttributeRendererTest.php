<?php

use Webkul\Attribute\Models\Attribute;
use Webkul\Attribute\Models\AttributeOption;
use Webkul\Moldable\Services\AttributeRenderer;

test('MOLD-028 & MOLD-029: Text & Textarea attribute renderer output correct HTML components', function () {
    $attrText = new Attribute([
        'code'        => 'user_bio',
        'name'        => 'User Bio',
        'type'        => 'text',
        'is_required' => 1,
        'is_unique'   => 1,
    ]);

    $htmlText = AttributeRenderer::render($attrText, 'Hello World');

    expect($htmlText)->toContain('name="user_bio"');
    expect($htmlText)->toContain('value="Hello World"');
    expect($htmlText)->toContain('required');
    expect($htmlText)->toContain('data-unique=1');

    $attrArea = new Attribute([
        'code' => 'full_description',
        'name' => 'Full Description',
        'type' => 'textarea',
    ]);

    $htmlArea = AttributeRenderer::render($attrArea, 'Long description text');
    expect($htmlArea)->toContain('<textarea');
    expect($htmlArea)->toContain('name="full_description"');
    expect($htmlArea)->toContain('Long description text');
});

test('MOLD-030: Numeric & Price attribute renderer output correct number input components', function () {
    $attrPrice = new Attribute([
        'code' => 'deal_price',
        'name' => 'Deal Price',
        'type' => 'price',
    ]);

    $htmlPrice = AttributeRenderer::render($attrPrice, 1499.99);

    expect($htmlPrice)->toContain('type="number"');
    expect($htmlPrice)->toContain('name="deal_price"');
    expect($htmlPrice)->toContain('value="1499.99"');
    expect($htmlPrice)->toContain('$');
});

test('MOLD-031: Selection attribute renderer outputs select, multiselect and boolean components', function () {
    $attrSelect = new Attribute([
        'code' => 'property_type',
        'name' => 'Property Type',
        'type' => 'select',
    ]);

    $htmlSelect = AttributeRenderer::render($attrSelect, 1);
    expect($htmlSelect)->toContain('<select');
    expect($htmlSelect)->toContain('name="property_type"');

    $attrBool = new Attribute([
        'code' => 'is_verified',
        'name' => 'Is Verified',
        'type' => 'boolean',
    ]);

    $htmlBool = AttributeRenderer::render($attrBool, 1);
    expect($htmlBool)->toContain('type="checkbox"');
    expect($htmlBool)->toContain('name="is_verified"');
    expect($htmlBool)->toContain('checked');
});

test('MOLD-032: Date & Datetime attribute renderer outputs calendar date components', function () {
    $attrDate = new Attribute([
        'code' => 'possession_date',
        'name' => 'Possession Date',
        'type' => 'date',
    ]);

    $htmlDate = AttributeRenderer::render($attrDate, '2026-12-31');
    expect($htmlDate)->toContain('type="date"');
    expect($htmlDate)->toContain('name="possession_date"');
    expect($htmlDate)->toContain('value="2026-12-31"');
});

test('MOLD-033: Relationship attribute renderer outputs lookup component', function () {
    $attrLookup = new Attribute([
        'code'        => 'assigned_user_id',
        'name'        => 'Assigned User',
        'type'        => 'lookup',
        'lookup_type' => 'users',
    ]);

    $htmlLookup = AttributeRenderer::render($attrLookup, 5);
    expect($htmlLookup)->toContain('<select');
    expect($htmlLookup)->toContain('name="assigned_user_id"');
    expect($htmlLookup)->toContain('Users');
});

test('MOLD-034: Contact attribute renderer outputs email, phone, and address components', function () {
    $attrEmail = new Attribute([
        'code' => 'contact_email',
        'name' => 'Contact Email',
        'type' => 'email',
    ]);

    $htmlEmail = AttributeRenderer::render($attrEmail, 'john@example.com');
    expect($htmlEmail)->toContain('type="email"');
    expect($htmlEmail)->toContain('name="contact_email"');
    expect($htmlEmail)->toContain('value="john@example.com"');

    $attrAddr = new Attribute([
        'code' => 'billing_address',
        'name' => 'Billing Address',
        'type' => 'address',
    ]);

    $htmlAddr = AttributeRenderer::render($attrAddr, ['address' => '123 Main St', 'city' => 'Metropolis']);
    expect($htmlAddr)->toContain('name="billing_address[address]"');
    expect($htmlAddr)->toContain('value="123 Main St"');
    expect($htmlAddr)->toContain('value="Metropolis"');
});

test('MOLD-035: File & Image attribute renderer outputs file upload, preview, and remove button', function () {
    $attrFile = new Attribute([
        'code' => 'attachment_doc',
        'name' => 'Attachment Doc',
        'type' => 'file',
    ]);

    $htmlFile = AttributeRenderer::render($attrFile, 'documents/contract.pdf');
    expect($htmlFile)->toContain('type="file"');
    expect($htmlFile)->toContain('contract.pdf');
    expect($htmlFile)->toContain('[X] Remove');
});
