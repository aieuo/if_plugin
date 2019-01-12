<?php

namespace aieuo\ip\processes;

use pocketmine\item\Item;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SetItem extends Process
{
	public $id = self::SET_ITEM;

	public function getName()
	{
		return "インベントリの指定した場所にアイテムを追加する";
	}

	public function getDescription()
	{
		return "インベントリの§7<index>§fにidが§7<id>§fの§7<name>§fという名前のアイテムを§7<count>§f追加する";
	}

	public function getIndex()
	{
		return $this->getValues()[0];
	}

	public function getItem()
	{
		return $this->getValues()[1];
	}

	public function setItems(int $index, Item $item)
	{
		$this->setValues([$index, $item]);
	}

	public function parse(string $id)
	{
		if(!preg_match("/\s*([0-9]+)\s*,\s*([0-9]+)\s*:?\s*([0-9]*)\s*:?\s*([0-9]*)\s*:?\s*(\.*)\s*/", $id, $ids)) return false;
		$item = Item::get((int)$ids[2], empty($ids[3]) ? 0 : (int)$ids[3], empty($ids[4]) ? 0 : (int)$ids[4]);
		if(!empty($ids[5])) $item->setCustomName($ids[5]);
		return [(int)$ids[1], $item];
	}

	public function execute()
	{
		$player = $this->getPlayer();
		$item = $this->getItem();
		$index = $this->getIndex();
		if(!($item instanceof Item))
		{
			$player->sendMessage("§c[".$this->getName()."] 正しく入力できていません");
			return;
		}
		$player->getInventory()->setItem($index, $item);
	}


	public function getEditForm(string $default = "", string $mes = "")
	{
		$items = $this->parse($default);
		$id = $default;
		$count = "";
		$name = "";
		$index = "";
		if($items === false and $default !== "")
		{
			$mes .= "§c正しく入力できていません\n(idは数字で0以上の数を入力してください)§f";
		}
		elseif($items[1] instanceof Item)
		{
			$item = $items[1];
			$id = $item->getId().":".$item->getDamage();
			$count = $item->getCount();
			$name = $item->hasCustomName() ? $item->getName() : "";
			if($count === 0) $mes .= "§e個数が0になっています§f";
		}
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("§7<id>§f アイテムのidを入力してください", "例) 1:0", $id),
                Elements::getInput("§7<count>§f アイテムの数を入力してください", "例) 5", $count),
                Elements::getInput("§7<name>§f アイテムに付けたい名前を入力してください(空白ならそのままの名前です)", "例) aieuo", $name),
                Elements::getInput("§7<index>§f アイテムを追加する場所を入力してください", "例) 0", $index),
                Elements::getToggle("削除する"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

    public function parseFormData(array $datas) {
    	$status = true;
    	$id = explode(":", $datas[1]);
    	if(!isset($id[1])) $id[1] = 0;
    	$ids_str = $datas[4].",".$id[0].":".$id[1].":".$datas[2].($datas[3] !== "" ? ":".$datas[3] : "");
    	if($datas[1] === "" or $datas[2] === "" or $datas[4] === "") {
    		$status = null;
    	} else {
	    	$ids = $this->parse($ids_str);
	    	if($ids === false) $status = false;
	    }
    	return ["status" => $status, "contents" => $ids_str, "delete" => $datas[5], "cancel" => $datas[6]];
    }
}