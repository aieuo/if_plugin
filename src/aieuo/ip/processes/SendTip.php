<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SendTip extends TypeMessage
{
	public $id = self::SENDTIP;

	public function getName()
	{
		"tip欄にメッセージを送る";
	}

	public function getDescription()
	{
		"tip欄にメッセージ§7<message>§rを送る";
	}

	public function execute()
	{
		$player = $this->getPlayer();
		$player->sendTip($this->getMessage());
	}
}