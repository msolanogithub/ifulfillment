<?php

namespace Modules\Ifulfillment\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
// Bindings
use Modules\Ifulfillment\Repositories\Eloquent\EloquentOrderRepository;
use Modules\Ifulfillment\Repositories\Cache\CacheOrderDecorator;
use Modules\Ifulfillment\Repositories\OrderRepository;
use Modules\Ifulfillment\Models\Order;
use Modules\Ifulfillment\Repositories\Eloquent\EloquentOrderItemRepository;
use Modules\Ifulfillment\Repositories\Cache\CacheOrderItemDecorator;
use Modules\Ifulfillment\Repositories\OrderItemRepository;
use Modules\Ifulfillment\Models\OrderItem;
use Modules\Ifulfillment\Repositories\Eloquent\EloquentShipmentRepository;
use Modules\Ifulfillment\Repositories\Cache\CacheShipmentDecorator;
use Modules\Ifulfillment\Repositories\ShipmentRepository;
use Modules\Ifulfillment\Models\Shipment;
use Modules\Ifulfillment\Repositories\Eloquent\EloquentShipmentItemRepository;
use Modules\Ifulfillment\Repositories\Cache\CacheShipmentItemDecorator;
use Modules\Ifulfillment\Repositories\ShipmentItemRepository;
use Modules\Ifulfillment\Models\ShipmentItem;
// append-use-bindings





class IfulfillmentServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Ifulfillment';

    protected string $nameLower = 'ifulfillment';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->name, 'database/Migrations'));
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
        $this->registerBindings();
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        // $this->commands([]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = base_path('resources/lang/modules/' . $this->nameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->nameLower);
        } else {
            $moduleLangPath = module_path($this->name, 'resources/lang');
            $this->loadTranslationsFrom($moduleLangPath, $this->nameLower);
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $configPath = module_path($this->name, config('modules.paths.generator.config.path'));

        if (is_dir($configPath)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configPath));

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $config = str_replace($configPath.DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $config_key = str_replace([DIRECTORY_SEPARATOR, '.php'], ['.', ''], $config);
                    $segments = explode('.', $this->nameLower.'.'.$config_key);

                    // Remove duplicated adjacent segments
                    $normalized = [];
                    foreach ($segments as $segment) {
                        if (end($normalized) !== $segment) {
                            $normalized[] = $segment;
                        }
                    }

                    $key = ($config === 'config.php') ? $this->nameLower : implode('.', $normalized);

                    $this->publishes([$file->getPathname() => config_path($config)], 'config');
                    $this->merge_config_from($file->getPathname(), $key);
                }
            }
        }
    }

    /**
     * Merge config from the given path recursively.
     */
    protected function merge_config_from(string $path, string $key): void
    {
        $existing = config($key, []);
        $module_config = require $path;

        config([$key => array_replace_recursive($existing, $module_config)]);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->nameLower);
        $sourcePath = module_path($this->name, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);

        Blade::componentNamespace(config('modules.namespace').'\\' . $this->name . '\\View\\Components', $this->nameLower);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->nameLower)) {
                $paths[] = $path.'/modules/'.$this->nameLower;
            }
        }

        return $paths;
    }

    private function registerBindings(): void
    {
        $this->app->bind(OrderRepository::class, function () {
    $repository = new EloquentOrderRepository(new Order());

    return config('app.cache')
        ? new CacheOrderDecorator($repository)
        : $repository;
});
$this->app->bind(OrderItemRepository::class, function () {
    $repository = new EloquentOrderItemRepository(new OrderItem());

    return config('app.cache')
        ? new CacheOrderItemDecorator($repository)
        : $repository;
});
$this->app->bind(ShipmentRepository::class, function () {
    $repository = new EloquentShipmentRepository(new Shipment());

    return config('app.cache')
        ? new CacheShipmentDecorator($repository)
        : $repository;
});
$this->app->bind(ShipmentItemRepository::class, function () {
    $repository = new EloquentShipmentItemRepository(new ShipmentItem());

    return config('app.cache')
        ? new CacheShipmentItemDecorator($repository)
        : $repository;
});
// append-bindings




    }
}
