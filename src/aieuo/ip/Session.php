<?php

namespace aieuo\ip;

use pocketmine\Player;

class Session {

    const BLOCK = 0;
    const COMMAND = 1;
    const EVENT = 2;
    const CHAIN = 3;
    const FORM = 4;

    private static $sessions = [];

    /**
     * @param  Player $player
     * @return Session|null
     */
    public static function getSession(Player $player): Session {
        if (!isset(self::$sessions[$player->getName()])) self::createSession($player);
        return self::$sessions[$player->getName()];
    }

    public static function createSession(Player $player) {
        self::$sessions[$player->getName()] = new Session();
    }

////////////////////////////////////////////////////////////////////////

    /** @var bool */
    private $valid = false;
    private $if_type = null;
    /** @var array */
    private $datas = [];

    public function isValid(): bool {
        return $this->valid;
    }

    public function setValid($valid = true, $deleteDatas = true): self {
        $this->valid = $valid;
        if (!$valid and $deleteDatas) $this->removeAll();
        return $this;
    }

    public function get($key, $default = null) {
        if (!isset($this->datas[$key])) return $default;
        return $this->datas[$key];
    }

    public function set($key, $data): self {
        $this->datas[$key] = $data;
        return $this;
    }

    public function remove($key) {
        unset($this->datas[$key]);
    }

    public function removeAll() {
        $this->datas = [];
    }
}