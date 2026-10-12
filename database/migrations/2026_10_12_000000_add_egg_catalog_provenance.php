<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eggs', function (Blueprint $table): void {
            $table->char('catalog_key', 64)->nullable();
            $table->json('catalog_origin')->nullable();
            $table->unique(['nest_id', 'catalog_key'], 'eggs_nest_catalog_unique');
        });
    }

    public function down(): void
    {
        Schema::table('eggs', function (Blueprint $table): void {
            $table->dropUnique('eggs_nest_catalog_unique');
            $table->dropColumn(['catalog_key', 'catalog_origin']);
        });
    }
};
