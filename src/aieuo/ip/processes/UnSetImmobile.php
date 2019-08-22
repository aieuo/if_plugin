<?php

namespace aieuo\ip\processes;

class UnSetImmobile extends Process {

    protected $id = self::UNSET_IMMOBILE;
    protected $name = "@process.mobile.name";
    protected $description = "@process.mobile.description";
    protected $detail = "@process.mobile.detail";

    public function execute() {
        $player = $this->getPlayer();
        $player->setImmobile(false);
    }
}