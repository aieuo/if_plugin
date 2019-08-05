<?php

namespace aieuo\ip\processes;

use aieuo\ip\utils\Language;

class SendTitle extends TypeMessage {

    protected $id = self::SENDTITLE;
    protected $name = "@process.sendtitile.name";
    protected $description = "@process.sendtitile.description";

    public function getMessage() {
        $message = $this->getSendMessage();
        return Language::get("process.sendtitile.detail", [$message]);
    }

    public function execute() {
        $player = $this->getPlayer();
        $player->addTitle($this->getSendMessage(), "", 20, 100, 20);
    }
}