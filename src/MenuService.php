<?php

namespace Mohib\Menu;

use Mohib\Menu\Builders\MenuBuilder;
use Mohib\Menu\Contracts\MenuInterface as MenuContract;
use Mohib\Menu\Contracts\MenuRegistryInterface as MenuRegistryContract;
use Mohib\Menu\Contracts\MenuRendererInterface as MenuRendererContract;
use Mohib\Menu\Registries\MenuRegistry;
use Mohib\Menu\Renderers\MenuRenderer;
use Closure;

class MenuService
{
    private MenuRegistryContract $registry;

    private MenuRendererContract $renderer;

    public function __construct(
        ?MenuRegistryContract $registry = null,
        ?MenuRendererContract $renderer = null
    ) {
        $this->registry = $registry ?: new MenuRegistry;
        $this->renderer = $renderer ?: new MenuRenderer(config('menu.css_classes', []));
    }

    public function create(string $name, array $metadata = [], array $options = []): MenuBuilder
    {
        return MenuBuilder::create($name, $metadata, $options);
    }

    public function build(string $name, array $metadata = [], array $options = []): MenuContract
    {
        return $this->create($name, $metadata, $options)->build();
    }

    public function register(MenuContract $menu): self
    {
        $this->registry->register($menu);

        return $this;
    }

    public function get(string $name): ?MenuContract
    {
        return $this->registry->get($name);
    }

    public function has(string $name): bool
    {
        return $this->registry->has($name);
    }

    public function render(string $name): string
    {
        $menu = $this->get($name);

        if (! $menu) {
            logger()->warning("Menu '{$name}' not found for rendering");

            return '';
        }

        return $this->renderer->render($menu);
    }

    public function remove(string $name): self
    {
        $this->registry->remove($name);

        return $this;
    }

    public function all(): array
    {
        return $this->registry->all();
    }

    public function clear(): self
    {
        $this->registry->clear();

        return $this;
    }

    public function count(): int
    {
        return count($this->registry->all());
    }

    public function validate(): array
    {
        // Simple validation since registry doesn't have validate method
        $errors = [];
        $warnings = [];

        foreach ($this->registry->all() as $id => $menu) {
            if ($menu->getItemCount() === 0) {
                $warnings[] = "Menu '{$id}' has no items";
            }
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
            'valid' => empty($errors),
        ];
    }

    public function getStats(): array
    {
        $stats = [
            'total_menus' => $this->count(),
            'total_items' => 0,
            'total_sections' => 0,
            'active_items' => 0,
        ];

        foreach ($this->registry->all() as $menu) {
            $stats['total_items'] += $menu->getItemCount();
            $stats['total_sections'] += $menu->getSections()->count();
            $stats['active_items'] += $menu->getActiveItems()->count();
        }

        return $stats;
    }

    // Fluent interface for building and registering in one go
    public function make(string $name, Closure $callback, array $metadata = [], array $options = []): self
    {
        $builder = $this->create($name, $metadata, $options);
        $callback($builder);
        $menu = $builder->build();

        return $this->register($menu);
    }

    // Convenience methods for common patterns
    public function simple(string $name, array $items, array $metadata = [], array $options = []): self
    {
        return $this->make($name, function (MenuBuilder $builder) use ($items) {
            foreach ($items as $item) {
                if (is_array($item) && isset($item['label'])) {
                    $builder->item($item['label'], $item['route'] ?? null)
                        ->when(isset($item['icon']), fn ($b) => $b->icon($item['icon']))
                        ->when(isset($item['badge']), fn ($b) => $b->badge($item['badge']))
                        ->when(isset($item['active']), fn ($b) => $b->active($item['active']));
                }
            }
        }, $metadata, $options);
    }

    // Configuration methods
    public function setRenderer(MenuRendererContract $renderer): self
    {
        $this->renderer = $renderer;

        return $this;
    }

    public function setRegistry(MenuRegistryContract $registry): self
    {
        $this->registry = $registry;

        return $this;
    }

    public function getRenderer(): MenuRendererContract
    {
        return $this->renderer;
    }

    public function getRegistry(): MenuRegistryContract
    {
        return $this->registry;
    }
}
