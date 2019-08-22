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
    public static function get(Player $player): ?Session {
        if(!isset(self::$sessions[$player->getName()])) return null;
        return self::$sessions[$player->getName()];
    }

    public static function register(Player $player) {
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
        if(!$valid and $deleteDatas) $this->removeAllData();
		return $this;
	}

	public function getIfType() : int {
		return $this->if_type;
	}

	public function setIfType($type) : self {
		$this->if_type = $type;
		return $this;
	}

    public function getData($key, $default = null) {
        if(!isset($this->datas[$key])) return $default;
        return $this->datas[$key];
	}

    public function setData($key, $data): self {
        $this->datas[$key] = $data;
		return $this;
	}

    public function removeData($key) {
        unset($this->datas[$key]);
	}

	public function removeAllData() {
		$this->datas = [];
	}
}