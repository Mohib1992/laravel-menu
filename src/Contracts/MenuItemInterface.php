<?php

namespace Mohib\Menu\Contracts;

interface MenuItemInterface extends MenuNodeInterface
{
    public function getLabel(): string;

    public function getRoute(): ?string;

    public function getAttributes(): array;

    public function getAttribute(string $key, mixed $default = null): mixed;

    public function getIcon(): mixed;

    public function getBadge(): mixed;

    public function isActive(): bool;

    public function getParent(): ?MenuItemInterface;

    public function setLabel(string $label): self;

    public function setRoute(?string $route): self;

    public function setAttribute(string $key, mixed $value): self;

    public function setAttributes(array $attributes): self;

    public function setIcon(mixed $icon): self;

    public function setBadge(mixed $badge): self;

    public function setActive(bool $active): self;

    public function setParent(?MenuItemInterface $parent): self;

    public function withAttributes(array $attributes): self;

    public function withChild(MenuItemInterface $child): self;

    public function resolveValue(mixed $value): mixed;
}
