<?php

namespace App\Actions\PlayerTrivia;

use App\Models\User;
use App\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;

class AllTriviasRankingAction
{
    public function execute()
    {
        $userAuth = app('user');
        
        if (!$userAuth) {
            throw new BusinessRuleException("User not authenticated.", 401);
        }

        // 1. Obtener TODOS los usuarios asignados a alguna trivia
        $assignedUsers = DB::table('trivia_user')
            ->join('users', 'trivia_user.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'users.email')
            ->distinct()
            ->get();

        // 2. Trivias asignadas por usuario
        $assignedTrivias = DB::table('trivia_user')
            ->select('user_id', DB::raw('GROUP_CONCAT(trivia_id) as assigned'))
            ->groupBy('user_id')
            ->get()
            ->mapWithKeys(function ($row) {
                return [
                    $row->user_id => $row->assigned
                        ? array_map('intval', explode(',', $row->assigned))
                        : []
                ];
            });

        // 3. Datos de trivias completadas
        $completedData = DB::table('trivia_user')
            ->select(
                'users.id',
                DB::raw('SUM(trivia_user.score) as total_score'),
                DB::raw('COUNT(trivia_user.trivia_id) as trivias_completed'),
                DB::raw('GROUP_CONCAT(trivia_user.trivia_id) as trivias_completed_id')
            )
            ->join('users', 'trivia_user.user_id', '=', 'users.id')
            ->whereNotNull('trivia_user.completed_at')
            ->groupBy('users.id')
            ->get()
            ->mapWithKeys(function ($row) {
                return [
                    $row->id => [
                        'total_score' => (int) $row->total_score,
                        'trivias_completed' => (int) $row->trivias_completed,
                        'trivias_completed_id' =>
                            $row->trivias_completed_id
                                ? array_map('intval', explode(',', $row->trivias_completed_id))
                                : []
                    ]
                ];
            });

        // 4. Construir ranking incluyendo trivias restantes
        $fullRanking = $assignedUsers->map(function ($user) use ($assignedTrivias, $completedData) {

            $assigned = $assignedTrivias[$user->id] ?? [];
            $completed = $completedData[$user->id]['trivias_completed_id'] ?? [];

            $remaining = array_values(array_diff($assigned, $completed));

            if (isset($completedData[$user->id])) {
                $data = $completedData[$user->id];

                return [
                    'position' => null,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'total_score' => $data['total_score'],
                    'trivias_completed' => $data['trivias_completed'],
                    'trivias_completed_id' => $completed,
                    'trivias_remaining_id' => $remaining,
                ];
            }

            // Usuario sin ninguna trivia completada
            return [
                'position' => null,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'total_score' => 0,
                'trivias_completed' => 0,
                'trivias_completed_id' => [],
                'trivias_remaining_id' => $assigned,
            ];
        });

        // 5. Ordenar ranking correctamente (UNA sola operación)
        $sorted = $fullRanking->sort(function ($a, $b) {

            // Primero: usuarios con trivias completadas van arriba
            if ($a['trivias_completed'] > 0 && $b['trivias_completed'] === 0) return -1;
            if ($b['trivias_completed'] > 0 && $a['trivias_completed'] === 0) return 1;

            // Segundo: ordenar por score descendente
            if ($a['total_score'] !== $b['total_score']) {
                return $b['total_score'] <=> $a['total_score'];
            }

            // Tercero: ordenar por cantidad de trivias completadas
            return $b['trivias_completed'] <=> $a['trivias_completed'];
        })->values();

        // 6. Asignar posiciones solo a los que completaron algo
        $pos = 1;

        $sorted = $sorted->map(function ($row) use (&$pos) {
            if ($row['trivias_completed'] > 0) {
                $row['position'] = $pos++;
            }
            return $row;
        });

        // 7. Información del usuario autenticado
        $userRow = $sorted->firstWhere('user.id', $userAuth->id);

        return response()->json([
            'ranking' => $sorted,
            'total_participants' => $fullRanking->count(),
            'your_position' => $userRow['position'] ?? null,
            'your_total_score' => $userRow['total_score'] ?? 0,
            'your_trivias_completed' => $userRow['trivias_completed'] ?? 0,
            'your_trivias_completed_id' => $userRow['trivias_completed_id'] ?? [],
            'your_trivias_remaining_id' => $userRow['trivias_remaining_id'] ?? [],
        ], 200);
    }
}
