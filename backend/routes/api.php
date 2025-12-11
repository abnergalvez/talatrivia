<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->group(['prefix' => 'api'], function () use ($router) {

    // Registro público
    $router->post('register', 'AuthController@register');
    
    // Login
    $router->post('login', 'AuthController@login');

    // Rutas protegidas
    $router->group(['middleware' => 'auth'], function () use ($router) {

        // Solo admin puede modificar roles o crear admins
        $router->group(['middleware' => 'role:admin'], function () use ($router) {
            $router->post('users/assign-role', 'UserController@assignRole');
        });

        // Logout
        $router->post('logout', 'AuthController@logout');
    });
});

