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
            $router->get('trivias', 'TriviaController@index');
            $router->post('trivias', 'TriviaController@store');
            $router->get('trivias/{id}', 'TriviaController@show');
            $router->put('trivias/{id}', 'TriviaController@update');
            $router->delete('trivias/{id}', 'TriviaController@destroy'); // borrado de cascada y validacion de existencia en respuestas.
            $router->get('trivias_users/{id}', 'TriviaController@triviaUsers');

            // Rutas CRUD para administración de preguntas y opciones
            $router->get('trivias/{id}/questions', 'QuestionController@index');
            $router->post('trivias/{id}/questions', 'QuestionController@store');
            $router->get('questions/{id}', 'QuestionController@show');
            $router->put('questions/{id}', 'QuestionController@update');
            $router->delete('questions/{id}', 'QuestionController@destroy'); // borrado de cascada y validacion de existencia en respuestas.

            // Rutas CRUD para administración de niveles
            $router->get('levels', 'LevelController@index');
            $router->post('levels', 'LevelController@store');
            $router->get('levels/{id}', 'LevelController@show');
            $router->put('levels/{id}', 'LevelController@update');
            $router->delete('levels/{id}', 'LevelController@destroy'); // borrado de cascada y validacion de existencia en preguntas.

            // Rutas CRUD para administración de roles
            $router->get('roles', 'RoleController@index');
            $router->post('roles', 'RoleController@store');
            $router->get('roles/{id}', 'RoleController@show');
            $router->put('roles/{id}', 'RoleController@update');
            $router->delete('roles/{id}', 'RoleController@destroy'); // validacion de existencia en usuarios.

            // Rutas para visualizacion de respuestas y resultados de usuarios
            $router->get('trivias/{id}/responses', 'TriviaController@responses');
            $router->get('trivias/{id}/responses/{user_id}', 'TriviaController@userResponses');

            // Rutas para asignar trivias a usuarios
            $router->post('trivias/{id}/assign', 'TriviaController@assignToUsers'); // Asignacion por email
            
            // Ruta para asignar rol admin a usuario
            $router->post('users/{id}/role_assign', 'UserController@assignRole');
        });

        //##### Rutas  para "player" (o cualquier usuario autenticado) ################

        // Ver mis trivias asignadas y puntajes obtenidos
        $router->get('assigned_trivias', 'UserController@assignedTrivias'); // puntaje = null a las no completadas. 

        // Entrar a una trivia y ver preguntas + opciones
        $router->get('trivias/{id}/get_full', 'TriviaController@getFullTrivia'); // trivia + preguntas + opciones
        
        // Responder todas las preguntas de una trivia
        $router->post('trivias/{id}/answer_all', 'TriviaController@answerAllQuestions');

        // Responder solo una pregunta
        $router->post('questions/{id}/answer', 'QuestionController@answerQuestion');

        // Ver mis respuestas y resultado final de una trivia
        $router->get('trivias/{id}/my_answers', 'TriviaController@myAnswer');

        // Ver el ranking general de una trivia (todos los usuarios)
        $router->get('trivias/{id}/ranking', 'TriviaController@ranking');

        // Ver el ranking general de todas las trivias (todos los usuarios)
        $router->get('all_trivias_ranking', 'TriviaController@allTriviasRanking');

    });
});

