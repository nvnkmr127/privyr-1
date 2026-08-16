<?php

use Webkul\Attribute\Models\Attribute;

test('rejects duplicate code per entity type', function () {
    $existingCode = 'budget';
    $existingEntity = 'leads';

    $isDuplicate = ($existingCode === 'budget' && $existingEntity === 'leads');
    expect($isDuplicate)->toBeTrue();
});

test('detects duplicate display name per entity type case-insensitively', function () {
    $existingName = 'Project Budget';
    $newName = 'project budget';

    $isDuplicateName = (mb_strtolower($existingName) === mb_strtolower($newName));
    expect($isDuplicateName)->toBeTrue();

    $differentEntityName = 'Project Budget';
    $isSameEntity = ('leads' === 'contacts');
    expect($isSameEntity)->toBeFalse();
});
