<?php

namespace aieuo\ip\conditions;

class NoCheck extends Condition
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
		return self::ERROR;
	}
}