<?php

namespace aieuo\ip\action\process;

use aieuo\ip\utils\Categories;
use pocketmine\Player;

class DoNothing extends Process {
    protected $id = self::DO_NOTHING;
    protected $name = "@process.donothing.name";
    protected $description = "@process.donothing.description";
    protected $detail = "@process.donothing.detail";
    protected $category = Categories::CATEGORY_ACTION_OTHER;

    public function execute(Player $player): ?bool {
        return true;
    }

    public function parseFromActionSaveData(array $content): ?self {
        return $this;
    }

    public function serializeContents(): array {
        return [];
    }
}