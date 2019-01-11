<?php

namespace aieuo\ip\processes;

use pocketmine\Server;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SendMessageToOp extends TypeMessage
{
	public $id = self::SENDMESSAGE_TO_OP;

	public function getName()
	{
		"opだけにメッセージを送る";
	}

	public function getDescription()
	{
		"opだけにメッセージ§7<message>§rを送る";
	}

	public function execute()
	{
    	$players = Server::getInstance()->getOnlinePlayers();
    	foreach ($players as $player) {
    		if($player->isOp()){
    			$player->sendMessage($this->getMessage());
    		}
    	}
	}
}