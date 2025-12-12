<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trivia extends Model
{
    protected $table = 'trivia';
    
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'questions_order',
        'time_option',
        'time'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'time' => 'integer'
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
                    ->withPivot('score', 'completed_at')
                    ->withTimestamps();
    }

}
