<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->group('api', function($routes) {
    $routes->post('login', 'Api\Auth::login');
    $routes->post('login/confirm-otp', 'Api\Auth::login_otp');

    $routes->group('', ['filter' => 'auth'], function($routes) {
        $routes->get('hello', 'Api\Hello::index');
    });
});