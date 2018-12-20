<?php

namespace aieuo\ip\ifs;

use pocketmine\math\Vector3;

use aieuo\ip\form\Form;
use aieui\ip\form\Elements;

class InArea extends IFs
{
	public $id = self::IN_AREA;

	private $area = [];

	public function __construct($player = null, $area = [])
	{
		parent::__construct($player);

		$this->area = $area;
	}

	public function getName()
	{
		return "指定した範囲内にいたら";
	}

	public function getDescription()
	{
		return "プレイヤーが§7<x|y|z>§r軸が§7<min>§r～§7<max>§rの範囲にいたら";
	}

	public function getEditForm(string $defaults = "", string $mes = "")
	{
		$areas = $this->parse($defaults);
		if($areas === false)
		{
			$areas = ["x" => $defaults, "y" => "", "z" => ""];
			$mes = "§c正しく入力できていません§f";
		}
		if($mes !== "") $mes = "\n".$mes;
		$content = [Elements::getLabel($this->getDescription().$mes)];
		foreach (["x", "y", "z"] as $axis => $value)
		{
			if(!isset($areas[$axis])) $areas[$axis] = "";
			if(is_array(($areas[$axis]))) $areas[$axis] = implode(",", $areas[$axis]);
			$content[] = Elements::getInput("<".$axis.">\n".$axis."軸の範囲を入力してください (指定しない場合は空白で)", "例) 0,100", $areas[$axis]);
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

	public function parse(string $areas) : Array
	{
        if(!preg_match("/([xyz]\(-?[0-9]+\.?[0-9]*,-?[0-9]+\.?[0-9]*\))+/", $areas, $matches)) return false;//x(1,2)y(3,4)z(5,6)
        array_shift($matches);
        $checks = [];
        foreach ($matches as $match) {
            if(!preg_match("/([xyz])\((-?[0-9]+\.?[0-9]*),(-?[0-9]+\.?[0-9]*\)/", $match, $matches1)) continue;
            $min = min((float)$matches1[2], (float)$matches1[3]);
            $max = max((float)$matches1[2], (float)$matches1[3]);
            $checks[$matches1[1]] = [$min, $max];
        }
        return $checks;
	}

	/**
	 * @return Array | bool
	 */
	public function getArea()
	{
		return $this->area;
	}

	/**
	 * @param Array | bool $area
	 */
	public function setArea($area)
	{
		$this->area = $area;
	}

	public function check()
	{
		$player = $this->getPlayer();
		$checks = $this->getArea();
		if($checks === false)
		{
			$player->sendMessage("§c[指定した範囲内にいたら] 正しく入力できていません§f");
			return self::ERROR;
		}
        foreach ($checks as $axis => $area) {
            if($player->$axis < $area[0] and $player->$axis > $area[1]){
                $result = self::NOT_MATCHED;
            }
        }
        return self::MATCHED;
	}
}