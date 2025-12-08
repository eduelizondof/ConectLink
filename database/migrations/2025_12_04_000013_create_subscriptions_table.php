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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('subscription_plans')->cascadeOnDelete();

            $table->enum('billing_cycle', ['monthly', 'quarterly', 'semiannual', 'annual'])->default('monthly');
            $table->decimal('amount_paid', 8, 2);
            $table->string('currency', 3)->default('USD');

            $table->enum('status', ['active', 'expired', 'cancelled', 'pending', 'trial'])->default('pending');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();

            // Payment info (external reference)
            $table->string('payment_method')->nullable(); // stripe, paypal, manual, etc.
            $table->string('payment_reference')->nullable(); // External transaction ID

            $table->text('notes')->nullable(); // Admin notes
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};





