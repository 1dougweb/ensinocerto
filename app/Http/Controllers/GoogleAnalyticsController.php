<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;

class GoogleAnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard(): View
    {
        return view('admin.analytics.dashboard');
    }

    public function getAnalyticsData(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        // Dados mock SIMPLES e funcionais
        $data = [
            'summary' => [
                'total_sessions' => rand(1000, 5000),
                'total_users' => rand(800, 4000),
                'total_pageviews' => rand(3000, 15000),
                'avg_bounce_rate' => rand(30, 70),
                'users_online' => rand(5, 50), // Usuários online agora
                'real_time_visitors' => rand(10, 100) // Visitantes em tempo real
            ],
            'daily_data' => $this->generateDailyData($startDate, $endDate)
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function getGoogleAdsData(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $data = [
            'summary' => [
                'total_clicks' => rand(500, 2000),
                'total_impressions' => rand(10000, 50000),
                'total_cost' => rand(100, 1000),
                'total_conversions' => rand(50, 200)
            ],
            'campaigns' => $this->generateCampaignsData()
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function getTrafficSources(Request $request): JsonResponse
    {
        $data = [
            'sources' => [
                ['name' => 'Google Ads', 'sessions' => rand(200, 800), 'users' => rand(150, 600), 'bounce_rate' => rand(20, 60)],
                ['name' => 'Organic Search', 'sessions' => rand(300, 1000), 'users' => rand(250, 800), 'bounce_rate' => rand(30, 70)],
                ['name' => 'Direct', 'sessions' => rand(100, 400), 'users' => rand(80, 300), 'bounce_rate' => rand(25, 65)],
                ['name' => 'Social Media', 'sessions' => rand(50, 200), 'users' => rand(40, 150), 'bounce_rate' => rand(40, 80)],
                ['name' => 'Referral', 'sessions' => rand(20, 100), 'users' => rand(15, 80), 'bounce_rate' => rand(35, 75)],
                ['name' => 'Email Marketing', 'sessions' => rand(30, 150), 'users' => rand(25, 120), 'bounce_rate' => rand(15, 45)]
            ],
            'top_pages' => [
                ['url' => '/', 'title' => 'Página Inicial', 'views' => rand(500, 2000), 'time_on_page' => rand(60, 300)],
                ['url' => '/cursos', 'title' => 'Cursos', 'views' => rand(300, 1200), 'time_on_page' => rand(90, 400)],
                ['url' => '/sobre', 'title' => 'Sobre Nós', 'views' => rand(100, 500), 'time_on_page' => rand(45, 180)],
                ['url' => '/contato', 'title' => 'Contato', 'views' => rand(80, 300), 'time_on_page' => rand(30, 120)]
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    private function generateDailyData(string $startDate, string $endDate): array
    {
        $data = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($current->lte($end)) {
            $data[] = [
                'date' => $current->format('Y-m-d'),
                'sessions' => rand(50, 200),
                'users' => rand(40, 150),
                'pageviews' => rand(150, 600)
            ];
            $current->addDay();
        }

        return $data;
    }

    private function generateCampaignsData(): array
    {
        return [
            ['name' => 'Campanha Principal', 'clicks' => rand(100, 500), 'cost' => rand(50, 300)],
            ['name' => 'Campanha Secundária', 'clicks' => rand(50, 200), 'cost' => rand(25, 150)],
            ['name' => 'Campanha de Remarketing', 'clicks' => rand(30, 150), 'cost' => rand(15, 100)]
        ];
    }
}
