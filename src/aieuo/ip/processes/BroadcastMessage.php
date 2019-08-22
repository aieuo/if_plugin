<?php

namespace aieuo\ip\processes;

use pocketmine\Server;

use aieuo\ip\utils\Language;

class BroadcastMessage extends TypeMessage {

    protected $id = self::BROADCASTMESSAGE;
    protected $name = "@process.broadcastmessage.name";
    protected $description = "@process.broadcastmessage.description";

    public function getDetail(): string {
        $message = $this->getSendMessage();
        return Language::get("process.broadcastmessage.detail", [$message]);
    }

    public function execute() {
        $player = $this->getPlayer();
        Server::getInstance()->broadcastMessage($this->getSendMessage());
    }
}