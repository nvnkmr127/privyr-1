<?php

test('2-color theme layout app.blade.php uses black slate-900 accents and white background', function () {
    $layoutPath = base_path('packages/Webkul/Moldable/src/Resources/views/layouts/app.blade.php');
    $content = file_get_contents($layoutPath);

    expect(str_contains($content, 'bg-slate-900'))->toBeTrue();
    expect(str_contains($content, 'bg-white'))->toBeTrue();
    expect(str_contains($content, 'MOLDABLE'))->toBeTrue();
});

test('moldable field builder renders cleanly with 2-color monochrome theme', function () {
    $html = view('moldable::builder.index')->render();

    expect(str_contains($html, 'Field Builder'))->toBeTrue();
    expect(str_contains($html, 'bg-slate-900'))->toBeTrue();
    expect(str_contains($html, 'Add Field'))->toBeTrue();
});
