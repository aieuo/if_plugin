<?php

namespace aieuo\ip\processes;

use pocketmine\math\Vector3;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SetHealth extends Process
{
	public $id = self::SET_HEALTH;

	public function __construct($player = null, $health = 0)
	{
		parent::__construct($player);
		$this->setValues($health);
	}

	public function getName()
	{
		return "体力を変更する";
	}

	public function getDescription()
	{
		return "プレイヤーの体力を§7<health>§fにする";
	}

	public function getHealth()
	{
		return $this->getValues();
	}

	public function setHealth(int $health)
	{
		$this->setValues($health);
	}

	public function parse(string $content)
	{
        $health = (int)$content;
        if($health <= 0) return false;
    	return $health;
	}

	public function toString() : string
	{
		return (string)$this->getHealth();
	}

	public function execute()
	{
		$player = $this->getPlayer();
		$health = $this->gethealth();
		if($health === false)
		{
			$player->sendMessage("§c[".$this->getName()."] 体力は1以上にしてください");
			return;
		}
		$player->setHealth($health);
	}

	public function getEditForm(string $default = "", string $mes = "")
	{
		$health = $this->parse($default);
		if($health === false)
		{
			$mes = "§c体力は1以上にしてください§f";
			$health = $default;
		}
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("\n§7<health>§f 体力を入力してください", "例) 10", $health),
                Elements::getToggle("削除する"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

    public function parseFormData(array $datas) {
    	if($datas[1] === "") return null;
    	$health = $this->parse($datas[1]);
    	if($health === false) return false;
    	return ["contents" => $datas[1], "delete" => $datas[2], "cancel" => $datas[3]];
    }
}