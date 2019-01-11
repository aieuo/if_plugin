<?php

namespace aieuo\ip\processes;

use pocketmine\Server;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class BroadcastMessage extends TypeMessage
{
	public $id = self::BROADCASTMESSAGE;

	public function getName()
	{
		"全員にメッセージを送る";
	}

	public function getDescription()
	{
		"全員にメッセージ§7<message>§rを送る";
	}

	public function execute()
	{
		$player = $this->getPlayer();
        Server::getInstance()->broadcastMessage($this->getMessage());
	}
}