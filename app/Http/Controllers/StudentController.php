<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginateRequest;
use App\Http\Requests\StoreStudentRequest;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    protected $studentRepository;

    public function __construct(StudentRepositoryInterface $studentRepository)
    {
        $this->studentRepository = $studentRepository;
    }
    public function index()
    {
        return $this->studentRepository->all();
    }

    public function paginate(PaginateRequest $request)
    {
        return $this->studentRepository->paginate($request);
    }

    public function show($id)
    {
        return $this->studentRepository->find($id);
    }

    public function store(StoreStudentRequest $request)
    {
        return $this->studentRepository->create($request->only([
            'first_name',
            'last_name',
            'phone',
            'user_id'
        ]));
    }

    public function update(StoreStudentRequest $request, $id)
    {
        return $this->studentRepository->update($id, $request->only([
            'first_name',
            'last_name',
            'phone',
            'user_id',
            'groups'
        ]));
    }

    public function attachUser(Request $request, $id)
    {
        return $this->studentRepository->attachUser($request, $id);
    }

    public function changeGroups(Request $request, $id)
    {
        return $this->studentRepository->changeGroups($request, $id);
    }

    public function assessingStudentTasks(Request $request, $id)
    {
        return $this->studentRepository->assessingStudentTasks($request, $id);
    }

    public function studentsRatings(PaginateRequest $request)
    {
        return $this->studentRepository->studentsRatings($request);
    }
}
