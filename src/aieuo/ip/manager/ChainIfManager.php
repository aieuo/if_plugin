<?php

namespace aieuo\ip\manager;

use aieuo\ip\variable\StringVariable;
use aieuo\ip\variable\NumberVariable;
use aieuo\ip\variable\ListVariable;

class ChainIfManager extends ifManager{

	public function __construct($owner){
		parent::__construct($owner ,"chains");
	}

    public function set($key, $datas = [], $args = []){
        $datas = $this->repairIF($datas);
        parent::set($key, $datas);
    }

    public function getReplaceDatas($datas) {
        $result = parent::getReplaceDatas($datas);
        if(isset($datas["count"])) $result["i"] = new NumberVariable("i", $datas["count"]);
        if(isset($datas["origin"])) {
            $origin = $datas["origin"];
            $variables = [
                "origin_name" => new StringVariable("origin_name", $origin->getName()),
                "origin_pos" => new StringVariable("origin_pos", $origin->x.",".$origin->y.",".$origin->z.",".$origin->level->getFolderName()),
                "origin_x" => new NumberVariable("origin_x", $origin->x),
                "origin_y" => new NumberVariable("origin_y", $origin->y),
                "origin_z" => new NumberVariable("origin_z", $origin->z),
                "origin_level" => new StringVariable("origin_level", $origin->level->getFolderName())
            ];
            $result = array_merge($result, $variables);
        }
        return $result;
    }
}