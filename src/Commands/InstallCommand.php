<?php

namespace Sndpbag\DynamicRoles\Commands;

use Illuminate\Console\Command;
use Sndpbag\DynamicRoles\Models\Role;
use Illuminate\Support\Facades\Hash;

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
            // '--force' => true, // <-- এই লাইনটি সরানো হয়েছে। এটি ইউজারদের করা পরিবর্তন ওভাররাইট করছিল।
        ]);

        // Publish views
        $this->call('vendor:publish', [
            '--tag' => 'dynamic-roles-views',
            // ভিউ থেকেও '--force' সরানো ভালো অভ্যাস, যাতে ইউজারদের কাস্টমাইজেশন সেভ থাকে।
        ]);

        // Run migrations
        $this->info('Running migrations...');
        $this->call('migrate');

        // Create default roles
        $this->info('Creating default roles...');
        $this->createDefaultRoles();
        $this->info('Default roles created: Super Admin, Admin, User');

        
        // --- ট্রেইট চেকিং -এর উন্নত কোড ---
        
        $userModelClass = config('dynamic-roles.user_model');
        
        // --- ধাপ ১: প্রথমে চেক করুন ক্লাসটির অস্তিত্ব আছে কিনা ---
        if (!class_exists($userModelClass)) {
            $this->error("Error: The User model class '{$userModelClass}' (defined in config/dynamic-roles.php) was not found.");
            $this->info("Please update the 'user_model' in your 'config/dynamic-roles.php' file to point to your correct User model.");
            $this->info("After updating the config, you may need to manually add the 'HasRolesAndPermissions' trait and assign the 'super-admin' role via tinker.");

        } else {
            // --- ধাপ ২: ক্লাসটির অস্তিত্ব আছে, এখন ট্রেইট চেক করুন ---
            $traits = class_uses_recursive($userModelClass);
            $myTrait = \Sndpbag\DynamicRoles\Traits\HasRolesAndPermissions::class; // সম্পূর্ণ পাথ
            
            if (!in_array($myTrait, $traits)) {
                // যদি ট্রেইট না থাকে, তাহলে এরর দিন
                $this->error("Error: Your User model ({$userModelClass}) does not use the HasRolesAndPermissions trait.");
                $this->info("Please add the 'use Sndpbag\DynamicRoles\Traits\HasRolesAndPermissions;' trait to your {$userModelClass} file first.");
                $this->info("Skipping Super Admin creation. After adding the trait, assign the role manually via tinker.");
            
            } else {
                
                // --- ধাপ ৩: ক্লাস এবং ট্রেইট দুটোই আছে, এখন অ্যাডমিন তৈরি করুন ---
                if ($this->confirm('Do you want to create or assign a Super Admin user now?', true)) {
                    $superAdminRoleSlug = config('dynamic-roles.super_admin_role', 'super-admin');
                    $role = Role::where('slug', $superAdminRoleSlug)->first();

                    if (!$role) {
                        $this->error("Error: Could not find the '{$superAdminRoleSlug}' role in the database.");
                        return;
                    }

                    $choice = $this->choice(
                        'What do you want to do?',
                        ['Assign role to an existing user', 'Create a new Super Admin user'],
                        0 
                    );

                    if ($choice == 'Assign role to an existing user') {
                        $this->assignRoleToExistingUser($userModelClass, $role);
                    } else {
                        $this->createNewSuperAdmin($userModelClass, $role);
                    }
                } else {
                    $this->info('Skipping Super Admin assignment. You can assign it manually later.');
                }
            }
        }
        // --- চেকিং কোড শেষ ---


        // Sync routes
        $this->info('Syncing routes to permissions...');
        $this->call('dynamic-roles:sync-routes');

        $this->info('✅ Dynamic Roles and Permissions installed successfully!');
        $this->newLine();
        $this->info('Next steps:');
        $this->info("1. Ensure your User model ({$userModelClass}) uses the HasRolesAndPermissions trait.");
        $this->info('2. Visit /admin/roles-permissions to manage roles and permissions');
    }

    protected function assignRoleToExistingUser($userModelClass, $role)
    {
        $email = $this->ask('Enter the email of the existing user:');
        try {
            $user = $userModelClass::where('email', $email)->first();
            if ($user) {
                $user->assignRole($role);
                $this->info("✅ Super Admin role assigned to {$user->email} successfully!");
            } else {
                $this->error("Error: No user found with email '{$email}'.");
            }
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
        }
    }

    protected function createNewSuperAdmin($userModelClass, $role)
    {
        $this->info("Let's create the Super Admin user...");
        
        $name = $this->ask('Enter the name:');
        $email = $this->ask('Enter the email:');
        $password = $this->secret('Enter a password (will be hidden):');

        if (empty($name) || empty($email) || empty($password)) {
            $this->error('All fields are required. Aborting user creation.');
            return;
        }

        if ($userModelClass::where('email', $email)->exists()) {
            $this->error("Error: A user with email '{$email}' already exists. Please use the 'assign role' option instead.");
            return;
        }

        try {
            $user = $userModelClass::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(), 
            ]);

            $user->assignRole($role);
            $this->info("✅ Super Admin user '{$email}' created and role assigned successfully!");

        } catch (\Exception $e) {
            $this->error('An error occurred while creating user: ' . $e->getMessage());
            $this->info('Please ensure your database is connected and `users` table has name, email, password fields.');
        }
    }

    protected function createDefaultRoles()
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => config('dynamic-roles.super_admin_role', 'super-admin'),
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
    }
}

