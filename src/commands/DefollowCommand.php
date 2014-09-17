<?php

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class DefollowCommand extends BaseCommand
{
	protected function configure()
	{
		$this
			->setName('defollow')
			->addOption(
				'username',
				'u',
				InputOption::VALUE_REQUIRED
			)
			->addOption(
				'time',
				null,
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
	
	public function getPastFollows($username, $time, $num)
	{
		return $this->db->follows->find(array(
			'username'   => $username,
			'time'       => array('$lte' => new MongoDate($time)),
			'defollowed' => array('$exists' => false),
		))
		->sort(array('time' => 1))
		->limit($num);
	}
	
	
	protected function fire(InputInterface $input, OutputInterface $output)
	{
		foreach (['username', 'time', 'num'] as $option) {
			if (is_null($input->getOption($option))) {
				throw new Exception("Missing argument: $option");
			}
		}
		
		$username = $input->getOption('username');
		$api = new Api($username);
		$time = strtotime($input->getOption('time'));
		
		$this->info("Defollow: from account $username");
		$this->info(sprintf("Followed: before %s", date(DATE_RFC822, $time)));
		$this->info('-----');
		
		$follows = $this->getPastFollows($username, $time, (int) $input->getOption('num'));
		$relationships = $this->getRelationships($username);
		
		foreach ($follows as $follow) {
			// Those are not already defollowed
			$follow = (object) $follow;
			
			$line = '';
			$line .= $follow->userId."\t";
			if (in_array($follow->userId, $relationships->followers)) {
				$line .= "Not defollowed because\tFollowing back";
				$defollowed = false;
			}
			elseif (!in_array($follow->userId, $relationships->followings)) {
				$line .= "Not defollowed because\tLooks like not following them anymore already";
				$defollowed = false;
			}
			else {
				$line .= "Defollowing";
				if ($input->getOption('force')) {
					$api->unfollow($follow->userId);
				}
				$defollowed = true;
			}
			// Update status
			$this->db->follows->update(
				array('_id' => $follow->_id),
				array('$set' => array('defollowed' => $defollowed))
			);
			// Print line
			if (isset($follow->response)) {
				$line .= "\t".$follow->response['screen_name'];
			}
			$this->line($line);
		}
	}
}
