<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class User extends Model implements AuthenticatableContract
{
    use Authenticatable, Authorizable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'api_token',
        'role_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_token'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function trivias()
    {
        return $this->belongsToMany(Trivia::class)
                    ->withPivot('score', 'completed_at')
                    ->withTimestamps();
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function isAdmin()
    {
        return $this->role && $this->role->name === 'admin';
    }

    public function isPlayer()
    {
        return $this->role && $this->role->name === 'player';
    }

}
