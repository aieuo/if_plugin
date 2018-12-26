<?php

namespace aieuo\ip\processes;

class DoNothing extends Process
{
	public $id = self::DO_NOTHING;

	public function getName()
	{
		"何もしない";
	}

	public function getDescription()
	{
		"何もしない";
	}
}