<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{

    use HasFactory;
    protected $fillable = [
        'name',
        'description',
    ];

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class);
    }

    public  function studentsRating(): BelongsToMany
    {
        return $this->belongsToMany(Student::class);
    }
}
