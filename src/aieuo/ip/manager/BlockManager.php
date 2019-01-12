<?php

namespace aieuo\ip\manager;

class BlockManager extends ifManager{

	public function __construct($owner){
		parent::__construct($owner ,"blocks");
	}

    public function set($key, $datas = [], $args = []){
        $datas = $this->repairIF($datas);
        parent::set($key, $datas);
    }

    public function getPosition($block){
        return $block->x.",".$block->y.",".$block->z.",".$block->level->getFolderName();
    }

    public function replaceDatas($string, $datas) {
        $string = parent::replaceDatas($string, $datas);
        $block = $datas["block"];
        $event = $datas["event"];
        $item = $event->getItem();
        $variables = [
            "{block}" => $block->__toString(),
            "{block_name}" => $block->getName(),
            "{block_id}" => $block->getId(),
            "{block_damage}" => $block->getDamage(),
            "{block_ids}" => $block->getId().":".$block->getDamage(),
            "{block_pos}" => $block->x.",".$block->y.",".$block->z.",".$block->level->getFolderName(),
            "{block_x}" => $block->x,
            "{block_y}" => $block->y,
            "{block_z}" => $block->z,
            "{block_level}" => $block->level->getFolderName(),
            "{touch_action}" => $event->getAction(),
            "{touch_face}" => $event->getFace(),
            "{item}" => $item->__toString(),
            "{item_name}" => $item->getName(),
            "{item_id}" => $item->getId(),
            "{item_damage}" => $item->getDamage(),
            "{item_ids}" => $item->getId().":".$item->getDamage(),
            "{item_count}" => $item->getCount(),
        ];
        foreach ($variables as $key => $value) {
            $string = str_replace($key, $value, $string);
        }
        return $string;
    }
}