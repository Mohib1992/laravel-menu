<?php

namespace Mohib\Menu\Facades;

use Illuminate\Support\Facades\Facade;
use Mohib\Menu\MenuService;

/**
 * @method static \Mohib\Menu\Contracts\MenuInterface make(string $name, \Closure $callback = null, array $metadata = [])
 * @method static \Mohib\Menu\Contracts\MenuInterface create(string $name, array $metadata = [], array $options = [])
 * @method static \Mohib\Menu\Contracts\MenuInterface build(string $name, array $metadata = [])
 * @method static bool has(string $name)
 * @method static string render(string $name, array $config = [])
 * @method static mixed get(string $name)
 * @method static void register(\Mohib\Menu\Contracts\MenuInterface $menu)
 * @method static void clear()
 * @method static array validate()
 * @method static array getStats()
 * @method static \Mohib\Menu\Builders\MenuBuilder builder(string $name)
 * @method static void setRenderer(\Mohib\Menu\Contracts\MenuRendererInterface $renderer)
 * @method static \Mohib\Menu\Contracts\MenuRendererInterface getRenderer()
 * @method static \Mohib\Menu\Contracts\MenuRegistryInterface getRegistry()
 * @method static mixed simple(string $name, array $items, array $metadata = [])
 * @method static void forget(string $name)
 * @method static void flush()
 */
class Menu extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return MenuService::class;
    }
}
