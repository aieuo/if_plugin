<?php

namespace aieuo\ip\conditions;

class IsOp extends Condition {

    protected $id = self::IS_OP;
    protected $name = "@condition.isop.name";
    protected $description = "@condition.isop.description";
    protected $detail = "@condition.isop.detail";

    public function check() {
        $player = $this->getPlayer();
        return $player->isOp() ? self::MATCHED : self::NOT_MATCHED;
    }
}