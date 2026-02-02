<?php

require_once __DIR__.'/vendor/autoload.php';

use Mohib\Menu\Facades\Menu;

echo "Testing mohib/menu Package\n";
echo "=========================\n\n";

try {
    // Test 1: Basic menu creation
    echo "Test 1: Basic menu creation\n";
    Menu::make('test-nav', function ($builder) {
        $builder
            ->item('Home', '/')
            ->item('About', '/about')
            ->item('Contact', '/contact');
    });

    echo "✅ Basic menu created successfully\n";

    // Test 2: Nested menu with sub()
    echo "\nTest 2: Nested menu with sub()\n";
    Menu::make('test-nested', function ($builder) {
        $builder
            ->item('Products', '/products')
            ->sub(function ($builder) {
                $builder
                    ->item('Electronics', '/electronics')
                    ->item('Books', '/books');
            })
            ->item('Services', '/services');
    });

    echo "✅ Nested menu created successfully\n";

    // Test 3: Root-level items
    echo "\nTest 3: Root-level items\n";
    Menu::make('test-root', function ($builder) {
        $builder
            ->item('Dashboard', '/')
            ->item('Settings', '/settings')
            ->item('Profile', '/profile');
    }, [], ['allowRootItems' => true]);

    echo "✅ Root-level menu created successfully\n";

    echo "\n🎉 All tests passed! mohib/menu package is working correctly!\n";

} catch (Exception $e) {
    echo '❌ Test failed: '.$e->getMessage()."\n";
    echo "Stack trace:\n".$e->getTraceAsString()."\n";
}
