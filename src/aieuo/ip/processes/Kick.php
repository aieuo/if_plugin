<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class Kick extends Process
{
	public $id = self::KICK;

	public function __construct($player = null, $health = null)
	{
		parent::__construct($player);
		$this->setValues($health);
	}

	public function getName()
	{
		return "キックする";
	}

	public function getDescription()
	{
		return "プレイヤーを§7<reason>§fでキックする";
	}

	public function getEditForm(string $defaults = "", string $mes = "")
	{
		if($mes !== "") $mes = "\n".$mes;
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().$mes),
                Elements::getInput("<reason>\n理由を入力してください", "例) 悪いことをしたから", $defaults),
                Elements::getToggle("削除する")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

	public function getReason() : string
	{
		return $this->getValues();
	}

	public function setReason(string $reason)
	{
		$this->setValues($reason);
	}

	public function execute()
	{
		$player = $this->getPlayer();
		$reason = $this->getReason();
		$player->kick($reason);
	}
}