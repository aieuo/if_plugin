<?php
namespace aieuo\ip\task;

use pocketmine\scheduler\Task;
use pocketmine\Player;

class KickTask extends Task {
    public function __construct(Player $player, string $reason) {
        $this->player = $player;
        $this->reason = $reason;
    }

    public function onRun(int $currentTick) {
        $this->player->kick($this->reason);
    }
}