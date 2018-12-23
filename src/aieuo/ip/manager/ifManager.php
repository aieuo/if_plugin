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

    /**
     * @param  strign $key
     * @param  bool $args
     * @return bool | array
     */
    public function get($key, $args = []){
        if(!$this->isAdded($key))return false;
        $datas = $this->config->get($key);
        $datas = $this->repairIF($datas);
        return $datas;
    }

    /**
     * @return array
     */
    public function getAll(){
    	return $this->config->getAll();
    }

    /**
     * @param string $key
     * @param string $type
     * @param int $id
     * @param string $content
     * @param array  $args
     */
    public function add($key, $type, $id, $content, $args = []){
        $datas = [];
        if($this->isAdded($key))$datas = $this->get($key);
        $datas = $this->repairIF($datas);
        $datas[$type][] = [
            "id" => $id,
            "content" => $content
        ];
        $this->config->set($key, $datas);
    }

    /**
     * @param string $key
     * @param array  $datas
     * @param array  $args
     */
    public function set($key, $datas = [], $args = []){
    	$this->config->set($key, $datas);
    }

    /**
     * @param  string $key
     * @param  string $type
     * @param  int $num
     * @return bool
     */
    public function del($key, $type, $num, $args = []){
        if(!$this->isAdded($key))return false;
        $datas = $this->get($key);
        unset($datas[$type][$num]);
        $datas[$type] = array_merge($datas[$type]);
        $this->config->set($key, $datas);
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

    /**
     * @param  array $datas
     * @return array
     */
    public function repairIF($datas){
        if(!isset($datas["if"]))$datas["if"] = [];
        if(!isset($datas["match"]))$datas["match"] = [];
        if(!isset($datas["else"]))$datas["else"] = [];
        return $datas;
    }
}