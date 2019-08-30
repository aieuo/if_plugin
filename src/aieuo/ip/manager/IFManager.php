<?php

namespace aieuo\ip\manager;

use aieuo\ip\recipe\IFBlock;
use aieuo\ip\Session;
use aieuo\ip\Main;

abstract class IFManager {

    const BLOCK = 0;
    const COMMAND = 1;
    const EVENT = 2;
    const CHAIN = 3;
    const FORM = 4;

    /** @var IFBlock[] */
    protected $ifs = [];

    /** @var string */
    protected $saveDir;

    public function __construct(Main $owner, string $type) {
        $this->owner = $owner;
        $this->saveDir = $owner->getDataFolder().$type."/";
        if (!file_exists($this->saveDir)) @mkdir($this->saveDir, 0666, true);
        $this->loadIFs();
    }

    abstract public function loadIFs();

    /**
     * @param string $key
     * @return boolean
     */
    public function exists(string $key) {
        return isset($this->ifs[$key]);
    }

    /**
     * @param string $key
     * @return IFBlock|null
     */
    public function get(string $key): ?IFBlock {
        return $this->ifs[$key] ?? null;
    }

    /**
     * @return IFBlock[]
     */
    public function getAll(): array {
        return $this->ifs;
    }

    /**
     * @param string $key
     * @param IFBlock $ifData
     * @return void
     */
    public function set(string $key, IFBlock $ifData) {
        $this->ifs[$key] = $ifData;
    }

    /**
     * @param string $key
     * @param IFBlock $ifData
     * @return void
     */
    public function remove(string $key) {
        if (!$this->exists($key)) return;
        unlink($this->saveDir.$this->get($key)->getName().".json");
        unset($this->ifs[$key]);
    }

    public function saveAll() {
        foreach ($this->getAll() as $ifData) {
            $ifData->save($this->saveDir);
        }
    }

    /**
     * @param Session $session
     * @return IFManager|null
     */
    public static function getBySession(Session $session): ?IFManager {
        $type = $session->get("if_type");
        if ($type === null) return null;
        switch ($type) {
            case IFManager::BLOCK:
                $manager = Main::getInstance()->getBlockIFManager();
                break;
            // case IFManager::COMMAND:
            //     $manager = IFPlugin::getInstance()->getCommandManager();
            //     break;
            // case IFManager::EVENT:
            //     $manager = IFPlugin::getInstance()->getEventManager();
            //     break;
            // case IFManager::CHAIN:
            //     $manager = IFPlugin::getInstance()->getChainManager();
            //     break;
            // case IFManager::FORM:
            //     $manager = IFPlugin::getInstance()->getFormIFManager();
            //     break;
            default:
                $manager = null;
        }
        return $manager;
    }
}