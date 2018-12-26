<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SendMessageToOp extends Process
{
	public $id = self::SENDMESSAGE_TO_OP;

	public function __construct($player = null, $message = null)
	{
		parent::__construct($player);
		$this->setValues($message);
	}

	public function getName()
	{
		"opだけにメッセージを送る";
	}

	public function getDescription()
	{
		"opだけにメッセージ§7<message>§rを送る";
	}

	public function getMessage()
	{
		return $this->getValues();
	}

	public function setMessage(string $message)
	{
		$this->setValues($message);
	}

	public function getEditForm(string $defaults = "", string $mes = "")
	{
		if($mes !== "") $mes = "\n".$mes;
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().$mes),
                Elements::getInput("<message>\n送るメッセージを入力してください", "例) aieuo", $defaults),
                Elements::getToggle("削除する")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

	public function excute()
	{
    	$players = $player->getServer()->getOnlinePlayers();
    	foreach ($players as $player) {
    		if($player->isOp()){
    			$player->sendMessage($this->getMessage());
    		}
    	}
	}
}