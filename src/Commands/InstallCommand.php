<?php

namespace Sndpbag\DynamicRoles\Commands;

use Illuminate\Console\Command;
use sndpbag\DynamicRoles\Models\Role;

class InstallCommand extends Command
{
    protected $signature = 'dynamic-roles:install';
    protected $description = 'Install Dynamic Roles and Permissions package';

    public function handle()
    {
        $this->info('Installing Dynamic Roles and Permissions...');

        // Publish config
        $this->call('vendor:publish', [
            '--tag' => 'dynamic-roles-config',
            '--force' => true,
        ]);

        // Publish views
        $this->call('vendor:publish', [
            '--tag' => 'dynamic-roles-views',
        ]);

        // Run migrations
        $this->info('Running migrations...');
        $this->call('migrate');

        // Create default roles
        $this->info('Creating default roles...');
        $this->createDefaultRoles();

        // Sync routes
        $this->info('Syncing routes to permissions...');
        $this->call('dynamic-roles:sync-routes');

        $this->info('✅ Dynamic Roles and Permissions installed successfully!');
        $this->newLine();
        $this->info('Next steps:');
        $this->info('1. Add HasRolesAndPermissions trait to your User model');
        $this->info('2. Visit /admin/roles-permissions to manage roles and permissions');
        $this->info('3. Assign roles to users');
    }

    protected function createDefaultRoles()
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Has access to everything',
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Has access to admin panel',
            ],
            [
                'name' => 'User',
                'slug' => 'user',
                'description' => 'Regular user',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }

        $this->info('Default roles created: Super Admin, Admin, User');
    }
}