<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            ['label' => 'Revenue (MTD)', 'value' => 'Rp 0', 'trend' => '+0.0%'],
            ['label' => 'Open Sales Orders', 'value' => '0', 'trend' => '0 pending'],
            ['label' => 'Low Stock SKUs', 'value' => '0', 'trend' => 'Safe'],
            ['label' => 'Production Queue', 'value' => '0', 'trend' => 'No backlog'],
        ];

        return view('admin.dashboard.index', compact('stats'));
    }
}
