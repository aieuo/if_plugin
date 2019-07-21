<?php

namespace aieuo\ip\processes;

use aieuo\ip\ifPlugin;
use pocketmine\command\ConsoleCommandSender;
use aieuo\ip\task\DelayedCommandTask;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class DelayedCommandConsole extends DelayedCommand {

    protected $id = self::DELAYED_COMMAND_CONSOLE;
    protected $name = "遅れてコマンドをコンソールから実行する";
    protected $description = "§7<time>§f秒遅れてコマンド§7<command>§fをコンソールから実行する";

    public function getMessage() {
        if($this->getValues() === false) return false;
        $command = $this->getCommand();
        $time = $this->getTime();
        return $time."秒遅れて/".$command." をコンソールから実行する";
    }

    public function execute() {
        $player = $this->getPlayer();
        if($this->getValues() === false) {
            $player->sendMessage("§c[".$this->getName()."] 正しく入力できていません");
            return;
        }
        $time = $this->getTime();
        $command = $this->getCommand();
        ifPlugin::getInstance()->getScheduler()->scheduleDelayedTask(new DelayedCommandTask(new ConsoleCommandSender(), $command), $time*20);
    }
}