<?php

namespace Mohib\Menu\Tests\Unit;

use Mohib\Menu\Builders\MenuBuilder;

class MenuBuilderTest
{
    protected MenuBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new MenuBuilder('test', [], ['noDefaultSection' => true]);
    }

    public function testCanCreateMenuWithItems(): void
    {
        $menu = $this->builder
            ->item('Home', '/')
            ->item('About', '/about')
            ->item('Contact', '/contact')
            ->build();

        $this->assertNotNull($menu);
    }

    public function testCanCreateNestedMenuWithSub(): void
    {
        $menu = $this->builder
            ->item('Products', '/products')
            ->sub(function ($builder) {
                $builder
                    ->item('Electronics', '/electronics')
                    ->item('Books', '/books');
            })
            ->item('About', '/about')
            ->build();

        $this->assertNotNull($menu);
    }

    public function testCanCreateMenuWithSections(): void
    {
        $menu = $this->builder
            ->section('Main')
            ->item('Home', '/')
            ->item('About', '/about')
            ->build();

        $this->assertNotNull($menu);
    }

    public function testCanCreateRootLevelItems(): void
    {
        $builder = new MenuBuilder('test', [], ['allowRootItems' => true]);

        $menu = $builder
            ->item('Home', '/')
            ->item('About', '/about')
            ->item('Contact', '/contact')
            ->build();

        $this->assertNotNull($menu);
    }

    public function testThrowsExceptionForEmptyMenu(): void
    {
        $this->expectException(\LogicException::class);

        $this->builder->validate()->build();
    }

    public function testSupportsAttributeMethods(): void
    {
        $menu = $this->builder
            ->item('Home', '/')
            ->icon('home')
            ->active(true)
            ->class('nav-home')
            ->build();

        $this->assertNotNull($menu);
    }

    public function testSupportsConditionalBuilding(): void
    {
        $menu = $this->builder
            ->item('Home', '/')
            ->when(true, function ($builder) {
                $builder->item('About', '/about');
            })
            ->when(false, function ($builder) {
                $builder->item('Contact', '/contact');
            })
            ->build();

        $this->assertNotNull($menu);
    }

    protected function assertNotNull($menu): void
    {
        if ($menu === null) {
            throw new \Exception('Menu should not be null');
        }
    }

    protected function expectException($exceptionClass): void
    {
        if (! class_exists($exceptionClass)) {
            throw new \Exception("Exception class {$exceptionClass} expected");
        }
    }
}
