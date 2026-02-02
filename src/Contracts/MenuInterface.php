<?php

namespace Mohib\Menu\Contracts;

use Illuminate\Support\Collection;

interface MenuInterface extends MenuNodeInterface
{
    public function getSections(): Collection;

    public function addSection(MenuSectionInterface $section): self;

    public function findSection(string $id): ?MenuSectionInterface;

    public function getMetadata(): array;

    public function withMetadata(array $metadata): self;

    // Root-level items support
    public function getItems(): Collection;

    public function addItem(MenuItemInterface $item): self;

    public function removeItem(string $itemId): self;

    public function findRootItem(string $id): ?MenuItemInterface;

    public function hasRootItems(): bool;
}
