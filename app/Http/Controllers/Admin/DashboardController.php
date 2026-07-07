<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventaris;
use App\Models\PeminjamanInventaris;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $inventarisCount = Inventaris::count();
        $inventarisThisMonthCount = Inventaris::whereMonth('created_at', now()->month)->count();
        $peminjamanCount = PeminjamanInventaris::where('status', 'dikembalikan')->count();
        $menungguKembaliPinjamanCount = PeminjamanInventaris::where('status', 'dipinjam')->count();

        return view('pages.admin.dashboard', compact('inventarisCount', 'inventarisThisMonthCount', 'peminjamanCount', 'menungguKembaliPinjamanCount'));
    }
}
