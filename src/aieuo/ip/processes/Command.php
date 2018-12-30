<?php

namespace aieuo\ip\processes;

use pockemine\Server;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class Command extends TypeCommand
{
	public $id = self::COMMAND;

	public function getName()
	{
		"コマンドを実行する";
	}

	public function getDescription()
	{
		"コマンド§7<command>§rを実行する";
	}

	public function excute()
	{
		$player = $this->getPlayer();
        Server::getInstance()->dispatchCommand($player, $this->getCommand());
	}
}