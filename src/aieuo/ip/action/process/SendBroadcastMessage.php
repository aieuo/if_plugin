<?php

namespace aieuo\ip\action\process;

use pocketmine\Player;
use aieuo\ip\utils\Language;
use pocketmine\Server;

class SendBroadcastMessage extends TypeMessage {
    protected $id = self::SEND_BROADCAST_MESSAGE;
    protected $name = "@process.broadcastmessage.name";
    protected $description = "@process.broadcastmessage.description";
    protected $detail = "process.broadcastmessage.detail"; // @ 無㝗

    public function execute(Player $player): ?bool {
        if (!$this->isDataValid()) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return false;
        }
        Server::getInstance()->broadcastMessage($this->getMessage());
        return true;
    }
}