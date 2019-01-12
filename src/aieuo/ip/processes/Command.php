<?php

namespace aieuo\ip\processes;

use pocketmine\Server;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class Command extends TypeCommand
{
	public $id = self::COMMAND;

	public function getName()
	{
		return "コマンドを実行する";
	}

	public function getDescription()
	{
		return "コマンド§7<command>§fを実行する";
	}

	public function execute()
	{
		$player = $this->getPlayer();
        Server::getInstance()->dispatchCommand($player, $this->getCommand());
	}
}