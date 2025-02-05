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
    return $this->roleRepository->all();
  }

  public function paginate(PaginateRequest $request)
  {
    return $this->roleRepository->paginate($request);
  }

  public function store(RoleRequest $request)
  {
    return $this->roleRepository->create([
      'name' => $request->name,
      'description' => $request->description,
    ]);
  }

  public function show($id)
  {
    return $this->roleRepository->find($id);
  }

  public function update(RoleRequest $request, $id)
  {
    return $this->roleRepository->update($id, [
      'name' => $request->name,
      'description' => $request->description
    ]);
  }

  public function destroy($id)
  {
    return $this->roleRepository->delete($id);
  }
}
