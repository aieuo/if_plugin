<?php

namespace aieuo\ip\processes;

use pocketmine\Server;

use aieuo\ip\utils\Language;

class SendMessageToOp extends TypeMessage {

    protected $id = self::SENDMESSAGE_TO_OP;
    protected $name = "@process.sendmessagetoop.name";
    protected $description = "@process.sendmessagetoop.description";

    public function getMessage() {
        $message = $this->getSendMessage();
        return Language::get("process.sendmessagetoop.detail", [$message]);
    }

    public function execute() {
        $players = Server::getInstance()->getOnlinePlayers();
        foreach ($players as $player) {
            if ($player->isOp()) {
                $player->sendMessage($this->getSendMessage());
            }
        }
    }
}