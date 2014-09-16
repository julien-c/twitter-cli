<?php

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class SyncCommand extends BaseCommand
{
	protected function configure()
	{
		$this
			->setName('sync')
			->setDescription('Sync followers/followings lists for specified users.')
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
		
		foreach ($usernames as $username) {
			$followers  = $api->followersIds($username);
			$followings = $api->followingsIds($username);
			$this->db->relationships->insert(array(
				'username'   => $username,
				'time'       => new MongoDate,
				'followers'  => $followers,
				'followings' => $followings,
			));
			
			if (!$output->isQuiet()) {
				$this->info(sprintf("Synced %s:\t Following %s\t Followers %s", 
					$username,
					count($followings),
					count($followers)
				));
			}
		}
	}
}
