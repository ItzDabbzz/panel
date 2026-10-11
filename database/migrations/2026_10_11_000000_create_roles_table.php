<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 191)->unique();
            $table->string('description', 191)->nullable();
            $table->boolean('root')->default(false);
            $table->json('permissions');
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->unsignedInteger('role_id');
            $table->unsignedInteger('user_id');

            $table->primary(['role_id', 'user_id']);
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        // Existing root administrators keep their access through the built-in role.
        $role = DB::table('roles')->insertGetId([
            'name' => 'Root Administrator',
            'description' => 'Full access to every server and setting on the panel.',
            'root' => true,
            'permissions' => '[]',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->where('root_admin', true)->orderBy('id')->pluck('id')->chunk(500)->each(
            fn ($ids) => DB::table('role_user')->insert($ids->map(fn ($id) => ['role_id' => $role, 'user_id' => $id])->all())
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
    }
};
