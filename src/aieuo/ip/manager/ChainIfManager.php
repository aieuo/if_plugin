<?php

namespace aieuo\ip\manager;

class ChainIfManager extends ifManager{

	public function __construct($owner){
		parent::__construct($owner ,"chains");
	}

    public function set($key, $datas = [], $args = []){
        $datas = $this->repairIF($datas);
        parent::set($key, $datas);
    }
}