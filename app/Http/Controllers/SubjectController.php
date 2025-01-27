<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        return response()->json(Subject::all(), 200);
    }

    public function paginate(Request $request)
    {
        $request->validate([
            'per_page' => 'integer',
            'page' => 'integer',
        ]);

        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);

        $subjects = Subject::paginate($perPage, ['*'], 'page', $page);

        return response()->json(
            $subjects,
            200
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        try {
            $subject = Subject::create($request->only(['name', 'description']));
            return response()->json($subject, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {

        $subject = Subject::find($id);

        if (!$subject) {
            return response()->json(['message' => 'subject not found'], 404);
        }

        return response()->json($subject, 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
        ]);
        $subject = Subject::find($id);

        if (!$subject) {
            return response()->json(['message' => 'subject not found'], 404);
        }

        try {
            $subject->update($request->only(['name', 'description']));
            return response()->json($subject, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $subject = Subject::find($id);

        if (!$subject) {
            return response()->json(['message' => 'subject not found'], 404);
        }

        try {
            $subject->delete();
            return response()->json(['message' => 'subject deleted'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
