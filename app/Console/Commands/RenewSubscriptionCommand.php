<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RenewSubscriptionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:renew
                            {email : The user email}
                            {--plan= : New plan slug (optional, keeps current plan if not provided)}
                            {--cycle= : Billing cycle (monthly, quarterly, semiannual, annual)}
                            {--duration=1 : Duration in cycles}
                            {--extend : Extend from current end date instead of from now}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renew or upgrade a user subscription';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return self::FAILURE;
        }

        // Get current subscription
        $currentSubscription = $user->subscriptions()
            ->orderBy('created_at', 'desc')
            ->first();

        // Determine plan
        $planSlug = $this->option('plan');
        
        // If no current subscription and no plan specified, show error
        if (!$currentSubscription && !$planSlug) {
            $this->error("User has no subscription and no plan was specified.");
            $this->line("Use: php artisan subscription:renew {$email} --plan=<plan-slug>");
            $this->listPlans();
            return self::FAILURE;
        }

        // If no current subscription, use the specified plan
        if (!$currentSubscription) {
            $plan = SubscriptionPlan::where('slug', $planSlug)->first();
            if (!$plan) {
                $this->error("Plan '{$planSlug}' not found.");
                $this->listPlans();
                return self::FAILURE;
            }
        } else {
            $this->info("Current subscription:");
            $this->displaySubscription($currentSubscription);
            $this->newLine();
            
            // Determine plan from option or current subscription
            $plan = $planSlug
                ? SubscriptionPlan::where('slug', $planSlug)->first()
                : $currentSubscription->plan;

            if (!$plan) {
                $this->error("Plan '{$planSlug}' not found.");
                $this->listPlans();
                return self::FAILURE;
            }
        }

        // Determine cycle
        $cycle = $this->option('cycle') ?? ($currentSubscription->billing_cycle ?? 'annual');
        $duration = (int) $this->option('duration');
        $extend = $this->option('extend');

        $price = match ($cycle) {
            'monthly' => $plan->price_monthly,
            'quarterly' => $plan->price_quarterly,
            'semiannual' => $plan->price_semiannual,
            'annual' => $plan->price_annual,
            default => $plan->price_monthly,
        };

        // Calculate start and end dates
        $startsAt = ($extend && $currentSubscription && $currentSubscription->ends_at?->isFuture())
            ? $currentSubscription->ends_at
            : now();

        $endsAt = match ($cycle) {
            'monthly' => $startsAt->copy()->addMonths($duration),
            'quarterly' => $startsAt->copy()->addMonths(3 * $duration),
            'semiannual' => $startsAt->copy()->addMonths(6 * $duration),
            'annual' => $startsAt->copy()->addYears($duration),
            default => $startsAt->copy()->addMonths($duration),
        };

        // Mark old subscription as expired if renewing from now and subscription exists
        if ($currentSubscription && !$extend) {
            $currentSubscription->update([
                'status' => 'expired',
                'notes' => ($currentSubscription->notes ? $currentSubscription->notes . "\n" : '') .
                    'Renewed on ' . now()->format('Y-m-d H:i:s'),
            ]);
        }

        // Create new subscription
        $newSubscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'billing_cycle' => $cycle,
            'amount_paid' => $price * $duration,
            'currency' => $plan->currency,
            'status' => 'active',
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'payment_method' => 'manual',
            'payment_reference' => 'RENEW-' . strtoupper(Str::random(8)),
            'notes' => 'Renewed via artisan command' .
                ($extend ? ' (extended from previous)' : ''),
        ]);

        $this->info("✅ Subscription renewed successfully:");
        $this->displaySubscription($newSubscription);

        return self::SUCCESS;
    }

    protected function displaySubscription(Subscription $subscription): void
    {
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $subscription->id],
                ['Plan', $subscription->plan->name ?? 'N/A'],
                ['Cycle', $subscription->billing_cycle],
                ['Status', $subscription->status],
                ['Amount', '$' . number_format($subscription->amount_paid, 2) . ' ' . $subscription->currency],
                ['Starts', $subscription->starts_at?->format('Y-m-d H:i:s') ?? 'N/A'],
                ['Expires', $subscription->ends_at?->format('Y-m-d H:i:s') ?? 'N/A'],
                ['Days Remaining', $subscription->daysRemaining()],
            ]
        );
    }

    protected function listPlans(): void
    {
        $this->newLine();
        $this->info("Available plans:");
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

