<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create(config('dynamic-roles.table_names.role_permission', 'role_permission'), function (Blueprint $table) {
            $table->foreignId('role_id')->constrained(config('dynamic-roles.table_names.roles', 'roles'))->onDelete('cascade');
            $table->foreignId('permission_id')->constrained(config('dynamic-roles.table_names.permissions', 'permissions'))->onDelete('cascade');
            $table->primary(['role_id', 'permission_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists(config('dynamic-roles.table_names.role_permission', 'role_permission'));
    }
};