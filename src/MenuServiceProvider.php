<?php

namespace Mohib\Menu;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Mohib\Menu\Registries\MenuRegistry;
use Mohib\Menu\Renderers\MenuRenderer;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MenuService::class, function (Application $app) {
            return new MenuService(
                new MenuRegistry($app['log']),
                new MenuRenderer,
                $app['cache.store'] ?? null
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerBladeDirectives();
    }

    /**
     * Register custom Blade directives.
     */
    protected function registerBladeDirectives(): void
    {
        Blade::directive('menu', function ($expression) {
            return "<?php echo app('menu')->render({$expression}); ?>";
        });

        Blade::directive('menuHas', function ($expression) {
            return "<?php if (app('menu')->has({$expression})): ?>";
        });

        Blade::directive('endMenuHas', function () {
            return '<?php endif; ?>';
        });

        Blade::directive('menuMissing', function ($expression) {
            return "<?php if (!app('menu')->has({$expression})): ?>";
        });

        Blade::directive('endMenuMissing', function () {
            return '<?php endif; ?>';
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            MenuService::class,
        ];
    }
}
