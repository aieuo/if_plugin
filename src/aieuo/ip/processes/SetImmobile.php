<?php

namespace aieuo\ip\processes;

class SetImmobile extends Process {

    protected $id = self::SET_IMMOBILE;
    protected $name = "@process.immobile.name";
    protected $description = "@process.immobile.description";
    protected $detail = "@process.immobile.detail";

    public function execute() {
        $player = $this->getPlayer();
        $player->setImmobile();
    }
}