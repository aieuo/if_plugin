<?php

namespace aieuo\ip\processes;

use pocketmine\lang\TranslationContainer;

use aieuo\ip\utils\Language;

class SendVoiceMessage extends TypeMessage {

    protected $id = self::SENDVOICEMESSAGE;
    protected $name = "@process.sendvoicemessage.name";
    protected $description = "@process.sendvoicemessage.description";

    public function getDetail(): string {
        $message = $this->getSendMessage();
        return Language::get("process.sendvoicemessage.detail", [$message]);
    }

    public function execute() {
        $player = $this->getPlayer();
        $text = new TranslationContainer($this->getSendMessage());
        $player->sendMessage($text);
    }
}