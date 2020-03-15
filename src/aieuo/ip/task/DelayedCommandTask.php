<?php
namespace aieuo\ip\task;

use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class DelayedCommandTask extends Task {
    /* @var string */
    private $command;
    /* @var CommandSender */
    private $player;

    public function __construct(CommandSender $player, string $command) {
        $this->player = $player;
        $this->command = $command;
    }

    public function onRun(int $currentTick) {
        if ($this->player instanceof Player && !$this->player->isOnline()) return;
        Server::getInstance()->dispatchCommand($this->player, $this->command);
    }
}