<?php

namespace aieuo\ip\condition;

use aieuo\ip\utils\Categories;
use pocketmine\Player;

class IsFlying extends Condition {
    protected $id = self::IS_FLYING;
    protected $name = "@condition.isflying.name";
    protected $description = "@condition.isflying.description";
    protected $detail = "@condition.isflying.detail";
    protected $category = Categories::CATEGORY_CONDITION_OTHER;

    public function execute(Player $player): ?bool {
        return $player->isFlying();
    }

    public function parseFromConditionSaveData(array $data): ?self {
        return $this;
    }

    public function serializeContents(): array {
        return [];
    }
}