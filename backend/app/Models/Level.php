<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $table = 'levels';

    protected $fillable = [
        'name', 'points',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

}
