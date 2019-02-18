<?php

namespace aieuo\ip\processes;

use pocketmine\Server;
use pocketmine\command\ConsoleCommandSender;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class CommandConsole extends TypeCommand {

	protected $id = self::COMMAND_CONSOLE;
	protected $name = "コンソールからコマンドを実行する";
	protected $description = "コンソールからコマンド§7<command>§fを実行する";

	public function getMessage() {
		$command = $this->getCommand();
		return "コンソールから/".$command." を実行する";
	}

	public function execute() {
        Server::getInstance()->dispatchCommand(new ConsoleCommandSender, $this->getCommand());
	}
}