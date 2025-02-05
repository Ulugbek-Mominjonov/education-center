<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginateRequest;
use App\Http\Requests\RoleRequest;
use App\Repositories\Interfaces\RoleRepositoryInterface;

class RoleController extends Controller
{

  protected $roleRepository;

  public function __construct(RoleRepositoryInterface $roleRepository)
  {
    $this->roleRepository = $roleRepository;
  }

  public function index()
  {
    return response()->json($this->roleRepository->all(), 200);
  }

  public function paginate(PaginateRequest $request)
  {
    return response()->json(
      $this->roleRepository->paginate($request),
      200
    );
  }

  public function store(RoleRequest $request)
  {
    try {
      $role = $this->roleRepository->create([
        'name' => $request->name,
        'description' => $request->description,
      ]);
      return response()->json($role, 201);
    } catch (\Exception $e) {
      return response()->json(['message' => $e->getMessage()], 500);
    }
  }

  public function show($id)
  {

    $role = $this->roleRepository->find($id);

    if (!$role) {
      return response()->json(['message' => 'role not found'], 404);
    }

    return response()->json($role, 200);
  }

  public function update(RoleRequest $request, $id)
  {

    $role = $this->roleRepository->update($id, [
      'name' => $request->name,
      'description' => $request->description,
    ]);

    if (!$role) {
      return response()->json(['message' => 'role not found'], 404);
    }

    return response()->json($role, 200);
  }

  public function destroy($id)
  {
    $role = $this->roleRepository->delete($id);
    if (!$role) {
      return response()->json(['message' => 'role not found'], 404);
    }
    return response()->json(['message' => 'role deleted'], 200);
  }
}
