<?php

namespace Tests;

class Utils
{

	private function __construct() {}

	public static function getDataDir()
	{
		return __DIR__;
	}

	public static function saveTestData($in, $data)
	{
		file_put_contents(self::getFilename($in), $data);
	}

	public static function getContent($in)
	{
		return file_get_contents(self::getFilename($in));
	}

	private static function getFilename($in)
	{
		return self::getDataDir() . '/' . $in . '.json';
	}

}
