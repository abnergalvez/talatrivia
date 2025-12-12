<?php

namespace App\Actions\User;

use App\Exceptions\BusinessRuleException;

class AssignedTriviasAction
{
    public function execute()
    {
        $userAuth = app('user');
        
        if (!$userAuth) {
            throw new BusinessRuleException("User not authenticated.", 401);
        }

        // Obtener trivias asignadas al usuario con información de pivot
        $trivias = $userAuth->trivias()
            ->withCount('questions')
            ->get()
            ->map(function ($trivia) {
                return [
                    'id' => $trivia->id,
                    'name' => $trivia->name,
                    'description' => $trivia->description,
                    'is_active' => $trivia->is_active,
                    'questions_order' => $trivia->questions_order,
                    'time_option' => $trivia->time_option,
                    'time' => $trivia->time,
                    'questions_count' => $trivia->questions_count,
                    'score' => $trivia->pivot->score ?? null,
                    'completed_at' => $trivia->pivot->completed_at,
                    'status' => $trivia->pivot->completed_at ? 'completed' : 'pending',
                    'created_at' => $trivia->created_at,
                    'updated_at' => $trivia->updated_at,
                ];
            });

        return response()->json([
            'trivias' => $trivias,
            'total_trivias' => $trivias->count()
        ], 200);
    }
}