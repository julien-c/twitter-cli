<?php

class TwResponse
{
	public function __construct(array $response)
	{
		$this->requestHeader = $response['info']['request_header'];
		foreach ($response as $k => $v) {
			$this->$k = $v;
		}
		unset($this->info['request_header']);
	}
	
	
	public function check_rate_limit() {
		$headers = $this->headers;
		if (0 === intval($headers['x-rate-limit-remaining'])) {
			$reset = $headers['x-rate-limit-reset'];
			$sleep = intval($reset) - time();
			echo 'rate limited. reset time is ' . $reset . PHP_EOL;
			echo 'sleeping for ' . $sleep . ' seconds';
			sleep($sleep);
		}
	}
}
