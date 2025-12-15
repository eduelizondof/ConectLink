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
        Schema::table('custom_links', function (Blueprint $table) {
            $table->string('image')->nullable()->after('thumbnail');
            $table->enum('image_position', ['left', 'right', 'top', 'bottom'])->nullable()->after('image');
            $table->enum('image_shape', ['square', 'circle'])->nullable()->after('image_position');
            $table->string('url')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_links', function (Blueprint $table) {
            $table->dropColumn(['image', 'image_position', 'image_shape']);
            $table->string('url')->nullable(false)->change();
        });
    }
};
