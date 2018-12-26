<?php

namespace aieuo\ip\processes;

use pocketmine\lang\TranslationContainer;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SendVoiceMessage extends Process
{
	public $id = self::SENDVOICEMESSAGE;

	public function __construct($player = null, $message = null)
	{
		parent::__construct($player);
		$this->setValues($message);
	}

	public function getName()
	{
		"音声付きのメッセージを送る";
	}

	public function getDescription()
	{
		"音声付きのメッセージ§7<message>§rを送る";
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
		$player = $this->getPlayer();
        $text = new TranslationContainer($this->getMessage());
        $player->sendMessage($text);
	}
}