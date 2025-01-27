<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Resources\StudentResource;
use App\Http\Resources\StudentTaskRatingsResource;
use App\Models\Student;
use App\Models\StudentTaskRatings;
use App\Models\User;
use App\Rules\GroupCapacity;
use App\Rules\ValidUser;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index()
    {
        $students = StudentResource::collection(Student::all());
        return response()->json($students, 200);
    }

    public function paginate(Request $request)
    {
        $request->validate([
            'per_page' => 'integer',
            'page' => 'integer',
            'sort_by' => 'string',
            'sort_direction' => 'string|in:asc,desc',
        ]);

        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $sortBy = $request->query('sort_by', 'id');
        $sortDirection = $request->query('sort_direction', 'desc');

        $query = Student::query();

        if ($request->has('first_name')) {
            $first_name = $request->query('first_name');
            $query->where('first_name', 'like', "%$first_name%");
        }

        if ($request->has('last_name')) {
            $last_name = $request->query('last_name');
            $query->where('last_name', 'like', "%$last_name%");
        }

        if ($request->has('phone')) {
            $phone = $request->query('phone');
            $query->where('phone', 'like', "%$phone%");
        }

        if ($request->has('group_id')) {
            $group_id = $request->query('group_id');
            $query->whereHas('groups', function ($query) use ($group_id) {
                $query->where('group_id', '=', $group_id);
            });
        }

        if ($request->has('task_id')) {
            $task_id = $request->query('task_id');
            $query->whereHas('groups.tasks', function ($query) use ($task_id) {
                $query->where('task_id', '=', $task_id);
            });
        }

        if ($request->has('teacher_id')) {
            $teacher_id = $request->query('teacher_id');
            $query->whereHas('groups.teachers', function ($query) use ($teacher_id) {
                $query->where('teacher_id', '=', $teacher_id);
            });
        }

        $students = $query->with([
            'groups',
            'groups.subject:id,name',
            'user'
        ])
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json(
            [
                'data' => StudentResource::collection($students),
                'total' => $students->total(),
                'last_page' => $students->lastPage(),
                'current_page' => $students->currentPage(),
                'per_page' => $students->perPage()
            ],
            200
        );
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

    public function store(StoreStudentRequest $request)
    {
        try {
            $student = Student::create($request->only([
                'first_name',
                'last_name',
                'phone',
                'user_id'
            ]));

            $student->groups()->attach($request->groups);
            $student->user()->update(['is_attach' => true]);

            return response()->json(new StudentResource($student->load([
                'groups',
                'groups.subject:id,name',
                'user'
            ])), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(StoreStudentRequest $request, $id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json(['message' => 'student not found'], 404);
        }

        try {
            $student->update($request->only([
                'first_name',
                'last_name',
                'phone',
                'user_id'
            ]));
            $student->groups()->sync($request->groups);
            return response()->json(new StudentResource($student->load([
                'groups',
                'groups.subject:id,name',
                'user'
            ])), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function attachUser(Request $request, $id)
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

        try {
            $student->user_id = $request->user_id;
            $student->save();

            $student->user()->update(['is_attach' => true]);

            return response()->json(new StudentResource($student->load(['user'])), 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function changeGroups(Request $request, $id)
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

        try {
            $student->groups()->sync($request->groups);
            return response()->json(new StudentResource($student->load([
                'groups',
                'groups.subject:id,name'
            ])), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function assessingStudentTasks(Request $request, $id)
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

        try {
            if ($exists) {
                $student->ratings()->updateExistingPivot($request->task_id, ['score_got' => $request->score_got, 'rated_by' => $rated_by, 'total_score' => $totalScore]);
            } else {
                $student->ratings()->attach($request->task_id, ['score_got' => $request->score_got, 'rated_by' => $rated_by, 'total_score' => $totalScore]);
            }

            return response()->json(['message' => 'success'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function studentsRatings(Request $request)
    {
        $request->validate([
            'per_page' => 'integer',
            'page' => 'integer',
            'sort_by' => 'string',
            'sort_direction' => 'string|in:asc,desc',
        ]);

        $perPage = $request->query('per_page',  10);
        $page = $request->query('page', 1);
        $sortBy = $request->query('sort_by', 'id');
        $sortDirection = $request->query('sort_direction', 'desc');

        $query = StudentTaskRatings::query();

        if ($request->has('student_id')) {
            $student_id = $request->query('student_id');
            $query->where('student_id', $student_id);
        }

        if ($request->has('task_id')) {
            $task_id = $request->query('task_id');
            $query->where('task_id', $task_id);
        }

        if ($request->has('teacher_id')) {
            $teacher_id = $request->query('teacher_id');
            $query->where('rated_by', $teacher_id);
        }

        if ($request->has('score_got')) {
            $score_got = $request->query('score_got');
            $query->where('score_got', $score_got);
        }

        if ($request->has('total_score')) {
            $total_score = $request->query('total_score');
            $query->where('total_score', $total_score);
        }

        if ($request->has('group_id')) {
            $group_id = $request->query('group_id');
            $query->whereHas('student.groups', function ($query) use ($group_id) {
                $query->where('group_id', $group_id);
            });
        }

        $studentsRatings = $query->with(['student', 'task', 'teacher'])
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage, ['*'], 'page', $page);
        return response()->json(StudentTaskRatingsResource::collection($studentsRatings), 200);
    }
}
