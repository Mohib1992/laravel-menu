<?php

namespace Mohib\Menu\Models;

use Mohib\Menu\Contracts\MenuItemInterface as MenuItemContract;
use Mohib\Menu\Contracts\MenuNodeInterface;
use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

class MenuItem implements MenuItemContract
{
    private string $id;

    private string $label;

    private ?string $route;

    private array $attributes = [];

    private Collection $children;

    private ?MenuItemContract $parent = null;

    private const ALLOWED_ATTRIBUTES = [
        'icon', 'badge', 'active', 'class', 'id', 'target', 'title', 'rel',
    ];

    public function __construct(string $id, string $label, ?string $route = null)
    {
        $this->validateId($id);
        $this->validateLabel($label);

        $this->id = $id;
        $this->label = $label;
        $this->route = $route;
        $this->children = new Collection;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function getIcon(): mixed
    {
        return $this->getAttribute('icon');
    }

    public function getBadge(): mixed
    {
        return $this->getAttribute('badge');
    }

    public function isActive(): bool
    {
        return (bool) $this->getAttribute('active', false);
    }

    public function getParent(): ?MenuItemContract
    {
        return $this->parent;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function hasChildren(): bool
    {
        return $this->children->isNotEmpty();
    }

    public function setLabel(string $label): self
    {
        $this->validateLabel($label);
        $this->label = $label;

        return $this;
    }

    public function setRoute(?string $route): self
    {
        $this->route = $route;

        return $this;
    }

    public function setAttribute(string $key, mixed $value): self
    {
        $this->validateAttribute($key);
        $this->attributes[$key] = $value;

        return $this;
    }

    public function setAttributes(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->validateAttribute($key);
        }
        $this->attributes = $attributes;

        return $this;
    }

    public function setIcon(mixed $icon): self
    {
        return $this->setAttribute('icon', $icon);
    }

    public function setBadge(mixed $badge): self
    {
        return $this->setAttribute('badge', $badge);
    }

    public function setActive(bool $active): self
    {
        return $this->setAttribute('active', $active);
    }

    public function setParent(?MenuItemContract $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function addChild(MenuNodeInterface $child): self
    {
        if (! $child instanceof MenuItemContract) {
            throw new \InvalidArgumentException('Child must be a MenuItem instance');
        }

        $child->setParent($this);
        $this->children->push($child);

        return $this;
    }

    public function findChild(string $id): ?MenuItemContract
    {
        return $this->children->first(fn ($child) => $child->getId() === $id);
    }

    public function withAttributes(array $attributes): self
    {
        $new = clone $this;

        return $new->setAttributes($attributes);
    }

    public function withChild(MenuItemContract $child): self
    {
        $new = clone $this;

        return $new->addChild($child);
    }

    public function resolveValue(mixed $value): mixed
    {
        if (is_numeric($value)) {
            return $value;
        }

        if ($value instanceof Closure) {
            return $value($this);
        }

        if ($value instanceof Htmlable) {
            return $value->toHtml();
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        return (string) ($value ?? '');
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'route' => $this->route,
            'attributes' => $this->attributes,
            'children' => $this->children->map(fn ($child) => $child->toArray())->toArray(),
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
            throw new \InvalidArgumentException('Menu item ID cannot be empty');
        }

        if (strlen($id) > 255) {
            throw new \InvalidArgumentException('Menu item ID cannot exceed 255 characters');
        }
    }

    private function validateLabel(string $label): void
    {
        if (empty(trim($label))) {
            throw new \InvalidArgumentException('Menu item label cannot be empty');
        }
    }

    private function validateAttribute(string $key): void
    {
        if (! in_array($key, self::ALLOWED_ATTRIBUTES)) {
            throw new \InvalidArgumentException(
                "Attribute '{$key}' is not allowed. Allowed attributes: ".
                implode(', ', self::ALLOWED_ATTRIBUTES)
            );
        }
    }
}
