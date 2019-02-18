<?php

namespace aieuo\ip\processes;

use pocketmine\Server;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class Command extends TypeCommand {

	protected $id = self::COMMAND;
	protected $name = "コマンドを実行する";
	protected $description = "コマンド§7<command>§fを実行する";

	public function getMessage() {
		$command = $this->getCommand();
		return "/".$command." を実行する";
	}

	public function execute() {
		$player = $this->getPlayer();
        Server::getInstance()->dispatchCommand($player, $this->getCommand());
	}
}