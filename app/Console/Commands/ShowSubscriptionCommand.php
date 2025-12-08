<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Console\Command;

class ShowSubscriptionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:show
                            {email : The user email}
                            {--all : Show all subscriptions, not just the active one}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show subscription details for a user';

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

        $this->info("User: {$user->name} ({$user->email})");
        $this->newLine();

        $showAll = $this->option('all');

        $subscriptions = $showAll
            ? $user->subscriptions()->with('plan')->orderBy('created_at', 'desc')->get()
            : $user->subscriptions()->with('plan')->where('status', 'active')->orderBy('created_at', 'desc')->get();

        if ($subscriptions->isEmpty()) {
            $this->warn($showAll
                ? "This user has no subscriptions."
                : "This user has no active subscriptions. Use --all to see all subscriptions.");
            return self::SUCCESS;
        }

        foreach ($subscriptions as $subscription) {
            $this->displaySubscription($subscription);
            $this->newLine();
        }

        // Show summary
        $this->info("📊 Summary:");
        $activeCount = $user->subscriptions()->where('status', 'active')->count();
        $totalPaid = $user->subscriptions()->sum('amount_paid');

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Subscriptions', $user->subscriptions()->count()],
                ['Active Subscriptions', $activeCount],
                ['Has Active', $user->hasActiveSubscription() ? '✅ Yes' : '❌ No'],
                ['Total Paid', '$' . number_format($totalPaid, 2)],
            ]
        );

        // Show plan limits if active subscription
        $activeSubscription = $user->subscriptions()->with('plan')->where('status', 'active')->first();
        if ($activeSubscription && $activeSubscription->plan) {
            $this->newLine();
            $this->info("📋 Plan Limits ({$activeSubscription->plan->name}):");
            $plan = $activeSubscription->plan;
            $this->table(
                ['Limit', 'Value'],
                [
                    ['Max Organizations', $plan->max_organizations],
                    ['Max Profiles per Org', $plan->max_profiles_per_org],
                    ['Max Products per Org', $plan->max_products_per_org],
                    ['Max Custom Links per Profile', $plan->max_custom_links_per_profile],
                    ['Max Social Links per Profile', $plan->max_social_links_per_profile],
                    ['Max Alerts per Profile', $plan->max_alerts_per_profile],
                    ['Custom Domain', $plan->can_use_custom_domain ? '✅' : '❌'],
                    ['Remove Branding', $plan->can_remove_branding ? '✅' : '❌'],
                    ['Analytics', $plan->can_access_analytics ? '✅' : '❌'],
                ]
            );
        }

        return self::SUCCESS;
    }

    protected function displaySubscription(Subscription $subscription): void
    {
        $statusIcon = match ($subscription->status) {
            'active' => '🟢',
            'expired' => '🔴',
            'cancelled' => '⚪',
            'pending' => '🟡',
            'trial' => '🔵',
            default => '⚫',
        };

        $this->info("$statusIcon Subscription #{$subscription->id}");
        $this->table(
            ['Field', 'Value'],
            [
                ['Plan', $subscription->plan->name ?? 'N/A'],
                ['Status', ucfirst($subscription->status)],
                ['Billing Cycle', ucfirst($subscription->billing_cycle)],
                ['Amount Paid', '$' . number_format($subscription->amount_paid, 2) . ' ' . $subscription->currency],
                ['Payment Method', $subscription->payment_method ?? 'N/A'],
                ['Payment Ref', $subscription->payment_reference ?? 'N/A'],
                ['Started', $subscription->starts_at?->format('Y-m-d H:i:s') ?? 'N/A'],
                ['Expires', $subscription->ends_at?->format('Y-m-d H:i:s') ?? 'N/A'],
                ['Days Remaining', $subscription->isActive() ? $subscription->daysRemaining() : 'Expired'],
                ['Is Active', $subscription->isActive() ? '✅ Yes' : '❌ No'],
                ['Created', $subscription->created_at->format('Y-m-d H:i:s')],
            ]
        );

        if ($subscription->notes) {
            $this->line("Notes: {$subscription->notes}");
        }
    }
}

