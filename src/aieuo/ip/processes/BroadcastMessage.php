<?php

namespace aieuo\ip\processes;

use pocketmine\Server;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class BroadcastMessage extends TypeMessage {

	protected $id = self::BROADCASTMESSAGE;
	protected $name = "全員にメッセージを送る";
	protected $description = "全員にメッセージ§7<message>§fを送る";

	public function getMessage() {
		$message = $this->getSendMessage();
		return "全員に".$message."と送る";
	}

	public function execute() {
		$player = $this->getPlayer();
        Server::getInstance()->broadcastMessage($this->getSendMessage());
	}
}