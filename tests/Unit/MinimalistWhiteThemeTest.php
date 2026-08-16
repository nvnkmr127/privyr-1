<?php

test('minimalist white admin UI theme uses bg-white and slate typography', function () {
    $layoutPath = base_path('packages/Webkul/Moldable/src/Resources/views/layouts/app.blade.php');
    $content    = file_get_contents($layoutPath);

    expect(str_contains($content, 'bg-white'))->toBeTrue();
    expect(str_contains($content, 'bg-slate-50'))->toBeTrue();
    expect(str_contains($content, 'text-slate-900'))->toBeTrue();
});

test('moldable field builder renders cleanly in minimalist white admin layout', function () {
    $html = view('moldable::builder.index')->render();

    expect(str_contains($html, 'Field Builder'))->toBeTrue();
    expect(str_contains($html, 'bg-white'))->toBeTrue();
});
