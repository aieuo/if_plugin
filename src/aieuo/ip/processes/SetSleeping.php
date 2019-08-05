<?php

namespace aieuo\ip\processes;

use pocketmine\level\Position;

use aieuo\ip\utils\Language;

class SetSleeping extends TypePosition {

    protected $id = self::SET_SLEEPING;
    protected $name = "@process.setsleeping.name";
    protected $description = "@process.setsleeping.description";

    public function getMessage() {
        $pos = $this->getPosition();
        if ($pos === false) return false;
        return Language::get("process.setsleeping.description", [$pos->x.",".$pos->y.",".$pos->z.",".$pos->level->getFolderName()]);
    }

    public function execute() {
        $player = $this->getPlayer();
        $pos = $this->getPosition();
        if (!($pos instanceof Position)) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return;
        }
        $player->sleepOn($pos);
    }
}