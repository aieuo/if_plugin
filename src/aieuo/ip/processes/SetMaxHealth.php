<?php

namespace aieuo\ip\processes;

use aieuo\ip\utils\Language;

class SetMaxHealth extends SetHealth {

    protected $id = self::SET_MAXHEALTH;
    protected $name = "@process.setmaxhealth.name";
    protected $description = "@process.setmaxhealth.description";

    public function getMessage() {
        $health = $this->getHealth();
        if ($health === false) return false;
        return Language::get("process.setmaxhealth.detail", [$health]);
    }

    public function execute() {
        $player = $this->getPlayer();
        $health = $this->getHealth();
        if ($health === false) {
            $player->sendMessage(Language::get("process.setmaxhealth.zero", [$this->getName()]));
            return;
        }
        $player->setMaxHealth($health);
    }
}