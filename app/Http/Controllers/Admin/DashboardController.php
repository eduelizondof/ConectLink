<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Profile;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get user's organizations with counts
        $organizations = $user->organizations()
            ->withCount(['profiles', 'products'])
            ->get();

        // Get stats
        $stats = [
            'total_organizations' => $organizations->count(),
            'total_profiles' => $organizations->sum('profiles_count'),
            'total_products' => $organizations->sum('products_count'),
            'total_views' => Profile::whereIn('organization_id', $organizations->pluck('id'))->sum('views_count'),
        ];

        // Get limits
        $limits = $user->getLimits();

        // Get active subscription
        $subscription = $user->subscriptions()->with('plan')->active()->first();

        // Tips for users
        $tips = [
            [
                'icon' => 'lightbulb',
                'title' => 'Personaliza tu perfil',
                'description' => 'Agrega tu logo, colores de marca y un slogan memorable para destacar.',
            ],
            [
                'icon' => 'share',
                'title' => 'Comparte tu link',
                'description' => 'Comparte tu perfil en redes sociales, tarjetas de presentación o códigos QR.',
            ],
            [
                'icon' => 'chart',
                'title' => 'Revisa tus estadísticas',
                'description' => 'Monitorea las visitas a tu perfil para entender mejor a tu audiencia.',
            ],
            [
                'icon' => 'bell',
                'title' => 'Usa alertas flotantes',
                'description' => 'Destaca promociones o anuncios importantes con alertas llamativas.',
            ],
        ];

        return Inertia::render('Admin/Dashboard', [
            'organizations' => $organizations,
            'stats' => $stats,
            'limits' => [
                'max_organizations' => $limits->max_organizations,
                'max_profiles_per_org' => $limits->max_profiles_per_org,
                'max_products_per_org' => $limits->max_products_per_org,
                'can_access_analytics' => $limits->can_access_analytics,
            ],
            'subscription' => $subscription ? [
                'plan_name' => $subscription->plan->name,
                'status' => $subscription->status,
                'ends_at' => $subscription->ends_at?->format('d/m/Y'),
                'days_remaining' => $subscription->daysRemaining(),
            ] : null,
            'tips' => $tips,
        ]);
    }
}





