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
        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->enum('platform', [
                'facebook',
                'instagram',
                'twitter',
                'tiktok',
                'linkedin',
                'youtube',
                'whatsapp',
                'telegram',
                'pinterest',
                'snapchat',
                'threads',
                'github',
                'dribbble',
                'behance',
                'spotify',
                'apple_music',
                'soundcloud',
                'twitch',
                'discord',
                'website',
                'email',
                'phone',
                'other'
            ]);
            $table->string('url');
            $table->string('label')->nullable(); // Custom label override
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['profile_id', 'is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_links');
    }
};

