<?php

namespace aieuo\ip\manager;

use pocketmine\block\SignPost;
use pocketmine\tile\Sign;

use aieuo\ip\variable\StringVariable;
use aieuo\ip\variable\NumberVariable;
use aieuo\ip\variable\ListVariable;

class BlockManager extends IFManager {

    public function __construct($owner){
        parent::__construct($owner, "blocks");
    }

    public function set(string $key, array $data = [], array $options = []) {
        $data = $this->repairIF($data);
        parent::set($key, $data);
    }

    public function getPosition($block){
        return $block->x.",".$block->y.",".$block->z.",".$block->level->getFolderName();
    }

    public function getReplaceData($data) {
        $variables = parent::getReplaceData($data);
        $block = $data["block"];
        $event = $data["event"];
        $add = [
            "block" => new StringVariable("block", $block->__toString()),
            "block_name" => new StringVariable("block_name", $block->getName()),
            "block_id" => new NumberVariable("block_id", $block->getId()),
            "block_damage" => new NumberVariable("block_damage", $block->getDamage()),
            "block_ids" => new StringVariable("block_ids", $block->getId().":".$block->getDamage()),
            "block_pos" => new StringVariable("block_pos", $block->x.",".$block->y.",".$block->z.",".$block->level->getFolderName()),
            "block_x" => new NumberVariable("block_x", $block->x),
            "block_y" => new NumberVariable("block_y", $block->y),
            "block_z" => new NumberVariable("block_z", $block->z),
            "block_level" => new StringVariable("block_level", $block->level->getFolderName()),
            "touch_face" => new NumberVariable("touch_face", $event->getFace())
        ];
        if ($block instanceof SignPost) {
            $sign = $block->level->getTile($block);
            if ($sign instanceof Sign) {
                $variables["sign_lines"] = new ListVariable("sign_lines", $sign->getText());
            }
        }
        return array_merge($variables, $add);
    }
}