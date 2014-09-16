<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class BaseCommand extends Command
{
	protected $input;
	protected $output;
	
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->input = $input;
		$this->output = $output;
		$this->db = (new MongoClient)->{'twitter-cli'};
		$this->fire($input, $output);
	}
	
	public function line($string)
	{
		$this->output->writeln($string);
	}
	
	public function info($string)
	{
		$this->output->writeln("<info>$string</info>");
	}
	
	public function comment($string)
	{
		$this->output->writeln("<comment>$string</comment>");
	}
	
	public function error($string)
	{
		$this->output->writeln("<error>$string</error>");
	}
	
	
	// Models
	
	public function getRelationships($username)
	{
		return (object) $this->db->relationships->find(array(
			'username' => $username,
		))
		->sort(array('time' => -1))
		->limit(1)
		->getNext();
	}
	
}
