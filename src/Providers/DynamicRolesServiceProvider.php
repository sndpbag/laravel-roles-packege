<?php

namespace sndpbag\DynamicRoles\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
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
        $this->loadMigrationsFrom(__DIR__ . '/../../Database');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../../Routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../../resources/Views', 'dynamic-roles');

        // Publish config
        $this->publishes([
            __DIR__ . '/../Config/dynamic-roles.php' => config_path('dynamic-roles.php'),
        ], 'dynamic-roles-config');

        // Publish views
        $this->publishes([
            __DIR__ . '/../../resources/Views' => resource_path('views/vendor/dynamic-roles'),
        ], 'dynamic-roles-views');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../../Database' => database_path('migrations'),
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

    /**
     * Register the custom blade directives.
     * <-- 3. এই সম্পূর্ণ নতুন মেথডটি ক্লাসের শেষে যোগ করুন -->
     */
    protected function registerBladeDirectives()
    {
        // @hasRole('role-slug') OR @hasRole(['role1', 'role2'])
        Blade::directive('hasRole', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->hasRole({$expression})): ?>";
        });
        Blade::directive('elsehasRole', function () {
            return '<?php else: ?>';
        });
        Blade::directive('endhasRole', function () {
            return '<?php endif; ?>';
        });

        // @hasAnyRole(['role1', 'role2'])
        Blade::directive('hasAnyRole', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->hasAnyRole({$expression})): ?>";
        });
        Blade::directive('elsehasAnyRole', function () {
            return '<?php else: ?>';
        });
        Blade::directive('endhasAnyRole', function () {
            return '<?php endif; ?>';
        });

        // @hasAllRoles(['role1', 'role2'])
        Blade::directive('hasAllRoles', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->hasAllRoles({$expression})): ?>";
        });
        Blade::directive('elsehasAllRoles', function () {
            return '<?php else: ?>';
        });
        Blade::directive('endhasAllRoles', function () {
            return '<?php endif; ?>';
        });

        // @hasPermission('perm-slug') OR @hasPermission(['perm1', 'perm2'])
        Blade::directive('hasPermission', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->hasPermission({$expression})): ?>";
        });
        Blade::directive('elsehasPermission', function () {
            return '<?php else: ?>';
        });
        Blade::directive('endhasPermission', function () {
            return '<?php endif; ?>';
        });

        // @hasAnyPermission(['perm1', 'perm2'])
        Blade::directive('hasAnyPermission', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->hasAnyPermission({$expression})): ?>";
        });
        Blade::directive('elsehasAnyPermission', function () {
            return '<?php else: ?>';
        });
        Blade::directive('endhasAnyPermission', function () {
            return '<?php endif; ?>';
        });

        // @hasAllPermissions(['perm1', 'perm2'])
        Blade::directive('hasAllPermissions', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->hasAllPermissions({$expression})): ?>";
        });
        Blade::directive('elsehasAllPermissions', function () {
            return '<?php else: ?>';
        });
        Blade::directive('endhasAllPermissions', function () {
            return '<?php endif; ?>';
        });
    }
}