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
}