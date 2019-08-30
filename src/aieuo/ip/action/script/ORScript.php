<?php

namespace aieuo\ip\action\script;

use pocketmine\Player;

class ORScript extends AndScript {
    protected $id = self::SCRIPT_OR;
    protected $name = "@script.or.name";

    public function getDetail(): string {
        $details = ["-----------or-----------"];
        foreach ($this->conditions as $condition) {
            $details[] = $condition->getDetail();
        }
        $details[] = "------------------------";
        return implode("\n", $details);
    }

    public function execute(Player $player): ?bool {
        $matched = false;
        foreach ($this->conditions as $condition) {
            $result = $condition->execute($player);
            if ($result === null) return null;
            if ($result) $matched = true;
        }
        return $matched;
    }
}