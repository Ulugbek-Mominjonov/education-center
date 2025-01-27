<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
  public function index(Request $request)
  {
    $request->validate([
      'is_active' => 'boolean',
      'is_attach' => 'boolean'
    ]);

    $query = User::query();

    if ($request->has('is_active')) {
      $active = $request->query('is_active');
      $query->where('is_active', '=', $active);
    }

    if ($request->has('is_attach')) {
      $attach = $request->query('is_attach');
      $query->where('is_attach', '=', $attach);
    }


    return response()->json(UserResource::collection($query->with(['userType', 'roles'])->get()), 200);
  }

  public function paginate(Request $request)
  {
    $request->validate([
      'per_page' => 'integer',
      'page' => 'integer',
      'is_active' => 'boolean',
      'is_attach' => 'boolean',
      'role_id' => 'integer'
    ]);


    $perPage = $request->query('per_page', 10);
    $page = $request->query('page', 1);

    $query = User::query();

    if ($request->has('is_active')) {
      $is_active = $request->query('is_active');
      $query->where('is_active', '=', $is_active);
    }

    if ($request->has('attach')) {
      $attach = $request->query('is_attach');
      $query->where('is_attach', '=', $attach);
    }

    if ($request->has('role_id')) {
      $role_id = $request->query('role_id');
      $query->whereHas('roles', function ($query) use ($role_id) {
        $query->where('role_id', '=', $role_id);
      });
    }

    $users = $query->with(['userType', 'roles'])->paginate($perPage, ['*'], 'page', $page);

    return response()->json(
      [
        'data' => UserResource::collection($users),
        'total' => $users->total(),
        'last_page' => $users->lastPage(),
        'current_page' => $users->currentPage(),
        'per_page' => $users->perPage()
      ],
      200
    );
  }

  public function store(StoreUserRequest $request)
  {
    try {
      $user = User::create([
        'user_name' => $request->user_name,
        'user_type_id' => $request->user_type_id,
        'password' => Hash::make(is_null($request->password) ? '12345678' : $request->password),
      ]);


      $user->roles()->attach($request->user_roles);

      return response()->json(new UserResource($user->load('userType', 'roles')), 201);
    } catch (\Exception $e) {
      return response()->json(['message' => $e->getMessage()], 500);
    }
  }

  public function show($id)
  {

    $user = User::find($id);

    if (!$user) {
      return response()->json(['message' => 'user not found'], 404);
    }

    return response()->json(new UserResource($user->load(['userType', 'roles'])), 200);
  }

  public function toggleActive($id)
  {
    $user = User::find($id);

    if (!$user) {
      return response()->json(['message' => 'user not found'], 404);
    }

    $user->is_active = !$user->is_active;
    $user->save();

    return response()->json(new UserResource($user->load(['userType', 'roles'])), 200);
  }

  public function changeRoles(Request $request, $id)
  {
    $request->validate([
      'user_roles' => 'array|required'
    ]);

    $user = User::find($id);

    if (!$user) {
      return response()->json(['message' => 'user not found'], 404);
    }

    $user->roles()->sync($request->user_roles);

    return response()->json(new UserResource($user->load(['userType', 'roles'])), 200);
  }

  public function destroy($id)
  {
    $user = User::find($id);

    if (!$user) {
      return response()->json(['message' => 'user not found'], 404);
    }

    try {
      $user->delete();
      return response()->json(['message' => 'user deleted'], 200);
    } catch (\Exception $e) {
      return response()->json(['message' => $e->getMessage()], 500);
    }
  }
}
