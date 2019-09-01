<?php

namespace aieuo\ip\conditions;

class IsFlying extends Condition {

    protected $id = self::IS_FLYING;
    protected $name = "@condition.isflying.name";
    protected $description = "@condition.isflying.description";
    protected $detail = "@condition.isflying.detail";

    public function check() {
        $player = $this->getPlayer();
        return $player->isFlying() ? self::MATCHED : self::NOT_MATCHED;
    }
}