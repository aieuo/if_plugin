<?php

namespace aieuo\ip\processes;

use pocketmine\Server;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SendMessageToOp extends TypeMessage {

    protected $id = self::SENDMESSAGE_TO_OP;
    protected $name = "opだけにメッセージを送る";
    protected $description = "opだけにメッセージ§7<message>§fを送る";

	public function getMessage() {
		$message = $this->getSendMessage();
		return "opだけに".$message."と送る";
	}

	public function execute() {
    	$players = Server::getInstance()->getOnlinePlayers();
    	foreach ($players as $player) {
    		if($player->isOp()){
    			$player->sendMessage($this->getSendMessage());
    		}
    	}
	}
}