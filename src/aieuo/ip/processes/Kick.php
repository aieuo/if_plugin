<?php

namespace aieuo\ip\processes;

use aieuo\ip\ifPlugin;
use aieuo\ip\task\KickTask;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class Kick extends Process
{
	public $id = self::KICK;

	public function __construct($player = null, $reason = "")
	{
		parent::__construct($player);
		$this->setValues($reason);
	}

	public function getName()
	{
		return "キックする";
	}

	public function getDescription()
	{
		return "プレイヤーを§7<reason>§fでキックする";
	}

	public function getReason()
	{
		return $this->getValues();
	}

	public function setReason(string $reason)
	{
		$this->setValues($reason);
	}

	public function toString() : string
	{
		return (string)$this->getReason();
	}

	public function execute()
	{
		$player = $this->getPlayer();
		$reason = $this->getReason();
        ifPlugin::getInstance()->getScheduler()->scheduleDelayedTask(new KickTask($player, $reason), 5);
	}

	public function getEditForm(string $default = "", string $mes = "")
	{
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("\n§7<reason>§f 理由を入力してください", "例) 悪いことをしたから", $default),
                Elements::getToggle("削除する"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

    public function parseFormData(array $datas) {
    	if($datas[1] === "") return null;
    	return ["contents" => $datas[1], "delete" => $datas[2], "cancel" => $datas[3]];
    }
}