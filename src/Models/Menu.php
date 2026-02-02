<?php

namespace Mohib\Menu\Models;

use Mohib\Menu\Contracts\MenuInterface as MenuContract;
use Mohib\Menu\Contracts\MenuItemInterface as MenuItemContract;
use Mohib\Menu\Contracts\MenuNodeInterface;
use Mohib\Menu\Contracts\MenuSectionInterface as MenuSectionContract;
use Illuminate\Support\Collection;

class Menu implements MenuContract
{
    private string $id;

    private Collection $sections;

    private Collection $items;

    private array $metadata = [];

    private ?string $version = null;

    public function __construct(string $id, array $metadata = [])
    {
        $this->validateId($id);

        $this->id = $id;
        $this->sections = new Collection;
        $this->items = new Collection;
        $this->metadata = $metadata;
        $this->version = $this->generateVersion();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function getChildren(): Collection
    {
        return $this->sections;
    }

    public function hasChildren(): bool
    {
        return $this->sections->isNotEmpty();
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function addSection(MenuSectionContract $section): self
    {
        if ($this->findSection($section->getId())) {
            throw new \InvalidArgumentException("Menu section '{$section->getId()}' already exists in menu '{$this->id}'");
        }

        $this->sections->push($section);
        $this->updateVersion();

        return $this;
    }

    public function removeSection(string $sectionId): self
    {
        $this->sections = $this->sections->reject(fn ($section) => $section->getId() === $sectionId);
        $this->updateVersion();

        return $this;
    }

    public function findSection(string $id): ?MenuSectionContract
    {
        return $this->sections->first(fn ($section) => $section->getId() === $id);
    }

    public function findItem(string $id): ?MenuNodeInterface
    {
        // First search in root-level items
        $rootItem = $this->findRootItem($id);
        if ($rootItem) {
            return $rootItem;
        }

        // Then search in sections
        foreach ($this->sections as $section) {
            $item = $section->findItem($id);
            if ($item) {
                return $item;
            }

            // Search in nested items
            $nestedItem = $this->findNestedItem($item, $id);
            if ($nestedItem) {
                return $nestedItem;
            }
        }

        return null;
    }

    public function addChild(MenuNodeInterface $child): self
    {
        if ($child instanceof MenuSectionContract) {
            return $this->addSection($child);
        } elseif ($child instanceof MenuItemContract) {
            return $this->addItem($child);
        } else {
            throw new \InvalidArgumentException('Menu can only have MenuSection or MenuItem children');
        }
    }

    public function findChild(string $id): ?MenuNodeInterface
    {
        return $this->findSection($id);
    }

    public function withMetadata(array $metadata): self
    {
        $new = clone $this;
        $new->metadata = array_merge($this->metadata, $metadata);
        $new->updateVersion();

        return $new;
    }

    // Root-level items methods
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(MenuItemContract $item): self
    {
        if ($this->findRootItem($item->getId())) {
            throw new \InvalidArgumentException("Menu item '{$item->getId()}' already exists in menu '{$this->id}'");
        }

        $this->items->push($item);
        $this->updateVersion();

        return $this;
    }

    public function removeItem(string $itemId): self
    {
        $this->items = $this->items->reject(fn ($item) => $item->getId() === $itemId);
        $this->updateVersion();

        return $this;
    }

    public function findRootItem(string $id): ?MenuItemContract
    {
        return $this->items->first(fn ($item) => $item->getId() === $id);
    }

    public function hasRootItems(): bool
    {
        return $this->items->isNotEmpty();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'sections' => $this->sections->map(fn ($section) => $section->toArray())->toArray(),
            'items' => $this->items->map(fn ($item) => $item->toArray())->toArray(),
            'metadata' => $this->metadata,
            'version' => $this->version,
            'hasChildren' => $this->hasChildren(),
            'hasRootItems' => $this->hasRootItems(),
            'itemCount' => $this->getItemCount(),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toCacheKey(): string
    {
        return "menu:{$this->id}:{$this->version}";
    }

    public function getItemCount(): int
    {
        $count = 0;

        // Count items in sections
        foreach ($this->sections as $section) {
            $count += $this->countItems($section);
        }

        // Count root-level items and their children
        foreach ($this->items as $item) {
            $count++;
            $count += $this->countNestedItems($item);
        }

        return $count;
    }

    public function isEmpty(): bool
    {
        return $this->getItemCount() === 0;
    }

    public function getActiveItems(): Collection
    {
        $activeItems = new Collection;

        // Collect from root-level items
        $this->collectActiveItems($this->items, $activeItems);

        // Collect from section items
        foreach ($this->sections as $section) {
            $this->collectActiveItems($section->getItems(), $activeItems);
        }

        return $activeItems;
    }

    private function findNestedItem(?MenuNodeInterface $item, string $id): ?MenuNodeInterface
    {
        if (! $item || ! $item->hasChildren()) {
            return null;
        }

        foreach ($item->getChildren() as $child) {
            if ($child->getId() === $id) {
                return $child;
            }

            $nestedItem = $this->findNestedItem($child, $id);
            if ($nestedItem) {
                return $nestedItem;
            }
        }

        return null;
    }

    private function countItems(MenuSectionContract $section): int
    {
        $count = $section->getItems()->count();

        foreach ($section->getItems() as $item) {
            $count += $this->countNestedItems($item);
        }

        return $count;
    }

    private function countNestedItems(MenuNodeInterface $item): int
    {
        $count = 0;

        if ($item->hasChildren()) {
            foreach ($item->getChildren() as $child) {
                $count++;
                $count += $this->countNestedItems($child);
            }
        }

        return $count;
    }

    private function collectActiveItems(Collection $items, Collection $activeItems): void
    {
        foreach ($items as $item) {
            if ($item->isActive()) {
                $activeItems->push($item);
            }

            if ($item->hasChildren()) {
                $this->collectActiveItems($item->getChildren(), $activeItems);
            }
        }
    }

    private function validateId(string $id): void
    {
        if (empty(trim($id))) {
            throw new \InvalidArgumentException('Menu ID cannot be empty');
        }

        if (strlen($id) > 255) {
            throw new \InvalidArgumentException('Menu ID cannot exceed 255 characters');
        }

        if (! preg_match('/^[a-zA-Z0-9_-]+$/', $id)) {
            throw new \InvalidArgumentException('Menu ID can only contain alphanumeric characters, underscores, and hyphens');
        }
    }

    private function generateVersion(): string
    {
        return md5(uniqid($this->id, true).microtime(true));
    }

    private function updateVersion(): void
    {
        $this->version = $this->generateVersion();
    }
}
