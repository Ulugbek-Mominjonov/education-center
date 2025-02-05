<?php

namespace App\Repositories;

use App\Http\Resources\StudentResource;
use App\Http\Resources\StudentTaskRatingsResource;
use App\Models\Student;
use App\Models\StudentTaskRatings;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use App\Rules\GroupCapacity;
use App\Rules\ValidUser;
use Illuminate\Support\Facades\DB;

class StudentRepository extends BaseRepository implements StudentRepositoryInterface
{
  public function __construct(Student $student)
  {
    parent::__construct($student, StudentResource::class);
  }

  public function paginate($request)
  {
    $perPage = $request->query('per_page', 10);
    $page = $request->query('page', 1);
    $sortBy = $request->query('sort_by', 'id');
    $sortDirection = $request->query('sort_direction', 'desc');

    $query = Student::query()
      ->when($request->query('first_name'), function ($query, $first_name) {
        $query->where('first_name', 'like', "%$first_name%");
      })
      ->when($request->query('last_name'), function ($query, $last_name) {
        $query->where('last_name', 'like', "%$last_name%");
      })
      ->when($request->query("phone"), function ($query, $phone) {
        $query->where("phone", "like", "%$phone%");
      })
      ->when($request->query('group_id'), function ($query, $group_id) {
        $query->whereHas("groups", function ($query) use ($group_id) {
          $query->where("group_id", '=',  $group_id);
        });
      })
      ->when($request->query('task_id'), function ($query, $task_id) {
        $query->whereHas("groups.tasks", function ($query) use ($task_id) {
          $query->where("task_id", '=', $task_id);
        });
      })
      ->when($request->query('teacher_id'), function ($query, $teacher_id) {
        $query->whereHas("groups.teachers", function ($query) use ($teacher_id) {
          $query->where("teacher_id", '=', $teacher_id);
        });
      })
      ->with([
        'groups:id,name,subject_id',
        'groups.subject:id,name',
        'user'
      ])
      ->orderBy($sortBy, $sortDirection)
      ->simplePaginate($perPage, ['*'], 'page', $page);
    return StudentResource::collection($query);
  }

  public function show($id)
  {
    $student = Student::with([
      'groups:groups.id,groups.name,groups.subject_id',
      'groups.subject:id,name',
      'user'
    ])->find($id);

    if (!$student) {
      return response()->json(['message' => 'user not found'], 404);
    }
    return response()->json(new StudentResource($student), 200);
  }

  public function create(array $attributes)
  {
    $student = Student::create($attributes);
    $student->groups()->attach($attributes['groups']);
    $student->user()->update(['is_attach' => true]);
    return response()->json(new StudentResource($student), 201);
  }

  public function update($id, array $attributes)
  {
    $student = Student::find($id);

    if (!$student) {
      return response()->json(['message' => 'student not found'], 404);
    }

    $student->update($attributes);
    $student->groups()->sync($attributes['groups']);
    return response()->json(new StudentResource($student), 200);
  }

  public function attachUser($request, $id)
  {
    $request->validate([
      'user_id' => [
        'required',
        'integer',
        new ValidUser,
      ],
    ]);
    $student = Student::find($id);

    if (!$student) {
      return response()->json(['message' => 'student not found'], 404);
    }

    if ($student->user_id) {
      return response()->json(['message' => 'student already attached'], 400);
    }

    $student->user_id = $request->user_id;
    $student->save();
    $student->user()->update(['is_attach' => true]);
    return response()->json(new StudentResource($student), 200);
  }

  public function changeGroups($request, $id)
  {
    $request->validate([
      'groups' => [
        'required',
        'array',
        new GroupCapacity
      ]
    ]);
    $student = Student::find($id);

    if (!$student) {
      return response()->json(['message' => 'student not found'], 404);
    }

    $student->groups()->sync($request->groups);
    return response()->json(new StudentResource($student));
  }

  public function assessingStudentTasks($request, $id)
  {
    $request->validate([
      'task_id' => 'required|integer|exists:tasks,id',
      'score_got' => 'required|integer',
    ]);

    $student = Student::find($id);
    $isExistTaskForStudentGroups = $student->groups()->whereHas('tasks', function ($query) use ($request) {
      $query->where('task_id', $request->task_id);
    })->exists();

    if (!$isExistTaskForStudentGroups) {
      return response()->json(['message' => 'task not found in this student groups'], 400);
    }

    $group_id = $student->groups()->whereHas('tasks', function ($query) use ($request) {
      $query->where('task_id', $request->task_id);
    })->first()->id;

    $totalScore = DB::table('group_task')->where('group_id', $group_id)->where('task_id', $request->task_id)->first()->total_score;

    $sessionUser = $request->user();

    if (!$student) {
      return response()->json(['message' => 'student not found'], 404);
    }

    if (is_null($sessionUser->teacher)) {
      return response()->json(['message' => 'You cannot perform this action.'], 400);
    }


    $rated_by = $sessionUser->teacher->id;
    $exists = $student->ratings()->where('task_id', $request->task_id)->exists();

    if ($exists) {
      $student->ratings()->updateExistingPivot($request->task_id, ['score_got' => $request->score_got, 'rated_by' => $rated_by, 'total_score' => $totalScore]);
    } else {
      $student->ratings()->attach($request->task_id, ['score_got' => $request->score_got, 'rated_by' => $rated_by, 'total_score' => $totalScore]);
    }

    return response()->json(['message' => 'success'], 201);
  }

  public function studentsRatings($request)
  {
    $perPage = $request->query('per_page',  10);
    $page = $request->query('page', 1);
    $sortBy = $request->query('sort_by', 'id');
    $sortDirection = $request->query('sort_direction', 'desc');

    $query = StudentTaskRatings::query()
      ->when($request->query('student_id'), function ($query, $studentId) {
        $query->where('student_id', $studentId);
      })
      ->when($request->query('task_id'), function ($query, $taskId) {
        $query->where('task_id', $taskId);
      })
      ->when($request->query('teacher_id'), function ($query, $teacherId) {
        $query->where('rated_by', $teacherId);
      })
      ->when($request->query('score_got'), function ($query, $scoreGot) {
        $query->where('score_got', $scoreGot);
      })
      ->when($request->query('total_score'), function ($query, $totalScore) {
        $query->where('total_score', $totalScore);
      })
      ->when($request->query('group_id'), function ($query, $groupId) {
        $query->whereHas('student.groups', function ($query) use ($groupId) {
          $query->where('group_id', $groupId);
        });
      })
      ->with(['student', 'task', 'teacher'])
      ->orderBy($sortBy, $sortDirection)
      ->simplePaginate($perPage, ['*'], 'page', $page);

    return response()->json(StudentTaskRatingsResource::collection($query), 200);
  }
}
