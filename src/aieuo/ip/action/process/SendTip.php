<?php

namespace aieuo\ip\action\process;

use pocketmine\Player;
use aieuo\ip\utils\Language;

class SendTip extends TypeMessage {
    protected $id = self::SEND_TIP;
    protected $name = "@process.sendtip.name";
    protected $description = "@process.sendtip.description";
    protected $detail = "process.sendtip.detail"; // @ 無し

    public function execute(Player $player): ?bool {
        if (!$this->isDataValid()) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return false;
        }
        $player->sendTip($this->getMessage());
        return true;
    }
}