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
			date('Ymd-His'),
			str_replace(['/', '.'], '-', Util::trimPrefix($url, '1.1/')),
			md5($twResponse->requestHeader)
		);
		
		dump('logs/raw/'.$filename, $twResponse);
		dump('logs/data/'.$filename, json_decode($twResponse->response));
		
		return $twResponse;
	}
}

