<?php

namespace aieuo\ip\processes;

use pockemine\Server;
use pocketmine\command\ConsoleCommandSender;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class CommandConsole extends Process
{
	public $id = self::COMMAND_CONSOLE;

	public function __construct($player = null, $command = null)
	{
		parent::__construct($player);
		$this->setValues($command);
	}

	public function getName()
	{
		"コンソールからコマンドを実行する";
	}

	public function getDescription()
	{
		"コンソールからコマンド§7<command>§rを実行する";
	}

	public function getCommand()
	{
		return $this->getValues();
	}

	public function setCommand(string $command)
	{
		$this->setValues($command);
	}

	public function getEditForm(string $defaults = "", string $mes = "")
	{
		if($mes !== "") $mes = "\n".$mes;
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().$mes),
                Elements::getInput("<command>\n実行するコマンドを入力してください", "例) help", $defaults),
                Elements::getToggle("削除する")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

	public function excute()
	{
        Server::getInstance()->dispatchCommand(new ConsoleCommandSender, $this->getCommand());
	}
}