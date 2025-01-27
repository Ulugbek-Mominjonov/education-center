<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentTaskRatings extends Model
{

    protected $fillable = [
        'student_id',
        'task_id',
        'score_got',
        'total_score',
        'rated_by',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'rated_by');
    }
}
