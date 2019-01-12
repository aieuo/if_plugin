<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SendMessage extends TypeMessage
{
	public $id = self::SENDMESSAGE;

	public function getName()
	{
		return "チャット欄にメッセージを送る";
	}

	public function getDescription()
	{
		return "チャット欄にメッセージ§7<message>§fを送る";
	}

	public function getMessage() {
		$message = $this->getSendMessage();
		return "チャット欄に".$message."と送る";
	}

	public function execute()
	{
		$player = $this->getPlayer();
		$player->sendMessage($this->getSendMessage());
	}
}