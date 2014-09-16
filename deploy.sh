#!/bin/sh

git pull
composer install
chown root:root cronjobs