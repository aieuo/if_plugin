<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SendTitle extends TypeMessage
{
	public $id = self::SENDTITLE;

	public function getName()
	{
		return "title欄にメッセージを送る";
	}

	public function getDescription()
	{
		return "title欄にメッセージ§7<message>§fを送る";
	}

	public function execute()
	{
		$player = $this->getPlayer();
        $player->addTitle($this->getMessage(), "", 20, 100, 20);
	}
}