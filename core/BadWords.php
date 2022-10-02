<?php

class BadWords {

	public function __construct()
	{
		$wrongWords = array_filter(
			array_unique(
				explode("-", file_get_contents('wrong.txt'))
			)
		);

			$fp = fopen('wrong.txt', 'w');
			fwrite($fp, implode('-', $wrongWords) . "-");
			fclose($fp);
	}

	public function addWord($word)
	{
		$fp = fopen('wrong.txt', 'a');
		fwrite($fp, $word . "-");
		fclose($fp);
	}

	public function getList()
	{
		return explode("-", file_get_contents('wrong.txt'));
	}
}