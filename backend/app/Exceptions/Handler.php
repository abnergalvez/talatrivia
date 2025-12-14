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
        // Validación
        if ($e instanceof ValidationException) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);
        }

        // Modelo no encontrado
        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return response()->json(['error' => 'Resource not found'], 404);
        }

        // Error de autenticación
        if ($e instanceof AuthenticationException) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Error en base de datos
        if ($e instanceof QueryException) {
            return response()->json([
                'error' => 'Database error',
                'message' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }

        // Excepciones personalizadas
        if ($e instanceof BusinessRuleException ||
            $e instanceof DomainException ||
            $e instanceof AuthorizationException) {

            return response()->json([
                'error' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }

        // Errores desconocidos (500)
        return response()->json([
            'error' => 'Internal Server Error',
            'message' => env('APP_DEBUG') ? $e->getMessage() : null
        ], 500);
    }
}