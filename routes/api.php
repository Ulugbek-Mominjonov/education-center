<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserTypeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::get('/students/download-latest-excel-file', [StudentController::class, 'downloadLatestExcelFile']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout']);

    // paginate
    Route::get('/users/paginate', [UserController::class, 'paginate']);
    Route::get('/roles/paginate', [RoleController::class, 'paginate']);
    Route::get('/tasks/paginate', [TaskController::class, 'paginate']);
    Route::get('/groups/paginate', [GroupController::class, 'paginate']);
    Route::get('/students/paginate', [StudentController::class, 'paginate']);
    Route::get('/teachers/paginate', [TeacherController::class, 'paginate']);
    Route::get('/subjects/paginate', [SubjectController::class, 'paginate']);
    Route::get('/user-types/paginate', [UserTypeController::class, 'paginate']);

    // actions start
    // users
    Route::put('/users/change-roles/{id}', [UserController::class, 'changeRoles']);
    Route::put('/users/toggle-active/{id}', [UserController::class, 'toggleActive']);

    // groups
    Route::put('/groups/attach-task/{id}', [GroupController::class, 'attachTask']);
    Route::put('/groups/change-subject/{id}', [GroupController::class, 'changeSubject']);
    Route::put('/groups/attach-teachers/{id}', [GroupController::class, 'attachTeachers']);
    Route::put('/groups/attach-students/{id}', [GroupController::class, 'attachStudents']);
    Route::get('/groups/not-assinged-tasks/{id}', [GroupController::class, 'notAssingedTasks']);
    Route::get('/groups/not-attached-teachers/{id}', [GroupController::class, 'notAttachedTeachers']);

    // teachers
    Route::put('/teachers/attach-user/{id}', [TeacherController::class, 'attachUser']);
    Route::put('/teachers/change-groups/{id}', [TeacherController::class, 'changeGroups']);
    Route::put('/teachers/change-subjects/{id}', [TeacherController::class, 'changeSubjects']);

    // students 
    Route::get('/students/ratings', [StudentController::class, 'studentsRatings']);
    Route::put('/students/attach-user/{id}', [StudentController::class, 'attachUser']);
    Route::put('/students/change-groups/{id}', [StudentController::class, 'changeGroups']);
    Route::post('/students/generate-students-csv-file', [StudentController::class, 'generateStudentsCsvFile']);

    Route::put('/students/assessing-student-tasks/{id}', [StudentController::class, 'assessingStudentTasks']);


    // actions end

    // CRUD
    Route::apiResources([
        'roles' => RoleController::class,
        'users' => UserController::class,
        'tasks' => TaskController::class,
        'groups' => GroupController::class,
        'students' => StudentController::class,
        'teachers' => TeacherController::class,
        'subjects' => SubjectController::class,
        'user-types' => UserTypeController::class,
    ]);
});
