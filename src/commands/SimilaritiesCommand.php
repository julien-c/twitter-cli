<?php

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class SimilaritiesCommand extends BaseCommand
{
	protected function configure()
	{
		$this
			->setName('similarities')
		;
	}
	
	protected function fire(InputInterface $input, OutputInterface $output)
	{
		$files = glob('data/followers-*.json');
		for ($i = 0; $i < count($files); $i++) { 
			for ($j = $i + 1; $j < count($files); $j++) {
				$usernamei = Util::trimSuffix(Util::trimPrefix($files[$i], 'data/followers-'), '.json');
				$usernamej = Util::trimSuffix(Util::trimPrefix($files[$j], 'data/followers-'), '.json');
				$this->info($usernamei.' + '.$usernamej);
				$followersi = load($files[$i]);
				$followersj = load($files[$j]);
				$this->line(count(array_intersect($followersi, $followersj)));
			}
		}
	}
}