<?php
namespace aieuo\ip\task;

use pocketmine\scheduler\Task;

class DelayedCommandTask extends Task {
	public function __construct($player, $command) {
        $this->player = $player;
        $this->command = $command;
	}

	public function onRun(int $currentTick) {
		$this->player->getServer()->dispatchCommand($this->player, $this->command);
	}
}