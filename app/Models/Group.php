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
}
