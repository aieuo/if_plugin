<?php

namespace aieuo\ip\ifs;

class NoCheck extends IFs
{
	public $id = self::NO_CHECK;

	public function getName()
	{
		return "何も確認しない";
	}

	public function getDescription()
	{
		return "何も確認しない";
	}

	public function check()
	{
		return self::MATCHED;
	}
}