<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{

    use HasFactory;
    protected $fillable = [
        'subject_id',
        'name',
        'description',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class);
    }

    public  function studentsRating(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_task_ratings', 'task_id', 'student_id');
    }
}
