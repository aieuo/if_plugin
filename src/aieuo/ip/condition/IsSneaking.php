<?php

namespace aieuo\ip\condition;

use pocketmine\Player;

class IsSneaking extends Condition {
    protected $id = self::IS_SNEAKING;
    protected $detail = "@condition.issneaking.detail";

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