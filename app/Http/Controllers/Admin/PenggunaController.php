<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jemaat;
use App\Models\Periode;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PenggunaController extends Controller
{
    private function query()
    {
        return User::whereHas('role', fn ($q) => $q->where('is_admin', false));
    }

    public function index()
    {
        $roles = Role::where('is_admin', false)->orderBy('nama')->get(['id', 'nama', 'menus']);

        return view('pages.admin.pengguna', [
            'roles' => $roles,
            'periodes' => Periode::orderByDesc('tahun_mulai')->orderByDesc('tahun_selesai')->get(),
            'butuhPeriode' => $roles->mapWithKeys(fn ($r) => [$r->id => $r->butuhPeriode()]),
            'jemaats' => Jemaat::orderBy('nama')->get(['id', 'nama']),
            'butuhJemaat' => $roles->mapWithKeys(fn ($r) => [$r->id => $r->butuhJemaat()]),
        ]);
    }

    public function getData(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $paginator = $this->query()
            ->with(['role:id,nama', 'periode', 'jemaat:id,nama'])
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%')))
            ->orderBy('name')
            ->paginate(25);

        $paginator->getCollection()->each->makeVisible('password_view');

        return response()->json([
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
        ]);
    }

    private function simpan(Request $request, ?User $user = null): void
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:6'],
            'role_id' => ['required', Rule::exists('roles', 'id')->where('is_admin', false)],
            'periode_id' => ['nullable', 'exists:periodes,id'],
            'jemaat_id' => ['nullable', 'exists:jemaat,id'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'role_id.required' => 'Role wajib dipilih.',
            'role_id.exists' => 'Role tidak valid.',
            'periode_id.exists' => 'Periode tidak valid.',
            'jemaat_id.exists' => 'Jemaat tidak valid.',
        ]);

        $role = Role::find($data['role_id']);
        $butuh = $role->butuhPeriode();
        $butuhJemaat = $role->butuhJemaat();

        if ($butuhJemaat && empty($data['jemaat_id'])) {
            $request->validate(['jemaat_id' => 'required'], ['jemaat_id.required' => 'Jemaat wajib dipilih untuk role ini.']);
        }

        if ($butuh && empty($data['periode_id'])) {
            $request->validate(['periode_id' => 'required'], ['periode_id.required' => 'Periode wajib dipilih untuk role kepengurusan.']);
        }

        $isi = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role_id' => $role->id,
            'periode_id' => $butuh ? $data['periode_id'] : null,
            'jemaat_id' => $butuhJemaat ? $data['jemaat_id'] : null,
        ];

        if (! empty($data['password'])) {
            $isi['password'] = $data['password'];
            $isi['password_view'] = $data['password'];
        }

        $user ? $user->update($isi) : User::create($isi);
    }

    public function store(Request $request)
    {
        $this->simpan($request);

        return response()->json(['status' => true, 'message' => 'Pengguna berhasil ditambahkan.']);
    }

    public function show($id)
    {
        $user = $this->query()->with(['role:id,nama', 'periode', 'jemaat:id,nama'])->findOrFail($id);

        return response()->json($user->makeVisible('password_view'));
    }

    public function update(Request $request, $id)
    {
        $this->simpan($request, $this->query()->findOrFail($id));

        return response()->json(['status' => true, 'message' => 'Pengguna berhasil diperbarui.']);
    }

    public function destroy($id)
    {
        $this->query()->findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Pengguna berhasil dihapus.']);
    }
}
