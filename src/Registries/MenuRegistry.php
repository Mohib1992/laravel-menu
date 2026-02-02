<?php

namespace Mohib\Menu\Registries;

use Mohib\Menu\Contracts\MenuInterface as MenuContract;
use Mohib\Menu\Contracts\MenuRegistryInterface as MenuRegistryContract;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

class MenuRegistry implements MenuRegistryContract
{
    private array $menus = [];

    private LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: app('log');
    }

    public function get(string $id): ?MenuContract
    {
        if (! isset($this->menus[$id])) {
            $this->logger->debug("Menu '{$id}' not found in registry");

            return null;
        }

        return $this->menus[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->menus[$id]);
    }

    public function register(MenuContract $menu): self
    {
        $id = $menu->getId();

        if (isset($this->menus[$id])) {
            $this->logger->warning("Overriding existing menu '{$id}' in registry");
        }

        $this->menus[$id] = $menu;
        $this->logger->debug("Menu '{$id}' registered successfully");

        return $this;
    }

    public function remove(string $id): self
    {
        if (! isset($this->menus[$id])) {
            $this->logger->debug("Cannot remove menu '{$id}' - not found in registry");

            return $this;
        }

        unset($this->menus[$id]);
        $this->logger->debug("Menu '{$id}' removed from registry");

        return $this;
    }

    public function all(): array
    {
        return $this->menus;
    }

    public function clear(): self
    {
        $count = count($this->menus);
        $this->menus = [];
        $this->logger->debug("Cleared {$count} menus from registry");

        return $this;
    }

    public function count(): int
    {
        return count($this->menus);
    }

    public function keys(): array
    {
        return array_keys($this->menus);
    }

    public function filter(callable $callback): Collection
    {
        return (new Collection($this->menus))->filter($callback);
    }

    public function isEmpty(): bool
    {
        return empty($this->menus);
    }

    public function getStats(): array
    {
        $stats = [
            'total_menus' => count($this->menus),
            'total_items' => 0,
            'total_sections' => 0,
            'active_items' => 0,
        ];

        foreach ($this->menus as $menu) {
            $stats['total_items'] += $menu->getItemCount();
            $stats['total_sections'] += $menu->getSections()->count();
            $stats['active_items'] += $menu->getActiveItems()->count();
        }

        return $stats;
    }

    public function validate(): array
    {
        $errors = [];
        $warnings = [];

        foreach ($this->menus as $id => $menu) {
            try {
                // Check if menu has items
                if ($menu->getItemCount() === 0) {
                    $warnings[] = "Menu '{$id}' has no items";
                }

                // Check for duplicate item IDs within each menu
                $itemIds = [];
                $this->collectItemIds($menu, $itemIds);
                $duplicates = array_keys(array_filter(array_count_values($itemIds), fn ($count) => $count > 1));

                if (! empty($duplicates)) {
                    $errors[] = "Menu '{$id}' has duplicate item IDs: ".implode(', ', $duplicates);
                }

                // Check for circular references
                if ($this->hasCircularReferences($menu)) {
                    $errors[] = "Menu '{$id}' has circular references in items";
                }

            } catch (\Exception $e) {
                $errors[] = "Menu '{$id}' validation failed: ".$e->getMessage();
            }
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
            'valid' => empty($errors),
        ];
    }

    private function collectItemIds(MenuContract $menu, array &$itemIds): void
    {
        foreach ($menu->getSections() as $section) {
            foreach ($section->getItems() as $item) {
                $itemIds[] = $item->getId();
                $this->collectChildItemIds($item, $itemIds);
            }
        }
    }

    private function collectChildItemIds($item, array &$itemIds): void
    {
        if ($item->hasChildren()) {
            foreach ($item->getChildren() as $child) {
                $itemIds[] = $child->getId();
                $this->collectChildItemIds($child, $itemIds);
            }
        }
    }

    private function hasCircularReferences(MenuContract $menu): bool
    {
        $visited = [];
        $recursionStack = [];

        foreach ($menu->getSections() as $section) {
            foreach ($section->getItems() as $item) {
                if ($this->hasCircularReference($item, $visited, $recursionStack)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function hasCircularReference($item, array &$visited, array &$recursionStack): bool
    {
        $itemId = $item->getId();

        if (isset($recursionStack[$itemId])) {
            return true;
        }

        if (isset($visited[$itemId])) {
            return false;
        }

        $visited[$itemId] = true;
        $recursionStack[$itemId] = true;

        if ($item->hasChildren()) {
            foreach ($item->getChildren() as $child) {
                if ($this->hasCircularReference($child, $visited, $recursionStack)) {
                    return true;
                }
            }
        }

        unset($recursionStack[$itemId]);

        return false;
    }
}
