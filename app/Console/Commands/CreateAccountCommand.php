<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateAccountCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:create
                            {email : The email address for the account}
                            {--name= : The name for the account}
                            {--password= : The password (will be generated if not provided)}
                            {--plan= : Subscription plan slug (basico, profesional, empresarial)}
                            {--cycle=annual : Billing cycle (monthly, quarterly, semiannual, annual)}
                            {--duration=1 : Duration in cycles (e.g., 1 year if cycle is annual)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user account with optional subscription';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');

        // Check if user already exists
        if (User::where('email', $email)->exists()) {
            $this->error("A user with email '{$email}' already exists.");
            return self::FAILURE;
        }

        $name = $this->option('name') ?? Str::before($email, '@');
        $password = $this->option('password') ?? Str::random(12);

        // Create user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
            'email_verified_at' => now(),
        ]);

        $this->info("✅ User created successfully:");
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $user->id],
                ['Name', $user->name],
                ['Email', $user->email],
                ['Password', $password],
            ]
        );

        // Create subscription if plan provided
        $planSlug = $this->option('plan');
        if ($planSlug) {
            $plan = SubscriptionPlan::where('slug', $planSlug)->first();

            if (!$plan) {
                $this->warn("Plan '{$planSlug}' not found. Available plans:");
                $this->listPlans();
                return self::SUCCESS;
            }

            $cycle = $this->option('cycle');
            $duration = (int) $this->option('duration');

            $price = match ($cycle) {
                'monthly' => $plan->price_monthly,
                'quarterly' => $plan->price_quarterly,
                'semiannual' => $plan->price_semiannual,
                'annual' => $plan->price_annual,
                default => $plan->price_monthly,
            };

            $endsAt = match ($cycle) {
                'monthly' => now()->addMonths($duration),
                'quarterly' => now()->addMonths(3 * $duration),
                'semiannual' => now()->addMonths(6 * $duration),
                'annual' => now()->addYears($duration),
                default => now()->addMonths($duration),
            };

            Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $cycle,
                'amount_paid' => $price * $duration,
                'currency' => $plan->currency,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => $endsAt,
                'payment_method' => 'manual',
                'payment_reference' => 'CLI-' . strtoupper(Str::random(8)),
                'notes' => 'Created via artisan command',
            ]);

            $this->newLine();
            $this->info("✅ Subscription created:");
            $this->table(
                ['Field', 'Value'],
                [
                    ['Plan', $plan->name],
                    ['Cycle', $cycle],
                    ['Amount', '$' . number_format($price * $duration, 2) . ' ' . $plan->currency],
                    ['Expires', $endsAt->format('Y-m-d H:i:s')],
                ]
            );
        }

        return self::SUCCESS;
    }

    protected function listPlans(): void
    {
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();
        $this->table(
            ['Slug', 'Name', 'Monthly', 'Annual'],
            $plans->map(fn($p) => [
                $p->slug,
                $p->name,
                '$' . number_format($p->price_monthly, 2),
                '$' . number_format($p->price_annual, 2),
            ])->toArray()
        );
    }
}

