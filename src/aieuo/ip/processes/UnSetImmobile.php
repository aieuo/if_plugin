<?php

namespace aieuo\ip\processes;

use pocketmine\item\Item;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class UnSetImmobile extends Process {

	protected $id = self::UNSET_IMMOBILE;
    protected $name = "動けるようにする";
    protected $description = "プレイヤーを動けるようにする";

	public function getMessage() {
		return "プレイヤーを動けるようにする";
	}

	public function execute() {
		$player = $this->getPlayer();
		$player->setImmobile(false);
	}
}