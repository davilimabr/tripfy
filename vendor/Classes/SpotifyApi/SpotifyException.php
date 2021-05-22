<?php 
namespace Classes\SpotifyApi;

class SpotifyException extends \Exception
{
	const EXPIRED_TOKEN = 'The access token expired'; 

	public function HasExpiredToken()
	{
		return $this->getMessage() === SpotifyException::EXPIRED_TOKEN;
	}
}



?>
