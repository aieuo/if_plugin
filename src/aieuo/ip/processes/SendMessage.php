<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SendMessage extends TypeMessage
{
	public $id = self::SENDMESSAGE;

	public function getName()
	{
		"チャット欄にメッセージを送る";
	}

	public function getDescription()
	{
		"チャット欄にメッセージ§7<message>§rを送る";
	}

	public function excute()
	{
		$player = $this->getPlayer();
		$player->sendMessage($this->getMessage());
	}
}