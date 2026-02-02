<?php

namespace Mohib\Menu\Contracts;

use Illuminate\Support\Collection;

interface MenuSectionInterface extends MenuNodeInterface
{
    public function getTitle(): ?string;

    public function getIcon(): ?string;

    public function setTitle(?string $title): self;

    public function setIcon(?string $icon): self;

    public function getItems(): Collection;

    public function addItem(MenuItemInterface $item): self;

    public function findItem(string $id): ?MenuItemInterface;
}
