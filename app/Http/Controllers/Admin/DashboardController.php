<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataAnggota;
use App\Models\Inventaris;
use App\Models\Jemaat;
use App\Models\PeminjamanInventaris;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $inventarisCount = Inventaris::count();
        $inventarisThisMonthCount = Inventaris::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        $dipinjamCount = PeminjamanInventaris::where('status', 'dipinjam')->count();
        $dikembalikanCount = PeminjamanInventaris::where('status', 'dikembalikan')->count();
        $anggotaAktifCount = DataAnggota::where('status_anggota', 'Aktif')->count();
        $jemaatCount = Jemaat::count();

        return view('pages.admin.dashboard', compact(
            'inventarisCount',
            'inventarisThisMonthCount',
            'dipinjamCount',
            'dikembalikanCount',
            'anggotaAktifCount',
            'jemaatCount'
        ));
    }
}
