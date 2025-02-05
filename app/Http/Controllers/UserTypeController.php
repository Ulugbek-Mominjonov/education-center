<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginateRequest;
use App\Http\Requests\UserTypeRequest;
use App\Repositories\Interfaces\UserTypeRepositoryInterface;
use Illuminate\Http\Request;

class UserTypeController extends Controller
{

  protected $userTypeRepository;

  public function __construct(UserTypeRepositoryInterface $userTypeRepository)
  {
    $this->userTypeRepository = $userTypeRepository;
  }
  public function index()
  {
    return $this->userTypeRepository->all();
  }

  public function paginate(PaginateRequest $request)
  {
    return $this->userTypeRepository->paginate($request);
  }

  public function store(UserTypeRequest $request)
  {
    $this->userTypeRepository->create([
      'name' => $request->name,
      'description' => $request->description,
    ]);
  }

  public function show($id)
  {
    return $this->userTypeRepository->find($id);
  }

  public function update(Request $request, $id)
  {
    return $this->userTypeRepository->update($id, [
      'name' => $request->name,
      'description' => $request->description,
    ]);
  }

  public function destroy($id)
  {
    return $this->userTypeRepository->delete($id);
  }
}
