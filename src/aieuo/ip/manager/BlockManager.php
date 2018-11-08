<?php

namespace aieuo\ip\manager;

class BlockManager extends ifManager{

	public function __construct($owner){
		parent::__construct($owner ,"blocks");
		if(!file_exists($this->getDataFolder()."blocks.yml")){
	        if(file_exists($this->getDataFolder()."if.db")) {
	            $db = new \SQLite3($this->getDataFolder()."if.db", SQLITE3_OPEN_READWRITE);
	        	$this->moveDB($db);
	        }
	    }
	}

	public function moveDB($db){
		$res = $db->query("SELECT * FROM list");
		while($row = $res->fetchArray()) {
			$data = [
				"if" => [
					["id" => $row["if"], "content" => $row["if_content"]]
				],
				"match" => [
					["id" => $row["type1"]+100, "content" => $row["type1_content"]]
				],
				"else" => [
					["id" => $row["type2"]+100, "content" => $row["type2_content"]]
				]
			];
			$this->set($row["pos"], $data);
		}
	}

    public function getPosition($block){
        return $block->x.",".$block->y.",".$block->z.",".$block->level->getFolderName();
    }

    public function set($key, $data = []){
        if(!isset($datas["if"]))$datas["if"] = [];
        if(!isset($datas["match"]))$datas["match"] = [];
        if(!isset($datas["else"]))$datas["else"] = [];
        parent::set($key, $data);
    }
}