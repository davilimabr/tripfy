<?php 

namespace Classes\SpotifyApi;

class Request
{
	const ACCOUNT_URL = 'https://accounts.spotify.com';
	const API_URL = 'https://api.spotify.com';

	private $Options = [];
	private $LastResponse = '';
	private $CurlOptions = [
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_RETURNTRANSFER => true
	];

	public function __construct($options = [])
	{
		$this->setOptions($options);
	}

	public function Send($method, $url, $parameters =[], $headers = [])
	{
		$ch = curl_init();
		$curl_data = $this->PrepareCURL($method, $url, $parameters, $headers);
		curl_setopt_array($ch, $curl_data + $this->CurlOptions);
		$results = curl_exec($ch);

		$results = explode("\r\n\r\n", $results);
		$last_response = [
				'header'=>$results[0],
				'body'=>json_decode(end($results), true)
		];

		if(curl_error($ch) && $last_response['body'])
		{
			throw new SpotifyException('Curl error: '. curl_error($ch));
		}
		curl_close($ch);
	
		$this->ThrowException($last_response['body']);
		
		$this->LastResponse = $last_response;
		return $last_response;
	}

	private function PrepareCURL($method, $url, $parameters, $headers)
	{
		$curl_data[CURLOPT_URL] = $url;

		if(is_array($parameters))
		$parameters = http_build_query($parameters);

		if($headers)
		{
			$curl_data[CURLOPT_HEADER] = true;
			$curl_data[CURLOPT_HTTPHEADER] = $this->PrepareHeaders($headers);
		}

		$method = strtoupper($method);
		switch ($method)
		{
			case 'DELETE':
			case 'POST':
				$curl_data[CURLOPT_POST] = true;
				$curl_data[CURLOPT_POSTFIELDS] = $parameters;
				break;
			case 'PUT':
				$curl_data[CURLOPT_PUT] = true;
				$curl_data[CURLOPT_POSTFIELDS] = $parameters;
				break;
			default:
				$curl_data[CURLOPT_CUSTOMREQUEST] = $method;

				if($parameters)
				{
					$curl_data[CURLOPT_URL] .= '/?'. $parameters;
				}
				break;
		}
		return $curl_data;
	}

	public function Account($method, $url, $parameters = [], $headers = [])
	{
		return $this->Send($method, Request::ACCOUNT_URL . $url, $parameters, $headers);
	}

	public function Api($method, $url, $parameters = [], $headers = [])
	{
		return $this->Send($method, Request::API_URL . $url, $parameters, $headers);
	}

	public function PrepareHeaders($headers)
	{
		$prepared_headers = [];

		foreach ($headers as $key => $value)
		{
			array_push($prepared_headers, "$key: $value");
		}

		return $prepared_headers;
	}

	public function ThrowException($body)
	{
		if(isset($body['error']))
		{
			$error = $body['error'];

			if(isset($error['message']))
			{
				throw new SpotifyException($error['message']);
			}
			else if (isset($error['error_description']))
				throw new SpotifyException($error['error_description']);
		}
	}

	public function setOptions($opt)
	{
		$this->Options += $opt;
	}

	public function getLastResponse()
	{
		return $this->LastResponse;
	}

}
?>