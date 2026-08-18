<?php

test('sidebar and settings menu configuration contains all backend feature routes', function () {
    $menuConfig = include base_path('packages/Webkul/Admin/src/Config/menu.php');
    expect($menuConfig)->toBeArray();

    $expectedRoutes = [
        'admin.moldable.builder.index',
        'admin.settings.web_forms.index',
        'admin.settings.email_templates.index',
        'admin.settings.warehouses.index',
    ];

    $configuredRoutes = array_column($menuConfig, 'route');

    foreach ($expectedRoutes as $route) {
        expect(in_array($route, $configuredRoutes))
            ->toBeTrue("Menu config must include backend feature route '{$route}'");
    }
});
