<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        // 1. Validación (422)
        if ($e instanceof ValidationException) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);
        }

        // 2. Modelo no encontrado (404)
        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return response()->json(['error' => 'Resource not found'], 404);
        }

        // 3. Error de autenticación (401)
        if ($e instanceof AuthenticationException) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // 4. Error en base de datos (sobre QueryException)
        if ($e instanceof QueryException) {
            return response()->json([
                'error' => 'Database error',
                'message' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }

        // 5. Excepciones personalizadas (tu propias reglas)
        if ($e instanceof BusinessRuleException ||
            $e instanceof DomainException ||
            $e instanceof AuthorizationException) {

            return response()->json([
                'error' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }

        // 6. Errores desconocidos (500)
        return response()->json([
            'error' => 'Internal Server Error',
            'message' => env('APP_DEBUG') ? $e->getMessage() : null
        ], 500);
    }
}