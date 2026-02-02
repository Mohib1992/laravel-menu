<?php

namespace Mohib\Menu\Contracts;

interface MenuRegistryInterface
{
    public function get(string $id): ?MenuInterface;

    public function has(string $id): bool;

    public function register(MenuInterface $menu): self;

    public function remove(string $id): self;

    public function all(): array;

    public function clear(): self;
}
