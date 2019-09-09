<?php

namespace aieuo\ip\processes;

use pocketmine\level\Position;

use aieuo\ip\utils\Language;

class Teleport extends TypePosition {

    protected $id = self::TELEPORT;
    protected $name = "@process.teleport.name";
    protected $description = "@process.teleport.description";

    public function getDetail(): string {
        $pos = $this->getPosition();
        if ($pos === false or $pos->level === null) return false;
        return Language::get("process.teleport.detail", [$pos->x.",".$pos->y.",".$pos->z.",".$pos->level->getFolderName()]);
    }

    public function execute() {
        $player = $this->getPlayer();
        $pos = $this->getPosition();
        if (!($pos instanceof Position)) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return;
        }
        $player->teleport($pos);
    }
}