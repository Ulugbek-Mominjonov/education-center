<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
  {
    return response()->json([
      'data' => Role::all()
    ], 200);
  }

  public function paginate(Request $request)
  {
    $request->validate([
      'per_page' => 'integer',
      'page' => 'integer',
    ]);

    $perPage = $request->query('per_page', 10);
    $page = $request->query('page', 1);
    
    $roles = Role::paginate($perPage, ['*'], 'page', $page);

    return response()->json(
      $roles
    , 200);
  }   
  
  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required',
    ]);

    try {
      $role = Role::create([
        'name' => $request->name,
        'description' => $request->description,
      ]);
      return response()->json($role, 201);
    } catch (\Exception $e) {
      return response()->json(['message' => 'role not created'], 500);
    }
  }

  public function show($id)
  {

      $role = Role::find($id);

      if (!$role) {
          return response()->json(['message' => 'role not found'], 404);
      }

      return response()->json($role, 200);
  }

  public function update(Request $request, $id)
  {
      $role = Role::find($id);

      if (!$role) {
          return response()->json(['message' => 'role not found'], 404);
      }

      $request->validate([
          'name' => 'required',
      ]);

      try {
          $role->update([
              'name' => $request->name,
              'description' => $request->description,
          ]);
          return response()->json($role, 200);
      } catch (\Exception $e) {
          return response()->json(['message' => 'role not updated'], 500);
      }
  }

  public function destroy($id)
  {
      $role = Role::find($id);

      if (!$role) {
          return response()->json(['message' => 'role not found'], 404);
      }

      try {
          $role->delete();
          return response()->json(['message' => 'role deleted'], 200);
      } catch (\Exception $e) {
          return response()->json(['message' => 'role not deleted'], 500);
      }
  }
}
