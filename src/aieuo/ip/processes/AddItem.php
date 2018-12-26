<?php

namespace aieuo\ip\processes;

use pocketmine\item\Item;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class AddItem extends Process
{
	public $id = self::ADD_ITEM;

	public function __construct($player = null, $item = null)
	{
		parent::__construct($player);
		$this->setValues($item);
	}

	public function getName()
	{
		return "インベントリにアイテムを追加する";
	}

	public function getDescription()
	{
		return "インベントリにidが§7<id>§fの§7<name>§fという名前のアイテムを§7<count>§f追加する";
	}

	public function getEditForm(string $defaults = "", string $mes = "")
	{
		$item = $this->parse($defaults);
		$id = $item->getId();
		$count = $item->getCount();
		$name = $item->getName();
		if($mes !== "") $mes = "\n".$mes;
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().$mes),
                Elements::getInput("<id>\nアイテムのidを入力してください", "例) 1:0", $id),
                Elements::getInput("<count>\nアイテムの数を入力してください", "例) 5", $count),
                Elements::getInput("<name>\nアイテムに付けたい名前を入力してください(空白ならそのままの名前です)", "例) aieuo", $name),
                Elements::getToggle("削除する")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

	public function parse(string $id) : Item
	{
		$ids = explode(":", $id);
		$item = Item::get((int)$ids[0], !empty($ids[1])?(int)$ids[1]:0, !empty($ids[2])?(int)$ids[2]:1);
		if(!empty($ids[3]))
		{
			$item->setCustomName($ids[3]);
		}
		return $item;
	}

	public function getItem() : Item
	{
		return $this->getValues();
	}

	public function setItem(Item $item)
	{
		$this->setValues($item);
	}

	public function execute()
	{
		$player = $this->getPlayer();
		$item = $this->getItem();
		$player->getInventory()->addItem($item);
	}
}