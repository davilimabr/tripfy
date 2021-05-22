<?php
require_once('vendor/autoload.php');

use Slim\Slim;
use Classes\SpotifyApi;

session_start();

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();

$app = new Slim();

$app->config('debug', true);

$app->get('/', function(){
    
    $api = new SpotifyApi();
    $url = $api->RequestUrlAuth();

    header("Location: {$url}");
    exit;
});

$app->get('/callback', function(){

    $api = new SpotifyApi();
    $api->RequestToken($_GET['code']);
    $api->SaveSession();

    header("Location: /teste");
    exit;
});

$app->get('/teste', function(){
    $api = SpotifyApi::GetSaveSession();
    var_dump($api) ;
});

$app->run();

?>