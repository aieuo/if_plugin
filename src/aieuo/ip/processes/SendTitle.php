<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SendTitle extends TypeMessage {

    protected $id = self::SENDTITLE;
    protected $name = "title欄にメッセージを送る";
    protected $description = "title欄にメッセージ§7<message>§fを送る";

	public function getMessage() {
		$message = $this->getSendMessage();
		return "title欄に".$message."と送る";
	}

	public function execute() {
		$player = $this->getPlayer();
        $player->addTitle($this->getSendMessage(), "", 20, 100, 20);
	}
}