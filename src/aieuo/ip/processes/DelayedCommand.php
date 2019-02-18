<?php

namespace aieuo\ip\processes;

use aieuo\ip\ifPlugin;
use aieuo\ip\task\DelayedCommandTask;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class DelayedCommand extends Process {

	protected $id = self::DELAYED_COMMAND;
    protected $name = "遅れてコマンドを実行する";
    protected $description = "§7<time>§f秒遅れてコマンド§7<command>§fを実行する";

	public function getMessage() {
		if($this->getValues() === false) return false;
		$command = $this->getCommand();
		$time = $this->getTiem();
		return ($time * 20)."秒遅れて/".$command." を実行する";
	}

	public function getTime() {
		return $this->getValues()[1];
	}

	public function getCommand() {
		return $this->getValues()[0];
	}

	public function setCommands(string $command, int $time) {
		$this->setValues($command, $time);
	}

	public function parse(string $commands) {
	    if(!preg_match("/([0-9]+),(.+)/", $commands, $matches)) return false;
	    return [$matches[2], (int)$matches[1]];
	}

	public function execute() {
		$player = $this->getPlayer();
		if($this->getValues() === false) {
			$player->sendMessage("§c[".$this->getName()."] 正しく入力できていません");
			return;
		}
		$time = $this->getTime();
		$command = $this->getCommand();
        ifPlugin::getInstance()->getScheduler()->scheduleDelayedTask(new DelayedCommandTask($player, $command), $time*20);
	}

	public function getEditForm(string $default = "", string $mes = "") {
		$commands = $this->parse($default);
		$command = $default;
		$time = "";
		if($commands !== false) {
			$command = $commands[0];
			$time = $commands[1];
		} elseif($default !== "") {
			$mes .= "§c正しく入力できていません§f";
		}
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("\n§7<command>§f 実行するコマンドを入力してください", "例) help", $command),
                Elements::getInput("\n§7<time>§f 遅らせる時間を入力してください", "例) 10", $time),
                Elements::getToggle("削除する"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

    public function parseFormData(array $datas) {
    	$status = true;
    	if($datas[1] === "" or $datas[2] === "") {
    		$status = null;
    	} else {
	    	$value = $this->parse($datas[2].",".$datas[1]);
	    	if($value === false) $status = false;
	    }
    	return ["status" => $status, "contents" => $datas[2].",".$datas[1], "delete" => $datas[3], "cancel" => $datas[4]];
    }
}