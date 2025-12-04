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
        Schema::create('floating_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('message');
            $table->enum('type', ['info', 'promo', 'warning', 'success', 'announcement'])->default('info');
            $table->string('icon')->nullable(); // Icon name
            $table->string('link_url')->nullable(); // Optional CTA link
            $table->string('link_text')->nullable(); // CTA button text
            $table->enum('position', ['top', 'bottom', 'top-left', 'top-right', 'bottom-left', 'bottom-right'])->default('top');
            $table->enum('animation', ['none', 'bounce', 'pulse', 'shake', 'slide'])->default('bounce');
            $table->string('background_color', 9)->nullable();
            $table->string('text_color', 9)->nullable();
            $table->boolean('is_dismissible')->default(true);
            $table->boolean('show_on_load')->default(true);
            $table->unsignedSmallInteger('delay_seconds')->default(0); // Delay before showing
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['profile_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('floating_alerts');
    }
};

