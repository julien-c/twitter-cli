<?php

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class FollowCommand extends BaseCommand
{
	protected function configure()
	{
		$this
			->setName('follow')
			->addOption(
				'username',
				'u',
				InputOption::VALUE_REQUIRED
			)
			->addOption(
				'file',
				'f',
				InputOption::VALUE_REQUIRED
			)
			->addOption(
				'num',
				null,
				InputOption::VALUE_REQUIRED
			)
			->addOption(
				'force',
				null,
				InputOption::VALUE_NONE
			)
		;
	}
	
	public function getState($username, $md5)
	{
		$state = $this->db->state->find(array(
			'username' => $username,
			'md5'      => $md5,
		))
		->sort(array('time' => -1))
		->limit(1)
		->getNext();
		
		return ($state) ? (object) $state : (object) array('position' => 0);
	}
	
	public function saveState($username, $md5, $position)
	{
		$this->db->state->insert(array(
			'username' => $username,
			'md5'      => $md5,
			'position' => $position,
			'time'     => new MongoDate(),
		));
	}
	
	
	
	protected function fire(InputInterface $input, OutputInterface $output)
	{
		foreach (array('username', 'file', 'num') as $option) {
			if (is_null($input->getOption($option))) {
				throw new Exception("Missing argument: $option");
			}
		}
		
		$username = $input->getOption('username');
		$relationships = $this->getRelationships($username);
		
		
		$api = new Api($username);
		$md5 = md5_file($input->getOption('file'));
		$toFollow = load($input->getOption('file'));
		$num = (int) $input->getOption('num');
		$state = $this->getState($username, $md5);
		
		$initialPosition = $state->position;
		
		$this->info("Follow: from account $username\t File: $md5");
		$this->info(sprintf("Starting from position: $initialPosition\t Out of: %s", count($toFollow)));
		$this->info(sprintf("Number to follow: %s", $num));
		$this->info(sprintf("Relationships update for %s: %s", $username, date(DATE_RFC822, $relationships->time->sec)));
		$this->info('-----');
		
		$n = 0;
		$position = $initialPosition;
		while ($n < $num) {
			if ($position >= count($toFollow)) {
				$this->error('File is over');
				break;
			}
			
			list($userId, $userSrc) = $toFollow[$position];
			if (in_array($userId, $relationships->followings)) {
				$this->line("Skipped $userId\tAlready following");
			}
			elseif (in_array($userId, $relationships->followers)) {
				$this->line("Skipped $userId\tAlready a follower");
			}
			else {
				if ($input->getOption('force')) {
					$response = $api->follow($userId);
					$response = json_decode($response->response);
					$this->line(sprintf("Followed $userId \tfrom $userSrc \t Username: ", $response->screen_name));
				} else {
					$this->line("Follow $userId \tfrom $userSrc");
				}
				// Store to archive
				$follow = array(
					'username' => $username,
					'md5'      => $md5,
					'time'     => new MongoDate(),
					'src'      => $userSrc,
					'userId'   => $userId,
				);
				if (isset($response)) {
					$follow['response'] = $response;
				}
				$this->db->follows->insert($follow);
				// Clean stuff
				unset($response);
				// Increment $n
				$n++;
			}
			// @todo: don't follow previously followed people
			
			$position++;
		}
		
		$this->saveState($username, $md5, $position);
	}
}
