<?php

namespace aieuo\ip\conditions;

use pocketmine\math\Vector3;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class InArea extends Condition
{
	public $id = self::IN_AREA;

	public function __construct($player = null, $area = false)
	{
		parent::__construct($player);
		$this->setValues($area);
	}

	public function getName()
	{
		return "指定した範囲内にいたら";
	}

	public function getDescription()
	{
		return "プレイヤーが§7<x|y|z>§r軸が§7<min>§r～§7<max>§rの範囲にいたら";
	}

	public function getArea()
	{
		return $this->getValues();
	}

	public function setArea(Array $area)
	{
		$this->setValues($area);
	}

	public function parse(string $areas)
	{
        if(!preg_match("/([xyz]\(-?[0-9]+\.?[0-9]*,-?[0-9]+\.?[0-9]*\))+/", $areas, $matches)) return false;
        array_shift($matches);
        $areas = [];
        foreach ($matches as $match) {
            if(!preg_match("/([xyz])\((-?[0-9]+\.?[0-9]*),(-?[0-9]+\.?[0-9]*)\)/", $match, $matches1)) continue;
            $min = min((float)$matches1[2], (float)$matches1[3]);
            $max = max((float)$matches1[2], (float)$matches1[3]);
            $areas[$matches1[1]] = [$min, $max];
        }
        return $areas;
	}

	public function check()
	{
		$player = $this->getPlayer();
		$areas = $this->getArea();
		if($areas === false)
		{
			$player->sendMessage("§c[指定した範囲内にいたら] 正しく入力できていません§f");
			return self::ERROR;
		}
        foreach ($areas as $axis => $area)
        {
            if($player->$axis < $area[0] and $player->$axis > $area[1]) return self::NOT_MATCHED;
        }
        return self::MATCHED;
	}

	public function getEditForm(string $default = "", string $mes = "")
	{
		$areas = $this->parse($default);
		if($areas === false)
		{
			$areas = ["x" => $default, "y" => "", "z" => ""];
			if($default !== "") $mes .= "§c正しく入力できていません§f";
		}

		$content = [Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes))];
		foreach (["x", "y", "z"] as $axis)
		{
			if(!isset($areas[$axis])) $areas[$axis] = "";
			if(is_array(($areas[$axis]))) $areas[$axis] = implode(",", $areas[$axis]);
			$content[] = Elements::getInput("\n§7<".$axis.">§f ".$axis."軸の範囲を入力してください (指定しない場合は空白で)", "例) 0,100", $areas[$axis]);
		}
		$content[] = Elements::getToggle("削除する");
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => $content
        ];
        $json = Form::encodeJson($data);
        return $json;
	}
}