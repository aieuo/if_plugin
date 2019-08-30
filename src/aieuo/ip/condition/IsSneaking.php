<?php

namespace aieuo\ip\condition;

use pocketmine\Player;
use aieuo\ip\utils\Categories;

class IsSneaking extends Condition {
    protected $id = self::IS_SNEAKING;
    protected $detail = "@condition.issneaking.detail";
    protected $category = Categories::CATEGORY_CONDITION_OTHER;

    public function execute(Player $player): ?bool {
        return $player->isSneaking();
    }

    public function serializeContents(): array {
        return [];
    }

    public function parseFromConditionSaveData(array $data): ?self {
        return $this;
    }
}