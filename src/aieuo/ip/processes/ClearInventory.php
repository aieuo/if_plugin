<?php

namespace aieuo\ip\processes;

class ClearInventory extends Process {
    protected $id = self::CLEAR_INVENTORY;
    protected $name = "@process.clearInventory.name";
    protected $description = "@process.clearInventory.description";
    protected $detail = "@process.clearInventory.detail";

    public function execute() {
        $player = $this->getPlayer();
        $player->getInventory()->clearAll();
    }
}