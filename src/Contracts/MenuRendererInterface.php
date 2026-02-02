<?php

namespace Mohib\Menu\Contracts;

interface MenuRendererInterface
{
    public function render(MenuInterface $menu): string;

    public function renderSection(MenuSectionInterface $section): string;

    public function renderItem(MenuItemInterface $item, int $depth = 1): string;
}
