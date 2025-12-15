<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['organization_id']);
            
            // Drop unique constraint
            $table->dropUnique(['organization_id', 'slug']);
            
            // Make organization_id nullable to support personal profiles
            $table->unsignedBigInteger('organization_id')->nullable()->change();
            
            // Recreate foreign key constraint with nullable support
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->onDelete('cascade');
            
            // Create new unique constraint that allows null organization_id
            // Note: MySQL allows multiple NULL values in unique constraints
            $table->unique(['organization_id', 'slug'], 'profiles_org_slug_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            // Drop unique constraint
            $table->dropUnique('profiles_org_slug_unique');
            
            // Drop foreign key constraint
            $table->dropForeign(['organization_id']);
            
            // Make organization_id required again (this will fail if there are NULL values)
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
            
            // Recreate foreign key constraint
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->onDelete('cascade');
            
            // Restore original unique constraint
            $table->unique(['organization_id', 'slug']);
        });
    }
};
