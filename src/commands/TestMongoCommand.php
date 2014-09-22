<?php

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class TestMongoCommand extends BaseCommand
{
	protected function configure()
	{
		$this
			->setName('test:mongo')
		;
	}
	
	protected function fire(InputInterface $input, OutputInterface $output)
	{
		$bigint = 2825725550;
		
		$this->db->test->remove();
		$this->db->test->insert(array(
			'foo' => 'bar',
			'int' => $bigint,
		));
		
		$doc = $this->db->test->findOne();
		var_dump($doc);
		if ($doc['int'] === $bigint) {
			$this->info('Bigint saved as bigint');
		}
		else {
			$this->error('Bigint NOT saved as bigint');
		}
	}
}
