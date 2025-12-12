<?php

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
{
    public function handle($request, Closure $next)
    {
        // El origen es tu frontend: http://localhost:8083 (por tu docker-compose)
        $origin = $request->header('Origin') ?? '*';

        $headers = [
            'Access-Control-Allow-Origin'      => $origin,
            'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => '86400',
            'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With'
        ];

        // 1. Manejar la solicitud OPTIONS (preflight request)
        if ($request->isMethod('OPTIONS')) {
            return response()->json('{"method":"OPTIONS"}', 200, $headers);
        }

        // 2. Ejecutar la solicitud real y añadir las cabeceras
        $response = $next($request);

        // Asegúrate de que la respuesta tenga las cabeceras CORS
        foreach ($headers as $key => $value) {
            // Usamos header() para evitar problemas con respuestas JSON
            $response->header($key, $value);
        }

        return $response;
    }
}