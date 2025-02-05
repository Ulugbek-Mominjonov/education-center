<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentTaskRatingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'task_id' => $this->task_id,
            'student_name' => $this->whenLoaded('student', function () {
                return $this->student->first_name . ' ' . $this->student->last_name;
            }),
            'task_name' => $this->whenLoaded('task', function () {
                return $this->task->name;
            }),
            'score_got' => $this->score_got,
            'total_score' => $this->total_score,
            'deadline' => $this->whenLoaded('task', function () {
                return $this->task->groups()->where('task_id', $this->task_id)->pluck('deadline')->first();
            }),
            'rated_by' => $this->whenLoaded('teacher', function () {
                return $this->teacher->first_name . ' ' . $this->teacher->last_name;
            }),
        ];
    }
}
