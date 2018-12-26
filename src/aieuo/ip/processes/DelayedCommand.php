<?php

namespace aieuo\ip\processes;

use aieuio\ip\ifPlugin;
use aieuo\ip\task\DelayedCommandTask;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class DelayedCommand extends Process
{
	public $id = self::DELAYED_COMMAND;

	public function __construct($player = null, $command = null, $time = 0)
	{
		parent::__construct($player);
		$this->setValues([$command, $time]);
	}

	public function getName()
	{
		"遅れてコマンドを実行する";
	}

	public function getDescription()
	{
		"§7<time>§r秒遅れてコマンド§7<command>§rを実行する";
	}

	public function getTime()
	{
		return $this->getValues()[1];
	}

	public function getCommand()
	{
		return $this->getValues()[0];
	}

	public function setCommands(string $command, int $time)
	{
		$this->setValues($command, $time);
	}

	public function parse(string $commands)
	{
	    if(!preg_match("/([0-9]+),(.+)/", $commands, $matches)) return false;
	    return [$matches[2], (int)$matches[1]];
	}

	public function getEditForm(string $defaults = "", string $mes = "")
	{
		if($mes !== "") $mes = "\n".$mes;
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().$mes),
                Elements::getInput("<command>\n実行するコマンドを入力してください", "例) help", $defaults),
                Elements::getInput("<time>\n遅らせる時間を入力してください", "例) 10", $defaults),
                Elements::getToggle("削除する")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

	public function excute()
	{
		$player = $this->getPlayer();
		$time = $this->getTime();
		$command = $this->getCommand();
        ifPlugin::getInstance()->getScheduler()->scheduleDelayedTask(new DelayedCommandTask($player, $command), $time*20);
	}
}