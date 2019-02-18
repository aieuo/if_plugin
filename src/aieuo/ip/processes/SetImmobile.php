<?php

namespace aieuo\ip\processes;

use pocketmine\item\Item;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SetImmobile extends Process {

    protected $id = self::SET_IMMOBILE;
    protected $name = "動けないようにする";
    protected $description = "プレイヤーを動けないようにする";

	public function getMessage() {
		return "プレイヤーを動けないようにする";
	}

	public function execute() {
		$player = $this->getPlayer();
		$player->setImmobile();
	}
}