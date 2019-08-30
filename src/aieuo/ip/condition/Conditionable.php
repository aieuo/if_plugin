<?php

namespace aieuo\ip\condition;

use pocketmine\Player;

interface Conditionable extends \JsonSerializable {
    /**
     * @param Player $player
     * @return boolean|null
     */
    public function execute(Player $player): ?bool;
}