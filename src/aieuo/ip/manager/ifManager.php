<?php

namespace aieuo\ip\manager;

use pocketmine\utils\Config;

use aieuo\ip\ifPlugin;
use aieuo\ip\ifAPI;

class ifManager extends ifAPI{

	private static $owner;
	private $config;

	public function __construct($owner, $type){
		self::$owner = $owner;
        $this->config = new Config($owner->getDataFolder() . $type. ".yml", Config::YAML, []);
	}

	public static function getOwner(){
		return self::$owner;
	}

	public function getServer(){
		return self::getOwner()->getServer();
	}

    /**
     * @return Config
     */
    public function getConfig(){
        return $this->config;
    }

    /**
     * @param  string  $key
     * @return boolean
     */
    public function isAdded($key){
    	return $this->config->exists($key);
    }

    public function get($key){
        if(!$this->isAdded($key))return false;
        $datas = $this->config->get($key);
        $change = false;
        foreach ($datas as $type => $data) {
            if(!is_array($data) or count($data) == 0)continue;
            if(!isset($data[0])){
                $datas[$type] = [];
                foreach ($data as $key => $value) {
                    $datas[$type][] = [
                        "id" => str_replace("id", "", $key),
                        "content" => $value
                    ];
                }
                $change = true;
            }
        }
        if(!isset($datas["if"]))$datas["if"] = [];
        if(!isset($datas["match"]))$datas["match"] = [];
        if(!isset($datas["else"]))$datas["else"] = [];
        if($change){
            $this->set($key, $datas);
        }
        return $datas;
    }

    /**
     * @return array
     */
    public function getAll(){
    	return $this->config->getAll();
    }

    public function set($key, $datas = []){
    	$this->config->set($key, $datas);
    }

    public function del($key, $type, $num){
        if(!$this->isAdded($key))return false;
        $datas = $this->get($key);
        unset($datas[$type][$num]);
        $datas[$type] = array_merge($datas[$type]);
        $this->set($key, $datas);
        return true;
    }

    /**
     * @param  string $key
     */
    public function remove($key){
    	$this->config->remove($key);
    }

	public function save(){
		$this->config->save();
	}

    public function add($key, $type, $id, $content){
        $datas = [];
        if($this->isAdded($key))$datas = $this->get($key);
        if(!isset($datas["if"]))$datas["if"] = [];
        if(!isset($datas["match"]))$datas["match"] = [];
        if(!isset($datas["else"]))$datas["else"] = [];
        $datas[$type][] = [
            "id" => $id,
            "content" => $content
        ];
        $this->config->set($key, $datas);
    }
}