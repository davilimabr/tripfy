<?php 

namespace Classes;

use Classes\SpotifyApi\Request;
use Classes\SpotifyApi\Session;
use Classes\SpotifyApi\SpotifyException;

class SpotifyApi
{
	const REDIRECT_URI = 'http://localhost/callback';
	const SESSION = 'spotify_session';
	private $Session = NULL;
	private $Request = NULL;
	public $IdUser = "";
	public $LastResponse = [];
	public $Options = [
		'auto_refresh' => true,
		'auto_retry' => false
	];
	public $Scope = [
		'playlist-read-private',
        'playlist-read-collaborative',
        'playlist-modify-public',
        'playlist-modify-private',
        'user-library-read',
        'user-read-private',
        'user-read-email'
	];

	public function __construct($options = [])
	{
		$this->Session = new Session(getenv('CLIENT_ID'), getenv('CLIENT_SECRET'), SpotifyApi::REDIRECT_URI, $this->Scope); //get credentials from .env
		$this->setOptions($options);
		$this->Request = new Request();
	}

	public function SendRequest($method, $url, $parameters = [], $headers = [])
	{
		$headers = $this->AuthHeader($headers);
		try
		{
			$results =  $this->Request->Api($method, $url, $parameters, $headers);

			$this->LastResponse = $results;
			return $results;
		}
		catch (SpotifyException $e)
		{
			if($this->Options['auto_refresh'] && $e->HasExpiredToken())
			{
				$this->Session->RefreshToken();

				return $this->SendRequest($method, $url, $parameters, $headers);
			}
			else if ($this->Options['auto_retry'])
			{	
				sleep(3);
				return $this->SendRequest($method, $url, $parameters, $headers);
			}
			else
				throw $e;
		}
	}
	
	public function RequestUrlAuth($showDialog = true)
	{
		return $this->Session->RequestUrlAuth(['show_dialog'=>$showDialog]);
	}

	public function RequestToken($code)
	{
		$this->Session->RequestToken($code);
		
		$this->setIdUser();
	}

	public function CreateNewPlaylist($name, $description = '')
	{
		$url = '/v1/users/'. $this->IdUser .'/playlists';

		$parameters = [
			'name'=>$name,
			'public'=>false,
			'description'=>$description
		];

		$headers = [
			'Content-Type' => 'application/json'
		];

		return $this->SendRequest('post', $url, json_encode($parameters), $headers);
	}

	public function AddTracksInPlaylists($idplaylist, $tracks = [])
	{
		$url = '/v1/playlists/'.$idplaylist.'/tracks';

		$headers = [
			'Content-Type'=>'application/json'
		];

		$tracks = $this->PreperTracks($tracks);

		return $this->SendRequest('post', $url, json_encode($tracks), $headers);
	}

	public function getTracksSaved($limit = 50)
	{
		$parameters = [
			'limit'=> $limit
		];

		return $this->SendRequest('get', '/v1/me/tracks', $parameters);
	}

	private function PreperTracks($tracks = [])
	{
		foreach ($tracks as &$value)
		{
			$value = 'spotify:track:'.$value;
		}

		return $tracks;
	}

	private function AuthHeader($headers = [])
	{
		$headers['Authorization'] = 'Bearer ' . $this->Session->getAccessToken();
		return $headers;
	}

	public function setOptions($opts)
	{
		$this->Options += $opts;
	}

	public function setIdUser()
	{
		$user = $this->SendRequest('get', '/v1/me');
		$this->IdUser = $user['body']['id'];
	}

	public function SaveSession()
	{
		if(session_status() !== PHP_SESSION_ACTIVE)
            session_start();
			
		$_SESSION[SpotifyApi::SESSION] = serialize($this);
	}

	public static function GetSaveSession()
	{
		return unserialize($_SESSION[SpotifyApi::SESSION]);
	}
}
?>