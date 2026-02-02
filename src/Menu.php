<?php

namespace App\Support;

use App\Support\Builders\MenuBuilder;
use Mohib\Menu\Contracts\MenuInterface as MenuContract;

/**
 * @deprecated Use MenuService instead. This class is kept for backward compatibility.
 */
class Menu
{
    private static MenuService $service;

    private static function getService(): MenuService
    {
        if (! isset(self::$service)) {
            self::$service = new MenuService;
        }

        return self::$service;
    }

    /**
     * @deprecated Use MenuService::create() instead
     */
    public static function make(string $name): MenuBuilder
    {
        return self::getService()->create($name);
    }

    /**
     * @deprecated Use MenuService::get() instead
     */
    public static function get(string $name): ?MenuContract
    {
        return self::getService()->get($name);
    }

    /**
     * @deprecated Use MenuService::render() instead
     */
    public static function render(string $name): string
    {
        return self::getService()->render($name);
    }

    /**
     * @deprecated Use MenuService::build() instead
     */
    public static function build(string $name, array $metadata = []): MenuContract
    {
        return self::getService()->build($name, $metadata);
    }

    /**
     * @deprecated Use MenuService::register() instead
     */
    public static function register(MenuContract $menu): void
    {
        self::getService()->register($menu);
    }

    /**
     * @deprecated Use MenuService::has() instead
     */
    public static function has(string $name): bool
    {
        return self::getService()->has($name);
    }

    /**
     * @deprecated Use MenuService::clear() instead
     */
    public static function clear(): void
    {
        self::getService()->clear();
    }

    /**
     * @deprecated Use MenuService::all() instead
     */
    public static function all(): array
    {
        return self::getService()->all();
    }
}
