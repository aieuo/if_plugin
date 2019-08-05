<?php

namespace aieuo\ip\manager;

use aieuo\ip\variable\StringVariable;
use aieuo\ip\variable\NumberVariable;

class ChainIfManager extends IFManager {

	public function __construct($owner) {
		parent::__construct($owner ,"chains");
	}

    public function set($key, $datas = [], $options = []) {
        $datas = $this->repairIF($datas);
        parent::set($key, $datas);
    }

    public function getReplaceDatas($datas) {
        $variables = parent::getReplaceDatas($datas);
        if(isset($datas["count"])) $variables["i"] = new NumberVariable("i", $datas["count"]);
        if(isset($datas["origin"])) {
            $origin = $datas["origin"];
            $add = [
                "origin_name" => new StringVariable("origin_name", $origin->getName()),
                "origin_pos" => new StringVariable("origin_pos", $origin->x.",".$origin->y.",".$origin->z.",".$origin->level->getFolderName()),
                "origin_x" => new NumberVariable("origin_x", $origin->x),
                "origin_y" => new NumberVariable("origin_y", $origin->y),
                "origin_z" => new NumberVariable("origin_z", $origin->z),
                "origin_level" => new StringVariable("origin_level", $origin->level->getFolderName())
            ];
            $variables = array_merge($variables, $add);
        }
        return $variables;
    }
}