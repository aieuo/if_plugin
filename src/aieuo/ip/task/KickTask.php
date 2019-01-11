<?php
namespace aieuo\ip\task;

use pocketmine\scheduler\Task;

class KickTask extends Task {
	public function __construct($player, $reason) {
        $this->player = $player;
        $this->reason = $reason;
	}

	public function onRun(int $currentTick) {
		$this->player->kick($this->reason);
	}
}