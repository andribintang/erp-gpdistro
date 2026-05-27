<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            ['label' => 'Pendapatan (Bulan Berjalan)', 'value' => 'Rp 0', 'trend' => '+0,0%'],
            ['label' => 'Sales Order Terbuka', 'value' => '0', 'trend' => '0 menunggu'],
            ['label' => 'SKU Stok Menipis', 'value' => '0', 'trend' => 'Aman'],
            ['label' => 'Antrian Produksi', 'value' => '0', 'trend' => 'Tidak ada backlog'],
        ];

        return view('admin.dashboard.index', compact('stats'));
    }
}
