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
        Schema::create('qr_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');

            // QR Colors
            $table->string('foreground_color', 7)->default('#000000');
            $table->string('background_color', 7)->default('#FFFFFF');

            // QR Style
            $table->enum('dot_style', ['square', 'rounded', 'dots', 'classy', 'classy-rounded', 'extra-rounded'])->default('square');
            $table->enum('corner_style', ['square', 'rounded', 'extra-rounded'])->default('square');
            $table->string('corner_color', 7)->nullable(); // Uses foreground if null

            // Center Logo
            $table->boolean('show_logo')->default(false);
            $table->enum('logo_source', ['organization', 'profile', 'custom'])->default('organization');
            $table->string('custom_logo')->nullable(); // Custom logo path
            $table->integer('logo_size')->default(20); // Percentage of QR size
            $table->enum('logo_shape', ['square', 'rounded', 'circle'])->default('circle');
            $table->string('logo_background_color', 7)->default('#FFFFFF');
            $table->boolean('logo_background_enabled')->default(true);
            $table->integer('logo_margin')->default(5); // Margin around logo in pixels

            // Error correction level (higher allows more damage/logo overlay)
            $table->enum('error_correction', ['L', 'M', 'Q', 'H'])->default('H');

            // QR Size
            $table->integer('size')->default(300); // Default size in pixels

            // Gradient options (optional advanced feature)
            $table->boolean('use_gradient')->default(false);
            $table->string('gradient_start_color', 7)->nullable();
            $table->string('gradient_end_color', 7)->nullable();
            $table->enum('gradient_type', ['linear', 'radial'])->default('linear');
            $table->integer('gradient_rotation')->default(0); // 0-360 degrees

            $table->timestamps();

            // Each profile can have only one QR setting
            $table->unique('profile_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_settings');
    }
};
