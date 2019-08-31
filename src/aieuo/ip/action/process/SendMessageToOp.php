<?php

namespace aieuo\ip\action\process;

use pocketmine\Server;
use pocketmine\Player;
use aieuo\ip\utils\Language;

class SendMessageToOp extends TypeMessage {
    protected $id = self::SEND_MESSAGE_TO_OP;
    protected $name = "@process.sendmessagetoop.name";
    protected $description = "@process.sendmessagetoop.description";
    protected $detail = "process.sendmessagetoop.detail"; // @ 無㝗

    public function execute(Player $player): ?bool {
        if (!$this->isDataValid()) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return false;
        }
        $players = Server::getInstance()->getOnlinePlayers();
        foreach ($players as $player) {
            if ($player->isOp()) {
                $player->sendMessage($this->getMessage());
            }
        }
        return true;
    }
}