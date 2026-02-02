<?php

namespace Mohib\Menu\Models;

use Mohib\Menu\Contracts\MenuItemInterface as MenuItemContract;
use Mohib\Menu\Contracts\MenuNodeInterface;
use Mohib\Menu\Contracts\MenuSectionInterface as MenuSectionContract;
use Illuminate\Support\Collection;

class MenuSection implements MenuSectionContract
{
    private string $id;

    private ?string $title;

    private ?string $icon;

    private Collection $items;

    public function __construct(string $id, ?string $title = null, ?string $icon = null)
    {
        $this->validateId($id);

        $this->id = $id;
        $this->title = $title;
        $this->icon = $icon;
        $this->items = new Collection;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function getChildren(): Collection
    {
        return $this->items;
    }

    public function hasChildren(): bool
    {
        return $this->items->isNotEmpty();
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function addItem(MenuItemContract $item): self
    {
        if ($this->findItem($item->getId())) {
            throw new \InvalidArgumentException("Menu item '{$item->getId()}' already exists in section '{$this->id}'");
        }

        $this->items->push($item);

        return $this;
    }

    public function removeItem(string $itemId): self
    {
        $this->items = $this->items->reject(fn ($item) => $item->getId() === $itemId);

        return $this;
    }

    public function findItem(string $id): ?MenuItemContract
    {
        return $this->items->first(fn ($item) => $item->getId() === $id);
    }

    public function addChild(MenuNodeInterface $child): self
    {
        if (! $child instanceof MenuItemContract) {
            throw new \InvalidArgumentException('MenuSection can only have MenuItem children');
        }

        return $this->addItem($child);
    }

    public function findChild(string $id): ?MenuNodeInterface
    {
        return $this->findItem($id);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'icon' => $this->icon,
            'items' => $this->items->map(fn ($item) => $item->toArray())->toArray(),
            'hasChildren' => $this->hasChildren(),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    private function validateId(string $id): void
    {
        if (empty(trim($id))) {
            throw new \InvalidArgumentException('Menu section ID cannot be empty');
        }

        if (strlen($id) > 255) {
            throw new \InvalidArgumentException('Menu section ID cannot exceed 255 characters');
        }
    }
}
