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

    /**
     * @param Session $session
     * @return IFManager|null
     */
    public static function getBySession(Session $session): ?IFManager {
        $type = $session->get("if_type");
        if ($type === null) return null;
        switch ($type) {
            case IFManager::BLOCK:
                $manager = IFPlugin::getInstance()->getBlockManager();
                break;
            case IFManager::COMMAND:
                $manager = IFPlugin::getInstance()->getCommandManager();
                break;
            case IFManager::EVENT:
                $manager = IFPlugin::getInstance()->getEventManager();
                break;
            case IFManager::CHAIN:
                $manager = IFPlugin::getInstance()->getChainManager();
                break;
            case IFManager::FORM:
                $manager = IFPlugin::getInstance()->getFormIFManager();
                break;
            default:
                $manager = null;
        }
        return $manager;
    }

    public function __construct(IFPlugin $owner, string $type) {
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
        $data = $this->config->get($key);
        $data = $this->repairIF($data);
        return $data;
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
     * @param array  $data
     * @param array  $options
     */
    public function set(string $key, array $data = [], array $options = []) {
        $this->config->set($key, $data);
        if (IFPlugin::getInstance()->saveOnChange) $this->config->save();
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
        $data = [];
        if ($this->exists($key))$data = $this->get($key);
        $data = $this->repairIF($data);
        $data[$type][] = [
            "id" => $id,
            "content" => $content
        ];
        $this->config->set($key, $data);
        if (IFPlugin::getInstance()->saveOnChange) $this->config->save();
    }

    /**
     * @param  string $key
     * @param  string $type
     * @param  int $num
     * @return bool
     */
    public function del($key, $type, $num, $options = []) {
        if (!$this->exists($key)) return false;
        $data = $this->get($key);
        unset($data[$type][$num]);
        $data[$type] = array_merge($data[$type]);
        $this->config->set($key, $data);
        if (IFPlugin::getInstance()->saveOnChange) $this->config->save();
        return true;
    }

    /**
     * @param string $key
     * @param string $type
     * @param int $num
     * @param $new
     * @param array $options
     * @return bool
     */
    public function updateContent($key, $type, $num, $new, $options = []) {
        if (!$this->exists($key)) return false;
        $data = $this->get($key);
        $data[$type][$num]["content"] = $new;
        $this->config->set($key, $data);
        if (IFPlugin::getInstance()->saveOnChange) $this->config->save();
        return true;
    }

    /**
     * @param string $key
     * @param string $name
     * @param array $options
     * @return bool
     */
    public function setName($key, $name, $options = []) {
        if (!$this->exists($key)) return false;
        $data = $this->get($key);
        $data["name"] = $name;
        $this->config->set($key, $data);
        if (IFPlugin::getInstance()->saveOnChange) $this->config->save();
        return true;
    }

    /**
     * @param string $key
     * @param array $options
     */
    public function remove(string $key, array $options = []) {
        $this->config->remove($key);
        if (IFPlugin::getInstance()->saveOnChange) $this->config->save();
    }

    public function save() {
        $this->config->save();
    }

    /**
     * @param  array $data
     * @return array
     */
    public function repairIF($data) {
        if (!isset($data["if"]))$data["if"] = [];
        if (!isset($data["match"]))$data["match"] = [];
        if (!isset($data["else"]))$data["else"] = [];
        return $data;
    }

    public function getReplaceData($data) {
        return parent::getReplaceData($data);
    }
}