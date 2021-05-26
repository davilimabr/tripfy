<?php
require_once('vendor/autoload.php');
session_start();

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Classes\SpotifyApi;
use Classes\BingRouteApi;

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();

$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

//home
$app->get('/', function(Request $request, Response $response, $args){
   
    $response->getBody()->write(file_get_contents('views/inicio.html'));
    return $response;
});

//SpotifyAuthentication
$app->get('/auth', function(Request $request, Response $response, $args){
    
    $spotifyApi = new SpotifyApi();
    $url = $spotifyApi->RequestUrlAuth();

    sleep(1);
    $response->getBody()->write($url);
    return $response;
});

$app->get('/callback', function(Request $request, Response $response, $args){

    $spotifyApi = new SpotifyApi();
    $spotifyApi->RequestToken($_GET['code']);
    $spotifyApi->SaveSession();

    return $response->withHeader('Location', '/travel');
});

//travel
$app->get('/travel', function(Request $request, Response $response, $args){
    $response->getBody()->write(file_get_contents('views/rota-viagem.html'));
    return $response;
});

$app->post('/travel', function(Request $request, Response $response, $args){

    $start = $_POST['partida'];
    $end = $_POST['chegada'];

    $bingApi = new BingRouteApi($start, $end);
    $bingApi->SaveSession();

    sleep(1);
    return $response;
});

//playlist
$app->get('/create-playlist', function(Request $request, Response $response, $args){

    $bingApi = BingRouteApi::GetSaveSession();
    $spotifyApi = SpotifyApi::GetSaveSession();

    if(empty($bingApi) || empty($spotifyApi))
        return $response->withHeader('Location', '/');

    $tracks = $spotifyApi->getTracksSaved();
    $tracks = $tracks['body']['items'];
    shuffle($tracks);
    
    $duration_max = (int)$bingApi->TravelDuration;
    $sum = 0;
    $playlist_tripfy = [];

    foreach($tracks as $key => $value)
    {
        $track = [
            'id' => $value['track']['id'],
            'duration' => (int)$value['track']['duration_ms'] / 1000 
        ];
        array_push($playlist_tripfy, $track);
        $sum += (int)$track['duration'];

        if($sum >= $duration_max)
            break;
    }
    if($sum > $duration_max)
    {
        $duration_end = (int)end($playlist_tripfy)['duration'];
        $diference = $duration_max - ($sum - $duration_end);
        array_pop($playlist_tripfy);

        foreach($tracks as $value)
        {
            $duration_value = (int)$value['track']['duration_ms'] / 1000;
            if($duration_value == $diference || abs($diference - $duration_value) <= 60)
            {
                $track = [
                    'id' => $value['track']['id'],
                    'duration' => (int)$value['track']['duration_ms'] / 1000 
                ];
                array_push($playlist_tripfy, $track);
                break;
            }
        }
    }

    foreach($playlist_tripfy as &$track)
        $track = $track['id'];

    $description = "Boa Viagem!";
    $idPlaylist = $spotifyApi->CreateNewPlaylist('Playlist-Tripfy', $description);
    $spotifyApi->AddTracksInPlaylists($idPlaylist['body']['id'], $playlist_tripfy);

    $response->getBody()->write($idPlaylist['body']['id']);
    return $response;
});

$app->run();

?>