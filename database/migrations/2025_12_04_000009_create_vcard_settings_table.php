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
        Schema::create('vcard_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();

            // Basic contact info
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('nickname')->nullable();
            $table->string('prefix')->nullable(); // Mr., Mrs., Dr., etc.
            $table->string('suffix')->nullable(); // Jr., Sr., III, etc.

            // Organization info
            $table->string('organization')->nullable();
            $table->string('job_title')->nullable();
            $table->string('department')->nullable();

            // Contact methods
            $table->string('email')->nullable();
            $table->string('email_work')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_work')->nullable();
            $table->string('phone_mobile')->nullable();
            $table->string('fax')->nullable();

            // Address
            $table->string('address_street')->nullable();
            $table->string('address_city')->nullable();
            $table->string('address_state')->nullable();
            $table->string('address_zip')->nullable();
            $table->string('address_country')->nullable();

            // Online
            $table->string('website')->nullable();

            // Additional
            $table->date('birthday')->nullable();
            $table->text('notes')->nullable();

            // Settings
            $table->boolean('is_active')->default(true);
            $table->boolean('include_photo')->default(true);
            $table->timestamps();

            $table->unique('profile_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vcard_settings');
    }
};

