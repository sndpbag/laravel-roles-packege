<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $tableName = config('dynamic-roles.table_names.roles', 'roles');
        
        Schema::create($tableName, function (Blueprint $table) use ($tableName) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->foreignId('parent_id')->nullable()->constrained($tableName)->onDelete('set null');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(config('dynamic-roles.table_names.roles', 'roles'));
    }
};
