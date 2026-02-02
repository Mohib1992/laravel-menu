<?php

namespace Mohib\Menu\Contracts;

use Illuminate\Support\Collection;
use JsonSerializable;

interface MenuNodeInterface extends JsonSerializable
{
    public function getId(): string;

    public function getChildren(): Collection;

    public function addChild(MenuNodeInterface $child): self;

    public function toArray(): array;

    public function hasChildren(): bool;

    public function findChild(string $id): ?MenuNodeInterface;
}
