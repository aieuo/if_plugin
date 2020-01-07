<?php
namespace aieuo\ip\task;

use pocketmine\scheduler\Task;
use aieuo\ip\IFPlugin;

class SaveTask extends Task {
    /** @var IFPlugin */
    private $owner;

    public function __construct(IFPlugin $owner) {
        $this->owner = $owner;
    }

    public function onRun(int $currentTick) {
        $this->owner->getBlockManager()->save();
        $this->owner->getCommandManager()->save();
        $this->owner->getEventManager()->save();
        $this->owner->getFormIFManager()->save();
        $this->owner->getChainManager()->save();
        $this->owner->getVariableHelper()->save();
        $this->owner->config->save();
    }
}