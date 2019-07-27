<?php

namespace aieuo\ip\conditions;

use pocketmine\math\Vector3;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class InArea extends Condition {

	protected $id = self::IN_AREA;
    protected $name = "@condition.inarea.name";
    protected $description = "@condition.inarea.description";

	public function __construct($player = null, $area = false) {
		parent::__construct($player);
		$this->setValues($area);
	}

	public function getMessage() {
		$areas = $this->getArea();
		if($areas === false) return false;
        $message = Language::get("condition.inarean.detail1");
		$mes = [];
		foreach ($areas as $axis => $area) {
			$mes[] = Language::get("condition.inarea.detail2", [$axis, $area[0], $area[1]]);
		}
		return $message.Language::get("condition.inarean.detail3", implode(",", $mes));
	}

	public function getArea() {
		return $this->getValues();
	}

	public function setArea(Array $area) {
		$this->setValues($area);
	}

	public function parse(string $areas) {
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

	public function check() {
		$player = $this->getPlayer();
		$areas = $this->getArea();
		if($areas === false) {
			$player->sendMessage(Language::get("input.invalid", [$this->getName()]));
			return self::ERROR;
		}
        foreach ($areas as $axis => $area) {
            if($player->$axis < $area[0] and $player->$axis > $area[1]) return self::NOT_MATCHED;
        }
        return self::MATCHED;
	}

	public function getEditForm(string $default = "", string $mes = "") {
		$areas = $this->parse($default);
		if($areas === false) {
			$areas = ["x" => $default, "y" => "", "z" => ""];
			if($default !== "") $mes .= Language::get("form.error");
		}

		$content = [Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes))];
		foreach (["x", "y", "z"] as $axis) {
			if(!isset($areas[$axis])) $areas[$axis] = "";
			if(is_array(($areas[$axis]))) $areas[$axis] = implode(",", $areas[$axis]);
			$content[] = Elements::getInput(Language::get("condition.inarea.form.area"), Language::get("input.example", ["0,100"]), $areas[$axis]);
		}
		$content[] = Elements::getToggle(Language::get("form.delete"));
		$content[] = Elements::getToggle(Language::get("form.cancel"));
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => $content
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

    public function parseFormData(array $datas) {
    	$status = true;
    	$area_str = $datas[1] !== "" ? "x(".$datas[1].")" : "";
    	$area_str .= $datas[2] !== "" ? "y(".$datas[2].")" : "";
    	$area_str .= $datas[3] !== "" ? "z(".$datas[3].")" : "";
    	if($datas[1] === "" and $datas[2] === "" and $datas[3] === "") {
    		$status = null;
    	} else {
	    	$areas = $this->parse($area_str);
	    	if($areas == false) $status = false;
	    }
    	return ["status" => $status, "contents" => $area_str, "delete" => $datas[4], "cancel" => $datas[5]];
    }
}