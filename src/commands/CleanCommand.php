<?php

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class CleanCommand extends BaseCommand
{
	protected function configure()
	{
		$this
			->setName('clean')
			->setDescription('Clean log and data files.')
			->addOption(
				'data',
				'd',
				InputOption::VALUE_NONE,
				'Clean data files as well'
			)
		;
	}
	
	protected function fire(InputInterface $input, OutputInterface $output)
	{
		$this->clean('logs/raw/*.json');
		if ($input->getOption('data')) {
			$this->clean('logs/data/*.json');
		}
	}
	
	protected function clean($pattern)
	{
		$files = glob($pattern);
		foreach ($files as $file) {
			$this->error($file);
			if (is_file($file)) {
				unlink($file);
			}
		}
	}
}