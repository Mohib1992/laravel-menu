# mohib/menu

A flexible, secure menu system for Laravel applications with fluent builder, unlimited nesting, and XSS protection.

## Features

- ✅ **Fluent Interface**: Clean method chaining for menu building
- ✅ **Secure by Default**: XSS protection and HTML attribute whitelisting  
- ✅ **Flexible Nesting**: Unlimited levels with `sub()` function
- ✅ **Root Items**: Optional root-level items without sections
- ✅ **Mixed Structure**: Combine sections and root items
- ✅ **Type Safe**: Full interface-based architecture
- ✅ **Caching**: Built-in caching for performance
- ✅ **Accessible**: ARIA attributes and semantic HTML
- ✅ **Laravel Native**: Blade directives and service container integration

## Quick Installation

```bash
composer require mohib/menu
```

## Quick Start

### 1. Publish Configuration

```bash
php artisan vendor:publish --provider="Mohib\\Menu\\MenuServiceProvider" --tag="config"
```

### 2. Create Simple Menu

```php
use Mohib\Menu\Facades\Menu;

// In your controller or service provider
Menu::make('navigation', function ($builder) {
    $builder
        ->item('Home', '/')
        ->item('About', '/about')
        ->item('Contact', '/contact');
});
```

### 3. Render in Blade

```blade
@menu('navigation')
```

### 4. Conditional Rendering

```blade
@menuHas('navigation')
    @menu('navigation')
@else
    <p>Menu not available</p>
@endMenuHas
```

## Usage Examples

### Basic Navigation

```php
Menu::make('main-nav', function ($builder) {
    $builder
        ->item('Home', '/')
            ->active(request()->is('/'))
            ->icon('home')
        ->item('Products', '/products')
            ->badge('NEW')
        ->item('About', '/about')
        ->item('Contact', '/contact');
});
```

### Nested Menu with sub()

```php
Menu::make('admin-menu', function ($builder) {
    $builder
        ->item('Users', '/users')
        ->sub(function ($builder) {
            $builder
                ->item('All Users', '/users/all')
                ->item('Create User', '/users/create');
        })
        ->item('Settings', '/settings')
            ->icon('cog');
}, [], ['allowRootItems' => true]);
```

### Root-Level Items

```php
Menu::make('simple-nav', function ($builder) {
    $builder
        ->item('Dashboard', '/')
        ->item('Products', '/products')
        ->item('About', '/about')
        ->item('Contact', '/contact');
}, [], ['allowRootItems' => true]);
```

### Mixed Structure

```php
Menu::make('complex-nav', function ($builder) {
    $builder
        ->item('Home', '/')                    // Root Level 1
        ->section('Products')                     // Section
        ->item('Electronics', '/electronics')     // Section Level 1
        ->sub(function ($builder) {             // Section Level 2
            $builder
                ->item('Phones', '/electronics/phones')
                ->item('Laptops', '/electronics/laptops');
        })
        ->item('Books', '/books')               // Section Level 1
        ->item('Settings', '/settings');         // Root Level 1
}, [], ['allowRootItems' => true]);
```

## API Reference

### MenuBuilder Methods

#### Core Methods
- `item(string $label, ?string $route = null): self`
- `sub(Closure $callback): self`
- `section(string $title, ?string $icon = null): self`

#### Attribute Methods
- `icon(mixed $icon): self`
- `badge(mixed $badge): self`
- `active(bool $active = true): self`
- `class(string $className): self`
- `id(string $elementId): self`
- `target(string $target): self`
- `title(string $title): self`

#### Utility Methods
- `when(bool $condition, Closure $callback): self`
- `unless(bool $condition, Closure $callback): self`
- `then(Closure $callback): self`
- `withMetadata(array $metadata): self`
- `validate(): self`
- `build(): MenuInterface`

### Facade Methods

- `make(string $name, Closure $callback, array $metadata = [])`: Create and register menu
- `create(string $name, array $metadata = [], array $options = [])`: Create menu builder
- `build(string $name, array $metadata = [])`: Build menu without registration
- `has(string $name)`: Check if menu exists
- `get(string $name)`: Get registered menu
- `render(string $name, array $config = [])`: Render menu to HTML
- `register(MenuInterface $menu)`: Manually register menu
- `clear()`: Clear all menus
- `forget(string $name)`: Remove specific menu
- `flush()`: Clear and refresh all menus

## Configuration

Publish the configuration file to customize menu behavior:

```bash
php artisan vendor:publish --provider="Mohib\\Menu\\MenuServiceProvider" --tag="config"
```

### Available Options

```php
// config/menu.php
return [
    'css_classes' => [
        'menu' => 'nav-menu',
        'section' => 'nav-section', 
        'item' => 'nav-item',
        'link' => 'nav-link',
        'active' => 'active',
    ],
    'security' => [
        'escape_all' => true,
        'allowed_attributes' => ['id', 'class', 'data-*'],
        'allowed_protocols' => ['http', 'https', 'mailto', 'tel'],
    ],
    'caching' => [
        'enabled' => true,
        'ttl' => 3600,
        'key_prefix' => 'menu',
    ],
    'accessibility' => [
        'aria_labels' => true,
        'semantic_html' => true,
        'keyboard_nav' => true,
    ],
];
```

## Security Features

- **XSS Protection**: All output is HTML escaped by default
- **Attribute Filtering**: Only whitelisted HTML attributes allowed
- **URL Validation**: Protocol checking and sanitization
- **Input Validation**: Comprehensive validation in menu models

## Compatibility

- **PHP**: ^8.0
- **Laravel**: ^9.0|^10.0|^11.0|^12.0
- **Tested**: Laravel 9.x, 10.x, 11.x, 12.x

## License

MIT License - see LICENSE file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.