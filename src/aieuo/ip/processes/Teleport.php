<?php

namespace aieuo\ip\processes;

use pocketmine\level\Position;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class Teleport extends TypePosition {

    protected $id = self::TELEPORT;
    protected $name = "テレポートする";
    protected $description = "§7<pos>§fにテレポートする";

	public function getMessage() {
		$pos = $this->getPosition();
		if($pos === false) return false;
		return $pos->__toString()."にテレポートする";
	}

	public function execute() {
		$player = $this->getPlayer();
		$pos = $this->getPosition();
		if(!($pos instanceof Position)) {
			$player->sendMessage("§c[".$this->getName()."] 正しく入力できていません");
			return;
		}
        $player->teleport($pos);
	}
}