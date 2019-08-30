<?php

namespace aieuo\ip\action\process;

use pocketmine\Player;
use aieuo\ip\utils\Language;

class SendMessage extends TypeMessage {
    protected $id = self::SEND_MESSAGE;
    protected $name = "@process.sendmessage.name";
    protected $description = "@process.sendmessage.description";
    protected $detail = "process.sendmessage.detail"; // @ 無し

    public function execute(Player $player): ?bool {
        if (!$this->isDataValid()) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return false;
        }
        $player->sendMessage($this->message);
        return true;
    }
}