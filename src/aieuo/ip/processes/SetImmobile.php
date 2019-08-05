<?php

namespace aieuo\ip\processes;

use aieuo\ip\utils\Language;

class SetImmobile extends Process {

    protected $id = self::SET_IMMOBILE;
    protected $name = "@process.immobile.name";
    protected $description = "@process.immobile.description";

    public function getMessage() {
        return Language::get("process.immobile.detail");
    }

    public function execute() {
        $player = $this->getPlayer();
        $player->setImmobile();
    }
}