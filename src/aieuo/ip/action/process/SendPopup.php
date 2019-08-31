<?php

namespace aieuo\ip\action\process;

use pocketmine\Player;
use aieuo\ip\utils\Language;

class SendPopup extends TypeMessage {
    protected $id = self::SEND_POPUP;
    protected $name = "@process.sendpopup.name";
    protected $description = "@process.sendpopup.description";
    protected $detail = "process.sendpopup.detail"; // @ 無し

    public function execute(Player $player): ?bool {
        if (!$this->isDataValid()) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return false;
        }
        $player->sendPopup($this->getMessage());
        return true;
    }
}