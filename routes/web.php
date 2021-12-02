<?php

$router->get('/', function() {
    return 'Lumen 8';
});

$router->group(['prefix' => 'api'], function ($router) {
    // Auth User
    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');
    $router->post('logout', 'AuthController@logout');
    $router->post('refresh', 'AuthController@refresh');
    $router->get('profile', 'AuthController@profile');

    // Product 
    $router->get('product', 'ProductController@results');
    $router->get('product/{id}', 'ProductController@view');
    $router->post('product/store', 'ProductController@store');
    $router->get('product/delete/{id}', 'ProductController@delete');

    // Transaction
    $router->get('transaction', 'TransactionController@results');
    $router->get('transaction/{id}', 'TransactionController@view');
    $router->post('transaction/store', 'TransactionController@store');
    $router->get('transaction/delete/{id}', 'TransactionController@delete');

    // Payment
    $router->get('payment', 'PaymentController@results');
    $router->get('payment/{id}', 'PaymentController@view');
    $router->post('payment/store', 'PaymentController@store');
});
