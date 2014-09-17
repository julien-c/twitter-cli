<?php

class TwApi
{
	public function __construct($username = null)
	{
		$credentials = load('.credentials.json');
		
		if ($username && !isset($credentials['users'][$username])) {
			throw new DomainException('Unknown user.');
		}
		
		$token = ($username) ? $credentials['users'][$username] : array_values($credentials['users'])[0];
		
		$this->tmh = new tmhOAuth(array(
			'consumer_key'    => $credentials['consumer_key'],
			'consumer_secret' => $credentials['consumer_secret'],
			'user_token'      => $token[0],
			'user_secret'     => $token[1],
			'user_agent'      => 'Circular.io/2.0',
		));
	}
	
	
	
	public function request($method, $url, $params = array())
	{
		$this->tmh->request($method, $this->tmh->url($url), $params);
		$twResponse = new TwResponse($this->tmh->response);
		
		$filename = sprintf(
			'%s-%s-%s.json', 
			date('Ymd-Gis'),
			str_replace(['/', '.'], '-', Util::trimPrefix($url, '1.1/')),
			md5($twResponse->requestHeader)
		);
		
		file_put_contents('logs/raw/' .$filename, json_encode($twResponse, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
		file_put_contents('logs/data/'.$filename, json_encode(json_decode($twResponse->response), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
		
		return $twResponse;
	}
}

