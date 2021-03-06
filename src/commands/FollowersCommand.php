<?php

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class FollowersCommand extends BaseCommand
{
	protected function configure()
	{
		$this
			->setName('followers')
			->addArgument(
				'usernames',
				InputArgument::IS_ARRAY
			)
		;
	}
	
	protected function fire(InputInterface $input, OutputInterface $output)
	{
		$usernames = $input->getArgument('usernames');
		$api = new Api;
		$merged = [];
		
		foreach ($usernames as $username) {
			$followers = $api->followersIds($username);
			dump('data/followers-'.$username.'.json', $followers);
			foreach ($followers as $f) {
				$merged[] = [$f, $username];
			}
		}
		
		$tmpFilename = sprintf(
			'merged-%s',
			date('Ymd-His')
		);
		dump('data/'.$tmpFilename,  $merged);
		$md5 = md5_file(app_path('data/'.$tmpFilename));
		rename(
			app_path('data/'.$tmpFilename),
			app_path('data/'.$tmpFilename.'-'.$md5.'.json')
		);
	}
}
