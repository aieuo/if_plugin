<?php

namespace aieuo\ip\processes;

use aieuo\ip\ifPlugin;
use pocketmine\command\ConsoleCommandSender;
use aieuo\ip\task\DelayedCommandTask;
use aieuo\ip\utils\Language;

class DelayedCommandConsole extends DelayedCommand {

    protected $id = self::DELAYED_COMMAND_CONSOLE;
    protected $name = "@process.delayedcommandconsole.name";
    protected $description = "@process.delayedcommandconsole.description";

    public function getDetail(): string {
        if ($this->getValues() === false) return false;
        $command = $this->getCommand();
        $time = $this->getTime();
        return Language::get("process.delayedcommandconsole.detail", [$time, $command]);
    }

    public function execute() {
        $player = $this->getPlayer();
        if ($this->getValues() === false) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return;
        }
        $time = $this->getTime();
        $command = $this->getCommand();
        ifPlugin::getInstance()->getScheduler()->scheduleDelayedTask(new DelayedCommandTask(new ConsoleCommandSender(), $command), $time*20);
    }
}