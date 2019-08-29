<?php

namespace aieuo\ip;

use pocketmine\Player;

class Session {

    /** @var array */
    private static $sessions = [];

    /**
     * @param  Player $player
     * @return Session|null
     */
    public static function getSession(Player $player): ?Session {
        return self::$sessions[$player->getName()] ?? null;
    }

    /**
     * @param Player $player
     * @return void
     */
    public static function createSession(Player $player): void {
        self::$sessions[$player->getName()] = new Session();
    }

////////////////////////////////////////////////////////////////////////

    /** @var bool */
    private $valid = false;
    /** @var array */
    private $datas = [];

    /**
     * @return boolean
     */
    public function isValid(): bool {
        return $this->valid;
    }

    /**
     * @param boolean $valid
     * @param boolean $deleteDatas
     * @return self
     */
    public function setValid(bool $valid = true, bool $deleteDatas = true): self {
        $this->valid = $valid;
        if (!$valid and $deleteDatas) $this->removeAll();
        return $this;
    }

    /**
     * @param string $key
     * @return boolean
     */
    public function exists(string $key): bool {
        return isset($this->datas[$key]);
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null) {
        if (!isset($this->datas[$key])) return $default;
        return $this->datas[$key];
    }

    /**
     * @param string $key
     * @param mixed $data
     * @return self
     */
    public function set(string $key, $data): self {
        $this->datas[$key] = $data;
        return $this;
    }

    /**
     * @return self
     */
    public function remove(string $key): self {
        unset($this->datas[$key]);
        return $this;
    }

    /**
     * @return self
     */
    public function removeAll(): self {
        $this->datas = [];
        return $this;
    }
}