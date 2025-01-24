<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserTypeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout']);

    // paginate
    Route::get('/user-types/paginate', [UserTypeController::class, 'paginate']);
    Route::get('/roles/paginate', [RoleController::class, 'paginate']);
    Route::get('users/paginate', [UserController::class, 'paginate']);
    Route::get('/teachers/paginate', [TeacherController::class, 'paginate']);

    // actions
    Route::put('users/toggle-active/{id}', [UserController::class, 'toggleActive']);
    Route::put('users/change-roles/{id}', [UserController::class, 'changeRoles']);

    // CRUD
    Route::apiResources([
        'user-types' => UserTypeController::class,
        'roles' => RoleController::class,
        'users' => UserController::class,
        'teachers' => TeacherController::class,
    ]);
});
