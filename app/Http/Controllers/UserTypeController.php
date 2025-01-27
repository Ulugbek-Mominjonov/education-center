<?php

namespace App\Http\Controllers;

use App\Models\UserType;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class UserTypeController extends Controller
{
  public function index()
  {
    return response()->json(UserType::all(), 200);
  }

  public function paginate(Request $request)
  {
    $request->validate([
      'per_page' => 'integer',
      'page' => 'integer',
    ]);

    $perPage = $request->query('per_page', 10);
    $page = $request->query('page', 1);

    $userTypes = UserType::paginate($perPage, ['*'], 'page', $page);

    return response()->json(
      $userTypes,
      200
    );
  }

  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required',
    ]);

    try {
      $userType = UserType::create([
        'name' => $request->name,
        'description' => $request->description,
      ]);
      return response()->json($userType, 201);
    } catch (\Exception $e) {
      return response()->json(['message' => $e->getMessage()], 500);
    }
  }

  public function show($id)
  {

    $userType = UserType::find($id);

    if (!$userType) {
      return response()->json(['message' => 'userType not found'], 404);
    }

    return response()->json($userType, 200);
  }

  public function update(Request $request, $id)
  {
    $userType = UserType::find($id);

    if (!$userType) {
      return response()->json(['message' => 'userType not found'], 404);
    }

    $request->validate([
      'name' => 'required',
    ]);

    try {
      $userType->update([
        'name' => $request->name,
        'description' => $request->description,
      ]);
      return response()->json($userType, 200);
    } catch (\Exception $e) {
      return response()->json(['message' => $e->getMessage()], 500);
    }
  }

  public function destroy($id)
  {
    $userType = UserType::find($id);

    if (!$userType) {
      return response()->json(['message' => 'userType not found'], 404);
    }

    try {
      $userType->delete();
      return response()->json(['message' => 'userType deleted'], 200);
    } catch (\Exception $e) {
      return response()->json(['message' => $e->getMessage()], 500);
    }
  }
}
