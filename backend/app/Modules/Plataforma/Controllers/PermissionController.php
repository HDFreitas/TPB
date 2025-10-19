<?php
namespace App\Modules\Plataforma\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Plataforma\Repositories\PermissionRepository;

class PermissionController extends Controller
{
    protected $permissionRepository;

    public function __construct(PermissionRepository $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $permissions = $this->permissionRepository->getAll($perPage, true);
        return response()->json($permissions);
    }

    public function show($id)
    {
        $permission = $this->permissionRepository->findById($id);
        return response()->json($permission);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:permissions,name',
            'guard_name' => 'required|string',
        ]);
        $permission = $this->permissionRepository->create($data);
        return response()->json($permission, 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|unique:permissions,name,' . $id,
            'guard_name' => 'sometimes|required|string',
        ]);
        $permission = $this->permissionRepository->update($id, $data);
        return response()->json($permission);
    }

    public function destroy($id)
    {
        $this->permissionRepository->delete($id);
        return response()->json(['message' => 'Permissão removida com sucesso.']);
    }
}
