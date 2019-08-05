<?php

namespace aieuo\ip\processes;

use aieuo\ip\utils\Language;

class SendMessage extends TypeMessage {

    protected $id = self::SENDMESSAGE;
    protected $name = "@process.sendmessage.name";
    protected $description = "@process.sendmessage.description";

    public function getMessage() {
        $message = $this->getSendMessage();
        return Language::get("process.sendmessage.detail", [$message]);
    }

    public function execute() {
        $player = $this->getPlayer();
        $player->sendMessage($this->getSendMessage());
    }
}