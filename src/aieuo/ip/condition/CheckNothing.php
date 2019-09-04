<?php

namespace aieuo\ip\condition;

use aieuo\ip\utils\Categories;
use pocketmine\Player;

class CheckNothing extends Condition {
    protected $id = self::CHECK_NOTHING;
    protected $name = "@condition.nocheck.name";
    protected $description = "@condition.nocheck.description";
    protected $detail = "@condition.nocheck.detail";
    protected $category = Categories::CATEGORY_CONDITION_OTHER;

    public function execute(Player $player): ?bool {
        return true;
    }

    public function parseFromConditionSaveData(array $data): ?self {
        return $this;
    }

    public function serializeContents(): array {
        return [];
    }
}