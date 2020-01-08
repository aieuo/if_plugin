<?php
namespace aieuo\ip\task;

use pocketmine\scheduler\Task;
use pocketmine\event\Event;
use pocketmine\Player;
use aieuo\ip\utils\Language;
use aieuo\ip\IFPlugin;

class DelayedCooperationTask extends Task {
    public function __construct(Player $player, string $name, ?Event $event, ?array $replaces) {
        $this->player = $player;
        $this->name = $name;
        $this->event = $event;
        $this->replaceDatas = $replaces;
    }

    public function onRun(int $currentTick) {
        $manager = IFPlugin::getInstance()->getChainManager();
        if (!$manager->exists($this->name)) {
            $this->player->sendMessage(Language::get("process.cooperation.notfount"));
            return;
        }
        $data = $manager->get($this->name);
        $options = [
            "player" => $this->player,
        ];
        if ($this->event instanceof Event) $options["event"] = $this->event;
        $options["replaces"] = $this->replaceDatas;
        $manager->executeIfMatchCondition(
            $this->player,
            $data["if"],
            $data["match"],
            $data["else"],
            $options
        );
    }
}