<?php 

namespace Classes\SpotifyApi;

use Classes\SpotifyApi\Request; 

class Session
{
	private $AccessToken = '';
	private $RefreshToken = '';
	private $Client_ID = '';
	private $Client_Secret = '';
	private $Request = NULL;
	private $Redirect_url = '';
	private $Scope = [];
	private $ExpirationTime = 0;

	public function __construct($client_id, $client_secret, $redirect_url, $scope = [])
	{
		$this->setClient_ID($client_id);
		$this->setClient_Secret($client_secret);
		$this->setRedirect_url($redirect_url);
		$this->setScope($scope);

		$this->Request = new Request();
	}

	public function RequestUrlAuth($options = [])
	{
		$url = '/authorize';
		$parameters = [
			'response_type'=>'code',
			'client_id'=>$this->getClient_ID(),
			'redirect_uri'=>$this->getRedirect_url(),
			'scope'=>implode(' ', $this->getScope()),
			'show_dialog' =>($options['show_dialog']) ? 'true' : 'false'
		];

		return Request::ACCOUNT_URL. $url . '?' . http_build_query($parameters);
	}

	public function RequestToken($code)
	{
		$url = '/api/token';

		$header = [
			'Authorization' => 'Basic '. base64_encode($this->getClient_ID().':'.$this->getClient_Secret()),
		];

		$parameters = [
			'grant_type'=>'authorization_code',
			'code'=>$code,
			'redirect_uri'=>$this->getRedirect_url()
		];

		$results = $this->Request->Account('post', $url, $parameters, $header);

		if(isset($results['body']['access_token']))
		{
			$this->AccessToken = $results['body']['access_token'];
			$this->RefreshToken = $results['body']['refresh_token'];
			$this->ExpirationTime = $results['body']['expires_in'];
		}
	}

	public function RefreshToken()
	{
		$url = '/api/token';

		$header = [
			'Authorization' => 'Basic '. base64_encode($this->getClient_ID().':'.$this->getClient_Secret()),
		];

		$parameters = [
			'grant_type'=>'refresh_token',
			'refresh_token'=> $this->getRefreshToken()
		];

		$results = $this->Request->Account('post', $url, $parameters, $header);

		if(isset($results['body']['access_token']))
		{
			$this->AccessToken = $results['body']['access_token'];
			$this->ExpirationTime = $results['body']['expires_in'];
		}		
	}
	
	public function getAccessToken()
	{
		return $this->AccessToken;
	}

	public function getRefreshToken()
	{
		return $this->RefreshToken;
	}

	public function getClient_ID()
	{
		return $this->Client_ID;
	}

	public function getClient_Secret()
	{
		return $this->Client_Secret;
	}

	public function getRedirect_url()
	{
		return $this->Redirect_url;
	}

	public function getScope()
	{
		return $this->Scope;
	}

	public function getExpirationTime()
	{
		return $this->ExpirationTime;
	}

	public function setClient_ID($client_id)
	{
		$this->Client_ID = $client_id;
	}

	public function setClient_Secret($client_secret)
	{
		$this->Client_Secret = $client_secret;
	}

	public function setRedirect_url($redirect_url)
	{
		$this->Redirect_url = $redirect_url;
	}

	public function setScope($scope)
	{
		$this->Scope = $scope;
	}

}

 ?>