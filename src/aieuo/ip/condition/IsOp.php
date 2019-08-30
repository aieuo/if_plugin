<?php

namespace aieuo\ip\condition;

use aieuo\ip\utils\Categories;
use pocketmine\Player;

class IsOp extends Condition {
    protected $id = self::IS_OP;
    protected $detail = "@condition.isop.detail";
    protected $category = Categories::CATEGORY_CONDITION_OTHER;

    public function execute(Player $player): ?bool {
        return $player->isOp();
    }

    public function serializeContents(): array {
        return [];
    }

    public function parseFromConditionSaveData(array $data): ?self {
        return $this;
    }
}