<?php

namespace aieuo\ip\conditions;

use pocketmine\item\Item;

use aieuo\ip\form\Form;
use aieui\ip\form\Elements;

class InHand extends Condition
{
	public $id = self::IN_HAND;

	public function __construct($player = null, $item = null)
	{
		parent::__construct($player);
		$this->setValues($item);
	}

	public function getName()
	{
		return "指定したアイテムを手に持ってるか";
	}

	public function getDescription()
	{
		return "idが§7<id>§fのアイテムを§7<count>§f個以上手に持っているなら";
	}

	public function getEditForm(string $defaults = "", string $mes = "")
	{
		$item = $this->parse($defaults);
		$id = $defaults;
		$count = "";
		if($item instanceof Item)
		{
			$id = $item->getId().":".$item->getDamage();
			$count = (string)$item->getCount();
		}
		else
		{
			$mes = "§c正しく入力できていません (idは 1:0 のように数字で入力してください)§f";
		}
		if($mes !== "") $mes = "\n".$mes;
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().$mes),
                Elements::getInput("<id>\nアイテムのidを入力してください", "例) 1:0", $id),
                Elements::getInput("<count>\nアイテムの数を入力してください", "例) 5", $count),
                Elements::getToggle("削除する")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

	public function parse(string $id) : Item
	{
		if($id === "" or (strpos($id, ":") === false and !is_numeric($id))) return false;
		$ids = explode(":", $id);
		$item = Item::get((int)$ids[0], !empty($ids[1]) ? (int)$ids[1] : 0, !empty($ids[2]) ? (int)$ids[2] : 0);
		return $item;
	}

	public function getItem() : Item
	{
		return $this->getValues()[0];
	}

	public function setItem(Item $item)
	{
		$this->setValues($item);
	}

	public function check()
	{
		$player = $this->getPlayer();
	    $hand = $player->getInventory()->getItemInHand();
	    $item = $this->getItem();
        if(
        	$hand->getId() == $item->getId()
        	and $hand->getDamage() == $item->getDamage()
        	and $hand->getCount() >= $item->getCount()
        )
        {
            return self::MATCHED;
        }
        return self::NOT_MATCHED;
	}
}