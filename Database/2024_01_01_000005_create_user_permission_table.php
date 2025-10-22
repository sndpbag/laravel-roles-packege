<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $tableName = config('dynamic-roles.table_names.user_permission', 'user_permission');
        $usersTable = (new (config('dynamic-roles.user_model')))->getTable();
        $permissionsTable = config('dynamic-roles.table_names.permissions', 'permissions');

        Schema::create($tableName, function (Blueprint $table) use ($usersTable, $permissionsTable) {
            $table->foreignId('user_id')->constrained($usersTable)->onDelete('cascade');
            $table->foreignId('permission_id')->constrained($permissionsTable)->onDelete('cascade');
            $table->primary(['user_id', 'permission_id']);
        });
    }

    public function down()
    {
        $tableName = config('dynamic-roles.table_names.user_permission', 'user_permission');
        Schema::dropIfExists($tableName);
    }
};

