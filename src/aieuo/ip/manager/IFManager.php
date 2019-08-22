<?php

namespace aieuo\ip\manager;

use pocketmine\utils\Config;
use pocketmine\Server;

use aieuo\ip\Session;
use aieuo\ip\IFPlugin;
use aieuo\ip\IFAPI;

class IFManager extends IFAPI {

    const BLOCK = 0;
    const COMMAND = 1;
    const EVENT = 2;
    const CHAIN = 3;
    const FORM = 4;

    /** @var IFPlugin */
    private $owner;
    /** @var Config */
    private $config;

    public function __construct($owner, $type) {
        $this->owner = $owner;
        $this->config = new Config($owner->getDataFolder() . $type. ".yml", Config::YAML, []);
    }

    public function getOwner(): IFPlugin {
        return $this->owner;
    }

    public function getServer(): Server {
        return $this->getOwner()->getServer();
    }

    public function getConfig(): Config {
        return $this->config;
    }

    /**
     * @param  string $key
     * @param  array  $options
     * @return array|null
     */
    public function get(string $key, array $options = []): ?array {
        if (!$this->exists($key)) return null;
        $datas = $this->config->get($key);
        $datas = $this->repairIF($datas);
        return $datas;
    }

    /**
     * @param string $key
     * @param array  $options
     * @return boolean
     */
    public function exists(string $key, array $options = []): bool {
        return $this->config->exists($key);
    }

    /**
     * @param string $key
     * @param array  $datas
     * @param array  $options
     */
    public function set(string $key, array $datas = [], array $options = []) {
        $this->config->set($key, $datas);
    }

    /**
     * @return array
     */
    public function getAll(): array {
        return $this->config->getAll();
    }

    /**
     * @param string $key
     * @param string $type
     * @param int    $id
     * @param string $content
     * @param array  $options
     */
    public function add($key, $type, $id, $content, $options = []) {
        $datas = [];
        if ($this->exists($key))$datas = $this->get($key);
        $datas = $this->repairIF($datas);
        $datas[$type][] = [
            "id" => $id,
            "content" => $content
        ];
        $this->config->set($key, $datas);
    }

    /**
     * @param  string $key
     * @param  string $type
     * @param  int $num
     * @return bool
     */
    public function del($key, $type, $num, $options = []) {
        if (!$this->exists($key)) return false;
        $datas = $this->get($key);
        unset($datas[$type][$num]);
        $datas[$type] = array_merge($datas[$type]);
        $this->config->set($key, $datas);
        return true;
    }

    /**
     * @param  string $key
     * @param  string $type
     * @param  int $num
     * @return bool
     */
    public function updateContent($key, $type, $num, $new, $options = []) {
        if (!$this->exists($key)) return false;
        $datas = $this->get($key);
        $datas[$type][$num]["content"] = $new;
        $this->config->set($key, $datas);
        return true;
    }

    /**
     * @param string $key
     * @param string $name
     * @param array $options
     */
    public function setName($key, $name, $options = []) {
        if (!$this->exists($key)) return false;
        $datas = $this->get($key);
        $datas["name"] = $name;
        $this->config->set($key, $datas);
        return true;
    }

    /**
     * @param  string $key
     */
    public function remove($key) {
        $this->config->remove($key);
    }

    public function save() {
        $this->config->save();
    }

    /**
     * @param  array $datas
     * @return array
     */
    public function repairIF($datas) {
        if (!isset($datas["if"]))$datas["if"] = [];
        if (!isset($datas["match"]))$datas["match"] = [];
        if (!isset($datas["else"]))$datas["else"] = [];
        return $datas;
    }

    public function getReplaceDatas($datas) {
        return parent::getReplaceDatas($datas);
    }
}