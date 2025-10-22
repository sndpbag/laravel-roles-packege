<?php

namespace Sndpbag\DynamicRoles\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Sndpbag\DynamicRoles\Models\Permission;
use Illuminate\Support\Str;

class SyncRoutesCommand extends Command
{
    protected $signature = 'dynamic-roles:sync-routes';
    protected $description = 'Sync all routes to permissions table';

    public function handle()
    {
        $this->info('Syncing routes to permissions...');

        $routes = Route::getRoutes();
        $excludePatterns = config('dynamic-roles.exclude_routes', []);
        $synced = 0;
        $skipped = 0;

        foreach ($routes as $route) {
            $routeName = $route->getName();
            
            if (!$routeName || $this->shouldExcludeRoute($routeName, $excludePatterns)) {
                $skipped++;
                continue;
            }

            $group = $this->getGroupFromRouteName($routeName);
            $name = $this->generatePermissionName($routeName);

            Permission::updateOrCreate(
                ['route_name' => $routeName],
                [
                    'name' => $name,
                    // 'slug' => Str::slug($routeName),
                    'slug' => $routeName,
                    'group' => $group,
                    'http_method' => implode('|', $route->methods()),
                    'http_uri' => $route->uri(),
                    'is_active' => true,
                ]
            );

            $synced++;
        }

        $this->info("✅ Synced {$synced} routes to permissions");
        $this->info("⏭️  Skipped {$skipped} routes");
    }

    protected function shouldExcludeRoute($routeName, $patterns)
    {
        foreach ($patterns as $pattern) {
            if (Str::is($pattern, $routeName)) {
                return true;
            }
        }
        return false;
    }

    protected function getGroupFromRouteName($routeName)
    {
        $parts = explode('.', $routeName);
        
        if (count($parts) > 0) {
            $group = $parts[0];
            $groups = config('dynamic-roles.permission_groups', []);
            
            return $groups[$group] ?? ucfirst($group) . ' Management';
        }

        return 'Other';
    }

    protected function generatePermissionName($routeName)
    {
        return Str::of($routeName)
            ->replace('.', ' ')
            ->replace('-', ' ')
            ->title()
            ->toString();
    }
}