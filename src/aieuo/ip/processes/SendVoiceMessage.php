<?php

namespace aieuo\ip\processes;

use pocketmine\lang\TranslationContainer;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SendVoiceMessage extends TypeMessage {

    protected $id = self::SENDVOICEMESSAGE;
    protected $name = "音声付きのメッセージを送る";
    protected $description = "音声付きのメッセージ§7<message>§fを送る";

	public function getMessage() {
		$message = $this->getSendMessage();
		return "音声付きのメッセージ(".$message.")を送る";
	}

	public function execute() {
		$player = $this->getPlayer();
        $text = new TranslationContainer($this->getSendMessage());
        $player->sendMessage($text);
	}
}