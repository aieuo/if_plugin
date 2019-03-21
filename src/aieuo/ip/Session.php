<?php

namespace aieuo\ip;

use pocketmine\Player;

class Session {

	const BLOCK = 0;
	const COMMAND = 1;
	const EVENT = 2;
	const CHAIN = 3;

    private static $sessions = [];

    /**
     * @param  Player $player
     * @return Session
     */
    public static function get(Player $player) : Session {
        if(!isset(self::$sessions[$player->getName()])) self::$sessions[$player->getName()] = new Session();
        return self::$sessions[$player->getName()];
    }


	private $valid = false;
	private $if_type = self::BLOCK;
	private $datas = [];

	public function isValid() {
		return $this->valid;
	}

	public function setValid($valid = true, $del = true) : self {
		$this->valid = $valid;
		if(!$valid and $del) $this->removeAllData();
		return $this;
	}

	public function getIfType() : int {
		return $this->if_type;
	}

	public function setIfType($type) : self {
		$this->if_type = $type;
		return $this;
	}

	public function getData($id, $default = null) {
		if(!isset($this->datas[$id])) return $default;
		return $this->datas[$id];
	}

	public function setData($id, $data) : self {
		$this->datas[$id] = $data;
		return $this;
	}

	public function removeData($id) {
		unset($this->datas[$id]);
	}

	public function removeAllData() {
		$this->datas = [];
	}
}