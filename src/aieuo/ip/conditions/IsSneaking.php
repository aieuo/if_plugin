<?php

namespace aieuo\ip\conditions;

class IsSneaking extends Condition {

    protected $id = self::IS_SNEAKING;
    protected $name = "@condition.issneaking.name";
    protected $description = "@condition.issneaking.description";
    protected $detail = "@condition.issneaking.detali";

	public function check() {
		$player = $this->getPlayer();
		return $player->isSneaking() ? self::MATCHED : self::NOT_MATCHED;
	}
}