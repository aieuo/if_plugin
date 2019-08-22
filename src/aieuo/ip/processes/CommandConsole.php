<?php

namespace aieuo\ip\processes;

use pocketmine\Server;
use pocketmine\command\ConsoleCommandSender;

use aieuo\ip\utils\Language;

class CommandConsole extends TypeCommand {

    protected $id = self::COMMAND_CONSOLE;
    protected $name = "@process.commandconsole.name";
    protected $description = "@process.commandconsole.description";

    public function getDetail(): string {
        $command = $this->getCommand();
        return Language::get("process.commandconsole.detail", [$command]);
    }

    public function execute() {
        Server::getInstance()->dispatchCommand(new ConsoleCommandSender, $this->getCommand());
    }
}