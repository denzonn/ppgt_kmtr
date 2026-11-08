<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuditLogController extends Controller
{
    public function index()
    {
        return view('pages.admin.audit-log');
    }

    public function getData(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $data = AuditLog::query()
            ->when($search !== '', function ($q) use ($search) {
                $objek = collect(AuditLog::OBJEK)->filter(fn ($nama) => Str::contains($nama, $search, true))->keys();

                $q->where(fn ($w) => $w
                    ->where('user_nama', 'like', "%{$search}%")
                    ->orWhere('label', 'like', "%{$search}%")
                    ->orWhereIn('model', $objek));
            })
            ->when($request->filled('aksi'), fn ($q) => $q->where('aksi', $request->input('aksi')))
            ->latest('id')
            ->paginate(25);

        return response()->json([
            'status' => true,
            'data' => collect($data->items())->map(fn (AuditLog $log) => [
                'id' => $log->id,
                'waktu' => $log->created_at?->timezone('Asia/Makassar')->format('d/m/Y H:i') . ' WITA',
                'pelaku' => $log->user_nama,
                'aksi' => $log->aksi,
                'objek' => AuditLog::OBJEK[$log->model] ?? $log->model,
                'model_id' => $log->model_id,
                'label' => $log->label,
                'perubahan' => $log->perubahan,
                'ip' => $log->ip,
            ]),
            'total' => $data->total(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
        ]);
    }
}
