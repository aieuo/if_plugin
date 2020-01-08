<?php

namespace aieuo\ip\manager;

use aieuo\ip\variable\StringVariable;
use aieuo\ip\variable\NumberVariable;

class ChainIfManager extends IFManager {

    public function __construct($owner) {
        parent::__construct($owner, "chains");
    }

    public function set($key, $data = [], $options = []) {
        $data = $this->repairIF($data);
        parent::set($key, $data);
    }

    public function getReplaceData($data) {
        $variables = parent::getReplaceData($data);
        if (isset($data["count"])) $variables["i"] = new NumberVariable("i", $data["count"]);
        if (isset($data["origin"])) {
            $origin = $data["origin"];
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
        if (isset($data["replaces"])) $variables = array_merge($data["replaces"], $variables);
        return $variables;
    }
}