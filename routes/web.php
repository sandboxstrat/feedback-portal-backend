<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

/*Routes required for Game Page*/
$router->group(['prefix' => 'api'], function () use ($router) {

    $router->get('games/{url}', ['uses' => 'GameController@getOneGameByUrlAndPublic']);

    $router->post('feedback',['uses' => 'FeedbackController@create']);

    $router->post('image', ['uses' => 'FeedbackController@uploadImage']);

    $router->get('option/game/{gameId}', ['uses' => 'OptionController@getOptionsByGameIdPublic']);

    $router->post('pros/user/cookie',['uses'=>'SaberProsApiController@getUserByCookie']);

    $router->get('pros/user/{authorizationCode}', ['uses' => 'SaberProsApiController@getUser']);

    $router->get('pros/user/login/{id}',['uses'=>'SaberProsApiController@getLoginState']);

    $router->delete('pros/user/login/{id}',['uses'=>'SaberProsApiController@deleteLoginState']);

    $router->post('pros/user/login',['uses'=>'SaberProsApiController@createLoginState']);

    $router->post('pros/user/logout',['uses'=>'SaberProsApiController@logOut']);
  
});

/*Admin section routes*/
$router->group(['prefix' => 'admin','middleware' => 'auth'], function () use ($router) {

    $router->get('games/url/{url}/{id}', ['uses'=>'GameController@checkGameUrl']);

    $router->get('games/{id}',  ['uses' => 'GameController@getOneGame']);

    $router->get('games',  ['uses' => 'GameController@getAllGames']);
    
    $router->post('games', ['uses' => 'GameController@create']);

    $router->post('image', ['uses' => 'GameController@uploadImage']);
  
    $router->delete('games/{id}', ['uses' => 'GameController@delete']);
  
    $router->put('games/{id}', ['uses' => 'GameController@update']);
    
    $router->get('feedback/latest',['uses' => 'FeedbackController@getLatestFeedback']);

    $router->get('feedback/count', ['uses' => 'FeedbackController@getFeedbackCountByDate']);

    $router->get('feedback', ['uses' => 'FeedbackController@getAllFeedback']);

    $router->get('feedback/{id}', ['uses' => 'FeedbackController@getFeedbackById']);

    $router->get('feedback/game/{gameId}', ['uses' => 'FeedbackController@getFeedbackByGame']);

    $router->put('feedback/{id}', ['uses' => 'FeedbackController@update']);

    $router->post('option', ['uses' => 'OptionController@create']);

    $router->get('option/game/{gameId}', ['uses' => 'OptionController@getOptionsByGameId']);

    $router->put('option/{id}', ['uses' => 'OptionController@update']);

    $router->delete('option/{id}', ['uses' => 'OptionController@delete']);

    $router->get('users', ['uses' => 'Auth0ManagementApiController@getAllUsers']);

    $router->get('users/{id}', ['uses' => 'Auth0ManagementApiController@getUser']);

    $router->post('users', ['uses' => 'Auth0ManagementApiController@createUser']);

    $router->put('users', ['uses' => 'Auth0ManagementApiController@updateUser']);

  });


