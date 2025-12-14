<?php

namespace App\Actions\PlayerTrivia;

use App\Models\Trivia;
use App\Exceptions\BusinessRuleException;

class TriviaRankingAction
{
    public function execute($triviaId)
    {
        $userAuth = app('user');
        
        if (!$userAuth) {
            throw new BusinessRuleException("User not authenticated.", 401);
        }

        $trivia = Trivia::findOrFail($triviaId);

        $assignedUsers = $trivia->users()->get();

        $completedUsers = $trivia->users()
            ->wherePivot('completed_at', '!=', null)
            ->orderByPivot('score', 'desc')
            ->orderByPivot('completed_at', 'asc')
            ->get();

        $completedMap = $completedUsers->map(function ($user, $index) {
            return [
                'id' => $user->id,
                'position' => $index + 1,
                'score' => $user->pivot->score,
                'completed_at' => $user->pivot->completed_at,
            ];
        })->keyBy('id');

        $fullRanking = $assignedUsers->map(function ($user) use ($completedMap) {

            if ($completedMap->has($user->id)) {
                $data = $completedMap[$user->id];
                return [
                    'position' => $data['position'],
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'score' => $data['score'],
                    'completed_at' => $data['completed_at'],
                ];
            }

            return [
                'position' => null,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'score' => 0,
                'completed_at' => null,
            ];
        });

        $userRow = $fullRanking->firstWhere('user.id', $userAuth->id);

        return response()->json([
            'trivia' => [
                'id' => $trivia->id,
                'name' => $trivia->name,
            ],
            'ranking' => $fullRanking,
            'total_participants' => $assignedUsers->count(),
            'your_position' => $userRow['position'],
            'your_score' => $userRow['score'],
        ], 200);
    }
}
