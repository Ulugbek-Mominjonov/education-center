<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [
        'subject_id',
        'name',
        'label',
        'price',
        'description',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class);
    }
}
