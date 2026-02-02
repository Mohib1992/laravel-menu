<?php

namespace Mohib\Menu\Builders;

use Mohib\Menu\Contracts\MenuInterface as MenuContract;
use App\Support\Models\Menu;
use App\Support\Models\MenuItem;
use App\Support\Models\MenuSection;
use Closure;
use Illuminate\Support\Collection;
use LogicException;

class MenuBuilder
{
    private string $menuId;

    private array $metadata = [];

    private ?string $currentSectionId = null;

    private ?string $currentItemId = null;

    // Store built objects temporarily during construction
    private array $sections = [];

    private array $items = [];

    private int $counter = 0;

    private bool $allowRootItems = false;

    private Collection $rootItems;

    public function __construct(string $menuId, array $metadata = [], array $options = [])
    {
        if (empty(trim($menuId))) {
            throw new \InvalidArgumentException('Menu ID cannot be empty');
        }

        $this->menuId = $menuId;
        $this->metadata = $metadata;
        $this->allowRootItems = $options['allowRootItems'] ?? false;
        $this->rootItems = new Collection;

        // Create default section only if root items are not allowed AND no explicit section option provided
        if (! $this->allowRootItems && ! ($options['noDefaultSection'] ?? false)) {
            $this->createDefaultSection();
        }
    }

    public function section(string $title, ?string $icon = null): self
    {
        $sectionId = $this->generateId('section');
        $section = new MenuSection($sectionId, $title, $icon);

        $this->sections[$sectionId] = $section;
        $this->currentSectionId = $sectionId;
        $this->currentItemId = null;

        return $this;
    }

    private bool $inSubContext = false;

    public function item(string $label, ?string $route = null): self
    {
        $itemId = $this->generateId('item');
        $item = new MenuItem($itemId, $label, $route);

        if ($this->inSubContext && $this->currentItemId && isset($this->items[$this->currentItemId])) {
            // Add as child of current item only when in sub() context
            $this->items[$this->currentItemId]->addChild($item);
            // Don't update currentItemId in sub context - parent stays current
        } elseif ($this->allowRootItems && ! $this->currentSectionId) {
            // Add as root item when allowed and no section context
            $this->rootItems->put($itemId, $item);
            $this->items[$itemId] = $item;
            $this->currentItemId = $itemId;
        } elseif ($this->currentSectionId && isset($this->sections[$this->currentSectionId])) {
            // Add to current section (default behavior)
            $this->sections[$this->currentSectionId]->addItem($item);
            $this->items[$itemId] = $item;
            $this->currentItemId = $itemId;
        } else {
            throw new LogicException('Cannot add item without a section when root items are not allowed');
        }

        return $this;
    }

    public function sub(Closure $callback): self
    {
        if (! $this->currentItemId) {
            throw new LogicException('sub() must be called after item()');
        }

        // Store previous context
        $previousItemId = $this->currentItemId;
        $previousSubContext = $this->inSubContext;

        // Enter sub context
        $this->inSubContext = true;

        // Execute callback to build sub-items
        $callback($this);

        // Restore context
        $this->currentItemId = $previousItemId;
        $this->inSubContext = $previousSubContext;

        return $this;
    }

    public function __call(string $method, array $arguments): self
    {
        if ($this->currentItemId && isset($this->items[$this->currentItemId])) {
            // Set attribute on current item
            $value = $arguments[0] ?? true;
            $this->items[$this->currentItemId]->setAttribute($method, $value);
        } elseif ($this->currentSectionId && $method === 'icon' && isset($this->sections[$this->currentSectionId])) {
            // Set icon on current section
            $icon = $arguments[0] ?? null;
            $this->sections[$this->currentSectionId]->setIcon(is_callable($icon) ? $icon() : $icon);
        } else {
            throw new LogicException("$method must follow item() or section()");
        }

        return $this;
    }

    public function withMetadata(array $metadata): self
    {
        $new = clone $this;
        $new->metadata = array_merge($this->metadata, $metadata);

        return $new;
    }

    public function build(): MenuContract
    {
        $menu = new Menu($this->menuId, $this->metadata);

        // Add all sections to the menu
        foreach ($this->sections as $section) {
            $menu->addSection($section);
        }

        // Add all root items if allowed
        if ($this->allowRootItems) {
            foreach ($this->rootItems as $item) {
                $menu->addItem($item);
            }
        }

        return $menu;
    }

    public static function create(string $menuId, array $metadata = [], array $options = []): self
    {
        return new self($menuId, $metadata, $options);
    }

    private function createDefaultSection(): void
    {
        $sectionId = $this->generateId('section');
        $section = new MenuSection($sectionId);

        $this->sections[$sectionId] = $section;
        $this->currentSectionId = $sectionId;
    }

    private function generateId(string $prefix): string
    {
        return "{$prefix}_{$this->menuId}_".(++$this->counter);
    }

    // Validation methods
    public function validate(): self
    {
        // Check if menu has any items (in sections or root level)
        $hasItems = false;

        // Check sections
        foreach ($this->sections as $section) {
            if ($section->hasChildren()) {
                $hasItems = true;
                break;
            }
        }

        // Check root items if allowed
        if (! $hasItems && $this->allowRootItems && $this->rootItems->isNotEmpty()) {
            $hasItems = true;
        }

        if (! $hasItems) {
            throw new LogicException("Menu '{$this->menuId}' must have at least one item");
        }

        return $this;
    }

    // Debug methods
    public function debug(): array
    {
        return [
            'menuId' => $this->menuId,
            'metadata' => $this->metadata,
            'allowRootItems' => $this->allowRootItems,
            'currentSectionId' => $this->currentSectionId,
            'currentItemId' => $this->currentItemId,
            'inSubContext' => $this->inSubContext,
            'sections' => array_map(fn ($section) => $section->toArray(), $this->sections),
            'rootItems' => array_map(fn ($item) => $item->toArray(), $this->rootItems->toArray()),
            'items' => array_map(fn ($item) => $item->toArray(), $this->items),
        ];
    }

    // Fluent interface for method chaining
    public function then(Closure $callback): self
    {
        $callback($this);

        return $this;
    }

    // Conditional building
    public function when(bool $condition, Closure $callback): self
    {
        if ($condition) {
            $callback($this);
        }

        return $this;
    }

    public function unless(bool $condition, Closure $callback): self
    {
        if (! $condition) {
            $callback($this);
        }

        return $this;
    }

    // Debug getters
    public function getCurrentSectionId(): ?string
    {
        return $this->currentSectionId;
    }

    public function getCurrentItemId(): ?string
    {
        return $this->currentItemId;
    }

    public function getInSubContext(): bool
    {
        return $this->inSubContext;
    }
}
