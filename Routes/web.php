<?php

use Illuminate\Support\Facades\Route;
use sndpbag\DynamicRoles\Controllers\RoleController;
use sndpbag\DynamicRoles\Controllers\PermissionController;
use sndpbag\DynamicRoles\Controllers\UserRoleController;

Route::prefix(config('dynamic-roles.route_prefix', 'admin/roles-permissions'))
    ->middleware(config('dynamic-roles.middleware', ['web', 'auth']))
    ->name('dynamic-roles.')
    ->group(function () {
        
        // Roles
        Route::resource('roles', RoleController::class);
        Route::get('roles/{role}/permissions', [RoleController::class, 'permissions'])
            ->name('roles.permissions');
        Route::put('roles/{role}/permissions', [RoleController::class, 'updatePermissions'])
            ->name('roles.permissions.update');

        // Permissions
        Route::resource('permissions', PermissionController::class);
        Route::post('permissions/sync', [PermissionController::class, 'sync'])
            ->name('permissions.sync');

        // User Roles
        Route::get('users', [UserRoleController::class, 'index'])
            ->name('users.index');
        Route::post('users/assign-role', [UserRoleController::class, 'assignRole'])
            ->name('users.assign-role');
        Route::delete('users/remove-role', [UserRoleController::class, 'removeRole'])
            ->name('users.remove-role');
        Route::put('users/sync-roles', [UserRoleController::class, 'syncRoles'])
            ->name('users.sync-roles');
    });