<?php
namespace aieuo\ip\task;

use pocketmine\scheduler\Task;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class DelayedCommandTask extends Task {
    public function __construct(CommandSender $player, string $command) {
        $this->player = $player;
        $this->command = $command;
    }

    public function onRun(int $currentTick) {
        Server::getInstance()->dispatchCommand($this->player, $this->command);
    }
}