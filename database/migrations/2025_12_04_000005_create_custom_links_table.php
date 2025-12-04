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
        Schema::create('custom_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('url');
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // Icon name or custom icon URL
            $table->string('thumbnail')->nullable(); // Optional thumbnail image
            $table->string('button_color', 9)->nullable(); // Custom button color
            $table->string('text_color', 9)->nullable(); // Custom text color
            $table->boolean('is_highlighted')->default(false); // Featured/highlighted link
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('clicks_count')->default(0);
            $table->timestamps();

            $table->index(['profile_id', 'is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_links');
    }
};

