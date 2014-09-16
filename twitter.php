<?php

require 'vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new CleanCommand);
$application->add(new FollowersCommand);
$application->add(new SimilaritiesCommand);
$application->add(new SyncCommand);

$application->add(new FollowCommand);
$application->add(new DefollowCommand);

$application->run();

