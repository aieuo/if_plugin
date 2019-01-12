<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SetScale extends Process
{
	public $id = self::SET_SCALE;

	public function __construct($player = null, $scale = false)
	{
		parent::__construct($player);
		$this->setValues($scale);
	}

	public function getName()
	{
		return "プレイヤーのサイズを変える";
	}

	public function getDescription()
	{
		return "プレイヤーのサイズを§7<scale>§fにする";
	}

	public function getScale()
	{
		return $this->getValues();
	}

	public function setScale(float $scale)
	{
		$this->setValues($scale);
	}

	public function parse(string $content)
	{
		$scale = (float)$content;
		if($scale <= 0) return false;
        return $scale;
	}

	public function toString() : string
	{
		return (string)$this->getScale();
	}

	public function execute()
	{
		$player = $this->getPlayer();
		$scale = $this->getScale();
		if($slace === false)
		{
			$player->sendMessage("§c[".$this->getName()."] 正しく入力できていません");
			return;
		}
		$item = $player->getInventory()->getItemInHand();
        $item->addEnchantment($enchant);
		$player->getInventory()->setItemInHand($item);
	}


	public function getEditForm(string $default = "", string $mes = "")
	{
		$scale = $this->parse($default);
		if($scale === false and $default !== "")
		{
			$scale = $default;
			$mes .= "§c正しく入力できていません\n値は0より大きくしてください§f";
		}
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("\n§7<scale>§f 大きさを入力してください", "例) 2", $scale),
                Elements::getToggle("削除する"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}
}