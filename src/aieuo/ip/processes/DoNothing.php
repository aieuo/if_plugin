<?php

namespace aieuo\ip\processes;

class DoNothing extends Process {

	protected $id = self::DO_NOTHING;
	protected $name = "何もしない";
	protected $description = "何もしない";

	public function getMessage() {
		return "何もしない";
	}
}