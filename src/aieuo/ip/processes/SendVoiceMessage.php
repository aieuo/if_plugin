<?php

namespace aieuo\ip\processes;

use pocketmine\lang\TranslationContainer;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SendVoiceMessage extends TypeMessage
{
	public $id = self::SENDVOICEMESSAGE;

	public function getName()
	{
		return "音声付きのメッセージを送る";
	}

	public function getDescription()
	{
		return "音声付きのメッセージ§7<message>§fを送る";
	}

	public function execute()
	{
		$player = $this->getPlayer();
        $text = new TranslationContainer($this->getMessage());
        $player->sendMessage($text);
	}
}