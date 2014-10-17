<?php

class Api extends TwApi
{
	public function followersIds($screen_name)
	{
		return $this->cursoredIds('1.1/followers/ids', $screen_name);
	}
	
	public function followingsIds($screen_name)
	{
		return $this->cursoredIds('1.1/friends/ids', $screen_name);
	}
	
	public function cursoredIds($url, $screen_name)
	{
		$cursor = '-1';
		$ids = array();
		while (true) {
			if ($cursor == '0') break;
			
			$response = $this->request('GET', $url, array(
				'screen_name' => $screen_name,
				'cursor' => $cursor,
			));
			
			$response->check_rate_limit();
			
			if ($response->code == 200) {
				$data = json_decode($response->response, true);
				$ids = array_merge($ids, $data['ids']);
				$cursor = $data['next_cursor_str'];
			} else {
				echo $response->response;
				break;
			}
		}
		return $ids;
	}
	
	
	public function follow($id)
	{
		return $this->request('POST', '1.1/friendships/create', array(
			'user_id' => $id,
		));
	}
	
	public function unfollow($id)
	{
		return $this->request('POST', '1.1/friendships/destroy', array(
			'user_id' => $id,
		));
	}
	
	
	public function usersLookup(array $userIds)
	{
		$response = $this->request('GET', '1.1/users/lookup', array(
			'user_id' => implode(',', $userIds),
		));
		if ($response->code == 200) {
			return json_decode($response->response);
		} else {
			echo $response->response;
			break;
		}
	}
}
