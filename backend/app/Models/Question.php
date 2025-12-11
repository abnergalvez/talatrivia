<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $table = 'questions';

    protected $fillable = [
        'trivia_id',
        'level_id',
        'description',
        'time',
    ];

    public function trivia()
    {
        return $this->belongsTo(Trivia::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function options()
    {
        return $this->hasMany(Option::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }


}
