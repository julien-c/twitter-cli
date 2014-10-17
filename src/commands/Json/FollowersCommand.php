<?php
namespace Json;

use Api;
use BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FollowersCommand extends BaseCommand
{
	
	protected function configure()
	{
		$this
			->setName('json:followers')
			->addArgument('username', InputArgument::REQUIRED)
			->addOption('toUnfollow', null, InputOption::VALUE_NONE)
			->addOption('lookup',     null, InputOption::VALUE_NONE)
			->addOption('unfollow',   null, InputOption::VALUE_NONE)
			->addOption('file',       null, InputOption::VALUE_OPTIONAL)
		;
	}
	
	protected function fire(InputInterface $input, OutputInterface $output)
	{
		$this->username = $input->getArgument('username');
		$this->api      = new Api($this->username);
		
		foreach (['toUnfollow', 'lookup', 'unfollow'] as $option) {
			if ($input->getOption($option)) {
				return $this->{$option}($input, $output);
			}
		}
	}
	
	
	public function toUnfollow(InputInterface $input, OutputInterface $output)
	{
		$followers  = $this->api->followersIds($this->username);
		$followings = $this->api->followingsIds($this->username);
		
		$toUnfollow = array_values(array_diff($followings, $followers));
		
		if (!$output->isQuiet()) {
			$this->info(sprintf("Stats %s:\t Following %s\t Followers %s",
				$this->username,
				count($followings),
				count($followers)
			));
			$this->info(sprintf("Followings not following back: %s",
				count($toUnfollow)
			));
		}
		
		echo json_encode($toUnfollow, JSON_PRETTY_PRINT).PHP_EOL;
	}
	
	
	public function lookup(InputInterface $input, OutputInterface $output)
	{
		$ids = json_decode(file_get_contents($input->getOption('file')), true);
		$lookups = [];
		foreach (array_chunk($ids, 100) as $userIds) {
			$users = $this->api->usersLookup($userIds);
			foreach ($users as $u) {
				$lookups[$u->id] = $u->screen_name;
			}
		}
		
		echo json_encode($lookups, JSON_PRETTY_PRINT).PHP_EOL;
	}
	
	
	public function unfollow(InputInterface $input, OutputInterface $output)
	{
		$lookups = json_decode(file_get_contents($input->getOption('file')), true);
		$leaders = load('.credentials.json')['leaders'][$this->username];
		foreach ($lookups as $id => $username) {
			if (!in_array($username, $leaders)) {
				$this->line('Unfollowing '.$username);
				$this->api->unfollow($id);
				sleep(2);
			}
		}
	}
}
