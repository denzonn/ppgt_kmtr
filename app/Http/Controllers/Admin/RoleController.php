<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        return view('pages.admin.role', [
            'menuUmum' => Role::MENU_UMUM,
            'menuKepengurusan' => Role::MENU_KEPENGURUSAN,
        ]);
    }

    public function getData(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $paginator = Role::query()
            ->withCount('users')
            ->when($search !== '', fn ($q) => $q->where('nama', 'like', '%' . $search . '%'))
            ->orderBy('nama')
            ->paginate(25);

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

    private function rules(?int $ignoreId = null): array
    {
        return [
            'nama' => ['required', 'string', 'max:100', Rule::unique('roles', 'nama')->ignore($ignoreId)],
            'menus' => ['present', 'array'],
            'menus.*' => ['string', Rule::in(array_keys(Role::MENU_UMUM + Role::MENU_KEPENGURUSAN))],
        ];
    }

    private function tolakAdmin(Role $role)
    {
        return $role->is_admin
            ? response()->json(['status' => false, 'message' => 'Role admin tidak dapat diubah atau dihapus.'], 422)
            : null;
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        Role::create(['nama' => $data['nama'], 'menus' => array_values(array_unique($data['menus']))]);

        return response()->json(['status' => true, 'message' => 'Role berhasil ditambahkan.']);
    }

    public function show($id)
    {
        return response()->json(Role::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        if ($tolak = $this->tolakAdmin($role)) {
            return $tolak;
        }

        $data = $request->validate($this->rules($role->id));

        $role->update(['nama' => $data['nama'], 'menus' => array_values(array_unique($data['menus']))]);

        return response()->json(['status' => true, 'message' => 'Role berhasil diperbarui.']);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        if ($tolak = $this->tolakAdmin($role)) {
            return $tolak;
        }

        if ($role->users()->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Role masih dipakai pengguna dan belum dapat dihapus.',
            ], 422);
        }

        $role->delete();

        return response()->json(['status' => true, 'message' => 'Role berhasil dihapus.']);
    }
}
