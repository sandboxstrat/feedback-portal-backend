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

$router->group(['prefix' => 'api'], function () use ($router) {

    $router->get('games/{url}', ['uses' => 'GameController@getOneGameByUrlAndPublic']);

    $router->post('feedback',['uses' => 'FeedbackController@create']);

    $router->post('image', ['uses' => 'FeedbackController@uploadImage']);
  
});

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

  });


