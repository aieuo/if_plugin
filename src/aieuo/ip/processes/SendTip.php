<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SendTip extends TypeMessage {

    protected $id = self::SENDTIP;
    protected $name = "tip欄にメッセージを送る";
    protected $description = "tip欄にメッセージ§7<message>§fを送る";

	public function getMessage() {
		$message = $this->getSendMessage();
		return "tip欄に".$message."と送る";
	}

	public function execute() {
		$player = $this->getPlayer();
		$player->sendTip($this->getSendMessage());
	}
}