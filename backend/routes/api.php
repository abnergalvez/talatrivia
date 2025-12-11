<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->group(['prefix' => 'api'], function () use ($router) {

    // Rutas Auth no autenticado
    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');

    // Rutas protegidas
    $router->group(['middleware' => 'auth'], function () use ($router) {

        // Rutas Auth ya autenticado
        $router->post('logout', 'AuthController@logout');
        $router->get('me', 'AuthController@me');

        // Solo solo "admin"
        $router->group(['middleware' => 'role:admin'], function () use ($router) {
            $router->get('users', 'UserController@index');

            // Rutas CRUD para administración de trivias

            // Rutas CRUD para administración de preguntas y opciones

            // Rutas CRUD para administración de niveles

            // Rutas CRUD para administración de roles

            // Rutas para visualizacion de respuestas y resultados de usuarios

            // Rutas para asignar trivias a usuarios

            // Ruta para asignar roles a usuarios
        });

        // Rutas  para "player" (o cualquier usuario autenticado)

        // Ver mis trivias asignadas y puntajes obtenidos

        // Entrar a una trivia y ver preguntas + opciones
        
        // Responder todas las preguntas de una trivia

        // Responder solo una pregunta

        // Ver mis respuestas y resultado final de una trivia

        // 
        $router->get('trivias', 'TriviaController@index');


    });
});

