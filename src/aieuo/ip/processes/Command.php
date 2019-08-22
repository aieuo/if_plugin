<?php

namespace aieuo\ip\processes;

use pocketmine\Server;

use aieuo\ip\utils\Language;

class Command extends TypeCommand {

    protected $id = self::COMMAND;
    protected $name = "@process.command.name";
    protected $description = "@process.command.description";

    public function getDetail(): string {
        $command = $this->getCommand();
        return Language::get("process.command.detail", [$command]);
    }

    public function execute() {
        $player = $this->getPlayer();
        Server::getInstance()->dispatchCommand($player, $this->getCommand());
    }
}