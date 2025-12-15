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
        Schema::table('profile_settings', function (Blueprint $table) {
            // Modify background_type enum to include 'animated'
            $table->enum('background_type', ['solid', 'gradient', 'image', 'animated'])->default('solid')->change();
            
            // Add animated background fields
            $table->string('background_animated_media')->nullable()->after('background_image');
            $table->enum('background_animated_media_type', ['image', 'gif', 'video'])->nullable()->after('background_animated_media');
            $table->unsignedTinyInteger('background_animated_overlay_opacity')->default(0)->after('background_animated_media_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile_settings', function (Blueprint $table) {
            $table->dropColumn(['background_animated_media', 'background_animated_media_type', 'background_animated_overlay_opacity']);
            $table->enum('background_type', ['solid', 'gradient', 'image'])->default('solid')->change();
        });
    }
};
