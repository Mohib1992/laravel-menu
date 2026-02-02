<?php

namespace Mohib\Menu\Renderers;

use Mohib\Menu\Contracts\MenuInterface as MenuContract;
use Mohib\Menu\Contracts\MenuItemInterface as MenuItemContract;
use Mohib\Menu\Contracts\MenuRendererInterface as MenuRendererContract;
use Mohib\Menu\Contracts\MenuSectionInterface as MenuSectionContract;

class MenuRenderer implements MenuRendererContract
{
    private array $config = [
        'css_classes' => [
            'menu' => 'nav-menu',
            'section' => 'nav-section',
            'section_title' => 'nav-section-title',
            'list' => 'nav-list',
            'item' => 'nav-item',
            'link' => 'nav-link',
            'active' => 'active',
            'text' => 'nav-text',
            'badge' => 'nav-badge',
            'chevron' => 'nav-chevron',
            'icon' => 'nav-icon',
            'sub_list' => 'nav-sub-list',
        ],
        'chevron_icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>',
        'max_depth' => 10,
        'escape_all' => true,
    ];

    public function __construct(array $config = [])
    {
        $this->config = array_merge_recursive($this->config, $config);
    }

    public function render(MenuContract $menu): string
    {
        if ($menu->getSections()->isEmpty() && ! $menu->hasRootItems()) {
            return '';
        }

        $html = '';
        $menuId = e($menu->getId());
        $menuClass = $this->config['css_classes']['menu'];

        $html .= '<nav id="'.$menuId.'" class="'.$menuClass.'" role="navigation" aria-label="'.$menuId.' menu">';

        // Render sections first (existing behavior)
        foreach ($menu->getSections() as $section) {
            $html .= $this->renderSection($section);
        }

        // Render root items if they exist
        if ($menu->hasRootItems()) {
            $html .= $this->renderRootItems($menu->getItems());
        }

        $html .= '</nav>';

        return $html;
    }

    public function renderSection(MenuSectionContract $section): string
    {
        if (! $section->hasChildren()) {
            return '';
        }

        $html = '';
        $sectionId = e($section->getId());
        $sectionClass = $this->config['css_classes']['section'];
        $sectionTitleClass = $this->config['css_classes']['section_title'];
        $listClass = $this->config['css_classes']['list'];

        $html .= '<div id="'.$sectionId.'" class="'.$sectionClass.'">';

        if ($section->getTitle()) {
            $icon = $this->renderIcon($section->getIcon());
            $title = e($section->getTitle());

            $html .= '<h2 class="'.$sectionTitleClass.'">';
            $html .= $icon;
            $html .= '<span>'.$title.'</span>';
            $html .= '</h2>';
        }

        $html .= '<ul class="'.$listClass.'">';

        foreach ($section->getItems() as $item) {
            $html .= $this->renderItem($item);
        }

        $html .= '</ul>';
        $html .= '</div>';

        return $html;
    }

    public function renderRootItems($items): string
    {
        if ($items->isEmpty()) {
            return '';
        }

        $html = '';
        $listClass = $this->config['css_classes']['list'];

        $html .= '<ul class="'.$listClass.' root-items">';

        foreach ($items as $item) {
            $html .= $this->renderItem($item, 1);
        }

        $html .= '</ul>';

        return $html;
    }

    public function renderItem(MenuItemContract $item, int $depth = 1): string
    {
        if ($depth > $this->config['max_depth']) {
            return '';
        }

        $attributes = $item->getAttributes();
        $hasChildren = $item->hasChildren();
        $isActive = $item->isActive();

        // Build HTML attributes
        $htmlAttrs = $this->buildHtmlAttributes($item, $hasChildren, $isActive);
        $cssClass = $this->buildCssClass($isActive);

        $html = '';
        $itemClass = $this->config['css_classes']['item'];

        $html .= '<li class="'.$itemClass.'">';

        // Build link
        $href = $item->getRoute() ? e($item->getRoute()) : '#';
        $html .= '<a href="'.$href.'" class="'.$cssClass.'"'.$htmlAttrs.'>';

        // Icon
        $html .= $this->renderIcon($item->getIcon());

        // Label
        $label = e($item->getLabel());
        $textClass = $this->config['css_classes']['text'];
        $html .= '<span class="'.$textClass.'">'.$label.'</span>';

        // Chevron for items with children
        if ($hasChildren) {
            $chevronClass = $this->config['css_classes']['chevron'];
            $chevronIcon = $this->config['chevron_icon'];
            $html .= '<span class="'.$chevronClass.'">'.$chevronIcon.'</span>';
        }

        // Badge
        $html .= $this->renderBadge($item->getBadge());

        $html .= '</a>';

        // Children
        if ($hasChildren) {
            $html .= $this->renderChildren($item, $depth);
        }

        $html .= '</li>';

        return $html;
    }

    private function renderIcon($icon): string
    {
        if (! $icon) {
            return '';
        }

        $iconClass = $this->config['css_classes']['icon'];
        $resolvedIcon = $this->resolveValue($icon) ?? '';

        if ($this->config['escape_all'] || is_string($resolvedIcon)) {
            return '<span class="'.$iconClass.'">'.e($resolvedIcon).'</span>';
        }

        return '<span class="'.$iconClass.'">'.$resolvedIcon.'</span>';
    }

    private function renderBadge($badge): string
    {
        if (! $badge) {
            return '';
        }

        $badgeClass = $this->config['css_classes']['badge'];
        $resolvedBadge = $this->resolveValue($badge);

        if (is_numeric($resolvedBadge)) {
            return '<span class="'.$badgeClass.'">'.e($resolvedBadge).'</span>';
        }

        // Always escape badge content to prevent XSS
        return '<span class="'.$badgeClass.'">'.e($resolvedBadge).'</span>';
    }

    private function renderChildren(MenuItemContract $item, int $depth): string
    {
        $subListClass = $this->config['css_classes']['sub_list'];
        $html = '<ul class="'.$subListClass.' level-'.$depth.'">';

        foreach ($item->getChildren() as $child) {
            $html .= $this->renderItem($child, $depth + 1);
        }

        $html .= '</ul>';

        return $html;
    }

    private function buildHtmlAttributes(MenuItemContract $item, bool $hasChildren, bool $isActive): string
    {
        $attributes = [];
        $itemAttrs = $item->getAttributes();

        // Remove special attributes that are handled separately
        $specialAttrs = ['icon', 'badge', 'active'];
        foreach ($specialAttrs as $attr) {
            unset($itemAttrs[$attr]);
        }

        // Add data attributes for children
        if ($hasChildren) {
            $attributes['data-toggle'] = 'sub-menu';
            $attributes['aria-expanded'] = $isActive ? 'true' : 'false';
        }

        // Add accessibility attributes
        if ($isActive) {
            $attributes['aria-current'] = 'page';
        }

        // Add custom attributes (whitelisted)
        $allowedAttrs = ['id', 'class', 'target', 'title', 'rel', 'data-*'];
        foreach ($itemAttrs as $key => $value) {
            if ($this->isAttributeAllowed($key, $allowedAttrs)) {
                $resolvedValue = $this->resolveValue($value);
                $attributes[$key] = e($resolvedValue);
            }
        }

        $html = '';
        foreach ($attributes as $key => $value) {
            if ($value !== null && $value !== '') {
                $html .= ' '.e($key).'="'.$value.'"';
            }
        }

        return $html;
    }

    private function buildCssClass(bool $isActive): string
    {
        $linkClass = $this->config['css_classes']['link'];
        $activeClass = $this->config['css_classes']['active'];

        return $linkClass.($isActive ? ' '.$activeClass : '');
    }

    private function isAttributeAllowed(string $attribute, array $allowed): bool
    {
        foreach ($allowed as $allowedAttr) {
            if ($allowedAttr === $attribute) {
                return true;
            }

            if (str_ends_with($allowedAttr, '*') && str_starts_with($attribute, substr($allowedAttr, 0, -1))) {
                return true;
            }
        }

        return false;
    }

    private function resolveValue(mixed $value): mixed
    {
        if (is_numeric($value)) {
            return $value;
        }

        if ($value instanceof \Closure) {
            return $value();
        }

        if ($value instanceof \Illuminate\Contracts\Support\Htmlable) {
            return $value->toHtml();
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        return (string) ($value ?? '');
    }

    public function setConfig(array $config): self
    {
        $this->config = array_merge_recursive($this->config, $config);

        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
