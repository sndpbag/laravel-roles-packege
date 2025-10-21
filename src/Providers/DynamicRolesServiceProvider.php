<?php

namespace sndpbag\DynamicRoles\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use sndpbag\DynamicRoles\Middleware\RoleMiddleware;
use sndpbag\DynamicRoles\Middleware\PermissionMiddleware;
use sndpbag\DynamicRoles\Commands\InstallCommand;
use sndpbag\DynamicRoles\Commands\SyncRoutesCommand;

class DynamicRolesServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/dynamic-roles.php', 'dynamic-roles');
    }

    public function boot(Router $router)
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../Views', 'dynamic-roles');

        // Publish config
        $this->publishes([
            __DIR__ . '/../Config/dynamic-roles.php' => config_path('dynamic-roles.php'),
        ], 'dynamic-roles-config');

        // Publish views
        $this->publishes([
            __DIR__ . '/../Views' => resource_path('views/vendor/dynamic-roles'),
        ], 'dynamic-roles-views');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../Database/Migrations' => database_path('migrations'),
        ], 'dynamic-roles-migrations');

        // Register middleware
        $router->aliasMiddleware('role', RoleMiddleware::class);
        $router->aliasMiddleware('permission', PermissionMiddleware::class);

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                SyncRoutesCommand::class,
            ]);
        }
    }
}