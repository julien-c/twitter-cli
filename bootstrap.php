<?php

function app_path($path = '')
{
	return __DIR__.($path ? '/'.$path : $path);
}

function load($filename)
{
	return json_decode(file_get_contents(app_path($filename)), true);
}

function dump($filename, $data)
{
	file_put_contents(app_path($filename), json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
}

