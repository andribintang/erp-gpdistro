<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard)
    {
    }

    public function index(): View
    {
        return view('admin.dashboard.index', [
            'stats' => $this->dashboard->stats(),
            'summary' => $this->dashboard->operationalSummary(),
            'alerts' => $this->dashboard->alerts(),
            'activities' => $this->dashboard->recentActivities(),
            'revenueChart' => $this->dashboard->revenueChart(),
        ]);
    }
}
