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
            // Rutas CRUD para administración de usuarios
            $router->get('users', 'UserController@index');
            $router->post('users', 'UserController@store');
            $router->get('users/{id}', 'UserController@show');
            $router->put('users/{id}', 'UserController@update');
            $router->delete('users/{id}', 'UserController@destroy');
            
            // Rutas CRUD para administración de roles
            $router->get('roles', 'RoleController@index');
            $router->post('roles', 'RoleController@store');
            $router->get('roles/{id}', 'RoleController@show');
            $router->put('roles/{id}', 'RoleController@update');
            $router->delete('roles/{id}', 'RoleController@destroy'); // validacion de existencia en usuarios.

            // Rutas CRUD para administración de trivias
            $router->get('trivias', 'TriviaController@index');
            $router->post('trivias', 'TriviaController@store');
            $router->get('trivias/{id}', 'TriviaController@show');
            $router->put('trivias/{id}', 'TriviaController@update');
            $router->delete('trivias/{id}', 'TriviaController@destroy'); // borrado de cascada y validacion de existencia en respuestas.
            $router->get('trivias/{id}/users', 'TriviaController@triviaUsers'); // usuarios asignados a la trivia

            // Rutas para asignar trivias a usuarios
            $router->post('trivias/{id}/assign', 'TriviaController@assignToUsers'); // Asignacion por email o por id
            // Rutas para desasignar trivias a usuarios
            $router->post('trivias/{id}/unassign', 'TriviaController@unassignFromUsers');

            // Rutas CRUD para administración de niveles
            $router->get('levels', 'LevelController@index');
            $router->post('levels', 'LevelController@store');
            $router->get('levels/{id}', 'LevelController@show');
            $router->put('levels/{id}', 'LevelController@update');
            $router->delete('levels/{id}', 'LevelController@destroy'); // borrado de cascada y validacion de existencia en preguntas.

            // Rutas CRUD para administración de preguntas y opciones
            $router->get('trivias/{triviaId}/questions', 'QuestionController@index');
            $router->post('trivias/{triviaId}/questions', 'QuestionController@store');
            $router->post('trivias/{triviaId}/questions_bulk', 'QuestionController@bulkStore'); // carga masiva de preguntas y opciones
            $router->get('questions/{id}', 'QuestionController@show');
            $router->put('questions/{id}', 'QuestionController@update');
            $router->delete('questions/{id}', 'QuestionController@destroy'); // borrado de cascada y validacion de existencia en respuestas.

            // Rutas para visualizacion de respuestas y resultados de usuarios
            $router->get('trivias/{id}/answers', 'TriviaController@responses'); //
            $router->get('trivias/{id}/answers/{user_id}', 'TriviaController@userResponses');

            // Ruta para asignar rol admin a usuario
            $router->post('users/{id}/role_assign', 'UserController@assignRole');
        });

        // ==========================================
        // Rutas PLAYER - Todos los usuarios autenticados
        // ==========================================
        
        // UserController - Trivias asignadas
        $router->get('assigned_trivias', 'UserController@assignedTrivias');
        
        // PlayerTriviaController - Interacción con trivias
        $router->get('trivias/{id}/get_full', 'PlayerTriviaController@getFullTrivia');
        $router->post('trivias/{id}/answer_all', 'PlayerTriviaController@answerAllQuestions');
        $router->get('trivias/{id}/my_answers', 'PlayerTriviaController@myAnswers');
        $router->get('trivias/{id}/ranking', 'PlayerTriviaController@ranking');
        $router->get('all_trivias_ranking', 'PlayerTriviaController@allTriviasRanking');
        
        // PlayerQuestionController - Responder preguntas individuales
        $router->post('questions/{id}/answer', 'PlayerQuestionController@answerQuestion');

    });
});

