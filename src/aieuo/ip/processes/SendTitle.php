<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SendTitle extends TypeMessage
{
	public $id = self::SENDTITLE;

	public function getName()
	{
		"title欄にメッセージを送る";
	}

	public function getDescription()
	{
		"title欄にメッセージ§7<message>§rを送る";
	}

	public function execute()
	{
		$player = $this->getPlayer();
        $player->addTitle($this->getMessage(), "", 20, 100, 20);
	}
}