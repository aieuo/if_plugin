<?php

namespace aieuo\ip\processes;

use pocketmine\item\Item;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class TypeItem extends Process
{
	public function __construct($player = null, $item = false)
	{
		parent::__construct($player);
		$this->setValues($item);
	}

	public function getItem()
	{
		return $this->getValues();
	}

	public function setItem(Item $item)
	{
		$this->setValues($item);
	}

	public function parse(string $id)
	{
		if(!preg_match("/\s*([0-9]+)\s*:?\s*([0-9]*)\s*:?\s*([0-9]*)\s*:?\s*(\.*)\s*/", $id, $ids)) return false;
		$item = Item::get((int)$ids[1], empty($ids[2]) ? 0 : (int)$ids[2], empty($ids[3]) ? 0 : (int)$ids[3]);
		if(!empty($ids[3])) $item->setCustomName($ids[3]);
		return $item;
	}

	public function toString() : string
	{
		$item = $this->getItem();
		if(!($item instanceof Item)) return (string)$item;
		$str = $item->getId().":".$item->getDamage().":".$item->getCount().($item->hasCustomName() ? ":".$item->getName() : "");
		return $str;
	}


	public function getEditForm(string $default = "", string $mes = "")
	{
		$item = $this->parse($default);
		$id = $default;
		$count = "";
		$name = "";
		if($item instanceof Item)
		{
			$id = $item->getId().":".$item->getDamage();
			$count = $item->getCount();
			$name = $item->hasCustomName() ? $item->getName() : "";
			if($count === 0) $mes .= "§e個数が0になっています§f";
		}
		elseif($default !== "")
		{
			$mes .= "§c正しく入力できていません\n(idは数字で0以上の数を入力してください)§f";
		}
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("§7<id>§f アイテムのidを入力してください", "例) 1:0", $id),
                Elements::getInput("§7<count>§f アイテムの数を入力してください", "例) 5", $count),
                Elements::getInput("§7<name>§f アイテムに付けたい名前を入力してください(空白ならそのままの名前です)", "例) aieuo", $name),
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
    	$ids_str = $id[0].":".$id[1].":".$datas[2].($datas[3] !== "" ? ":".$datas[3] : "");
    	if($datas[1] === "" or $datas[2] === "") {
    		$status = null;
    	} else {
	    	$ids = $this->parse($ids_str);
	    	if($ids === false) $status = false;
	    }
    	return ["status" => $status, "contents" => $ids_str, "delete" => $datas[4], "cancel" => $datas[5]];
    }
}