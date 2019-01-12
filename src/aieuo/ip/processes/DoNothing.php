<?php

namespace aieuo\ip\processes;

class DoNothing extends Process
{
	public $id = self::DO_NOTHING;

	public function getName()
	{
		return "何もしない";
	}

	public function getDescription()
	{
		return "何もしない";
	}
}