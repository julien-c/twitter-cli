<?php

class Util
{
	public static function trimPrefix($str, $prefix)
	{
		return (substr($str, 0, strlen($prefix)) == $prefix) ? substr($str, strlen($prefix)) : $str;
	}
	
	public static function trimSuffix($str, $suffix)
	{
		return (substr($str, -strlen($suffix)) == $suffix) ? substr($str, 0, -strlen($suffix)) : $str;
	}
}
