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
	private $LastResponse = [];
	private $Request = NULL;
	private $Options = [
		'auto_refresh' => true,
		'auto_retry' => false
	];
	private $Scope = [
        'playlist-modify-public',
        'playlist-modify-private',
        'user-library-read',
        'user-read-private',
	];

	public function __construct($options = [])
	{
		//get credentials from .env
		$this->Session = new Session(getenv('CLIENT_ID'), getenv('CLIENT_SECRET'), SpotifyApi::REDIRECT_URI, $this->Scope);
		
		$this->setOptions($options);

		$this->Request = new Request();
	}

	public function SendRequest($method, $url, $parameters = [], $headers = [])
	{
		$headers = $this->AuthHeader($headers);
		try
		{
			$results =  $this->Request->Api($method, $url, $parameters, $headers);
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

	public function AuthHeader($headers = [])
	{
		$headers['Authorization'] = 'Bearer ' . $this->Session->getAccessToken();
		return $headers;
	}

	public function RequestUrlAuth($showDialog = true)
	{
		return $this->Session->RequestUrlAuth(['show_dialog'=>$showDialog]);
	}

	public function RequestToken($code)
	{
		$this->Session->RequestToken($code);
	}

	public function SaveSession()
	{
		$_SESSION[SpotifyApi::SESSION] = serialize($this);
	}

	public static function GetSaveSession()
	{
		return unserialize($_SESSION[SpotifyApi::SESSION]);
	}

	public function CreateNewPlaylist($iduser, $name, $description = '')
	{
		$url = '/v1/users/'. $iduser .'/playlists';

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

	public function getTracks($tracks)
	{
		$url = '/v1/tracks?ids=' . implode(',', $tracks);

		return $this->SendRequest('get', $url);
	}

	public function getTrack($track)
	{
		$url = '/v1/tracks/'. $track;

		return $this->SendRequest('get', $url);
	}
	public function Me()
	{
		return $this->SendRequest('get', '/v1/me');
	}

	public function setSession($session)
	{
		$this->Session = $session;
	}

	public function setOptions($opts)
	{
		$this->Options += $opts;
	}

	public function getSession()
	{
		return $this->Session;
	}
	
}

 ?>