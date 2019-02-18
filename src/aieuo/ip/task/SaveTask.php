<?php
namespace aieuo\ip\task;

use pocketmine\scheduler\Task;

class SaveTask extends Task {
    public function __construct($owner) {
        $this->owner = $owner;
    }

    public function onRun(int $currentTick) {
        $this->owner->getBlockManager()->save();
        $this->owner->getCommandManager()->save();
        $this->owner->getEventManager()->save();
        $this->owner->getChainManager()->save();
        $this->owner->getVariableHelper()->save();
    }
}