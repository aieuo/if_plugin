<?php

namespace aieuo\ip\processes;

use aieuo\ip\utils\Language;

class SendTip extends TypeMessage {

    protected $id = self::SENDTIP;
    protected $name = "@process.sendtip.name";
    protected $description = "@process.sendtip.description";

    public function getMessage() {
        $message = $this->getSendMessage();
        return Language::get("process.sendtip.detail");
    }

    public function execute() {
        $player = $this->getPlayer();
        $player->sendTip($this->getSendMessage());
    }
}