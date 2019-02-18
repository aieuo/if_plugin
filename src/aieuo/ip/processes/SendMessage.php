<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SendMessage extends TypeMessage {

    protected $id = self::SENDMESSAGE;
    protected $name = "チャット欄にメッセージを送る";
    protected $description = "チャット欄にメッセージ§7<message>§fを送る";

	public function getMessage() {
		$message = $this->getSendMessage();
		return "チャット欄に".$message."と送る";
	}

	public function execute() {
		$player = $this->getPlayer();
		$player->sendMessage($this->getSendMessage());
	}
}