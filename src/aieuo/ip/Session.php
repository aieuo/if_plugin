<?php

namespace aieuo\ip;

class Session {

	const BLOCK = 0;
	const COMMAND = 1;
	const EVENT = 2;
	const CHAIN = 3;

	private $valid = false;
	private $if_type = self::BLOCK;
	private $datas = [];

	public function isValid(){
		return $this->valid;
	}

	public function setValid($valid = true, $del = true){
		$this->valid = $valid;
		if(!$valid and $del)$this->removeAllData();
	}

	public function getIfType(){
		return $this->if_type;
	}

	public function setIfType($type){
		if($type !== self::BLOCK and $type !== self::COMMAND and $type !== self::EVENT and $type !== self::CHAIN)$type = self::BLOCK;
		$this->if_type = $type;
	}

	public function getData($id){
		if(!isset($this->datas[$id]))return "";
		return $this->datas[$id];
	}

	public function setData($id, $data){
		$this->datas[$id] = $data;
	}

	public function removeData($id){
		unset($this->datas[$id]);
	}

	public function removeAllData(){
		$this->datas = [];
	}
}