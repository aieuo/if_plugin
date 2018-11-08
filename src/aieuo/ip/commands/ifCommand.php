<?php

namespace aieuo\ip\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;

use aieuo\ip\form\Form;
use aieuo\ip\utils\Messages;
use aieuo\ip\Session;

class ifCommand extends PluginCommand implements CommandExecutor {

	public function __construct($owner){
		parent::__construct('if', $owner);
		$this->setPermission('op');
		$this->setDescription("条件にあった時、イベントが起きた時に何かをする");
		$this->setUsage("if <block | command | event>");
		$this->setExecutor($this);
		$this->owner = $owner;
	}

	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
		if(!$sender->isOp() or $sender->getName() === "CONSOLE")return true;
		$name = $sender->getName();
		if(!isset($args[0])){
			$form = Form::getSelectIfTypeForm();
			Form::sendForm($sender, $form, Form::getFormId("SelectIfTypeForm"));
			return true;
		}
		switch ($args[0]) {
			case 'block':
				if(isset($args[1])){
					$session = $sender->ifSession;
					$session->setValid();
					$session->setIfType(Session::BLOCK);
					switch ($args[1]) {
						case "add":
							$session->setData("type", "add");
							$sender->sendMessage("追加するブロックを触ってください");
							return true;
						case "add_empty":
							$session->setData("type", "add_empty");
							$sender->sendMessage("追加するブロックを触ってください");
							return true;
						case "edit":
							$session->setData("type", "edit");
							$sender->sendMessage("編集するブロックを触ってください");
							return true;
						case "check":
							$session->setData("type", "check");
							$sender->sendMessage("確認するブロックを触ってください");
							return true;
						case "del":
							$session->setData("type", "del");
							$sender->sendMessage("削除するブロックを触ってください");
							return true;
						case "cancel":
							$session->setValid(false);
							$sender->sendMessage("キャンセルしました");
							return true;
					}
					return true;
				}
				$form = Form::getSelectBlockActionForm();
				Form::sendForm($sender, $form, Form::getFormId("SelectBlockActionForm"));
				break;
			case 'command':
				if(isset($args[1])){
					$session = $sender->ifSession;
					$session->setValid();
					$session->setIfType(Session::COMMAND);
					$manager = $this->owner->getCommandManager();
					switch ($args[1]) {
						case "add":
							$session->setData("type", "add_");
							$form = Form::getAddCommandForm();
							Form::sendForm($sender, $form, Form::getFormId("AddCommandForm"));
							return true;
						case "add_empty":
							$session->setData("type", "add_empty");
							$form = Form::getAddCommandForm();
							Form::sendForm($sender, $form, Form::getFormId("AddCommandForm"));
							return true;
						case "edit":
							$session->setData("type", "edit");
							if(isset($args[2])){
								if(!$manager->isAdded($args[2])){
									$sender->sendMessage("そのコマンドはまだ追加されていません");
									$session->setValid(false);
									return true;
								}
								$datas = $manager->get($args[2]);
								$mes = Messages::createMessage($datas["if"], $datas["match"], $datas["else"]);
								$form = Form::getEditIfForm($mes);
								Form::sendForm($sender, $form, Form::getFormId("EditIfForm"));
								return true;
							}
							$form = Form::getSelectCommandForm();
							Form::sendForm($sender, $form, Form::getFormId("SelectCommandForm"));
							return true;
						case "check":
							$session->setData("type", "check");
							if(isset($args[2])){
								if(!$manager->isAdded($args[2])){
									$sender->sendMessage("そのコマンドはまだ追加されていません");
									$session->setValid(false);
									return true;
								}
								$datas = $manager->get($args[2]);
								$mes = Messages::createMessage($datas["if"], $datas["match"], $datas["else"]);
								$sender->sendMessage($mes);
								return true;
							}
							$form = Form::getSelectCommandForm();
							Form::sendForm($sender, $form, Form::getFormId("SelectCommandForm"));
							return true;
						case "del":
							$session->setData("type", "del");
							if(isset($args[2])){
								if(!$manager->isAdded($args[2])){
									$sender->sendMessage("そのコマンドはまだ追加されていません");
									$session->setValid(false);
									return true;
								}
								$manager->remove($args[2]);
								$sender->sendMessage("削除しました");
								return true;
							}
							$form = Form::getSelectCommandForm();
							Form::sendForm($sender, $form, Form::getFormId("SelectCommandForm"));
							return true;
						case "cancel":
							$session->setValid(false);
							$sender->sendMessage("キャンセルしました");
							return true;
					}
				}
				$form = Form::getSelectCommandActionForm();
				Form::sendForm($sender, $form, Form::getFormId("SelectCommandActionForm"));
				break;
			case 'event':
				if(isset($args[1])){
	                $session = $sender->ifSession;
	                $session->setValid();
	                $session->setIfType(Session::EVENT);
	                switch ($args[1]) {
	                    case "add":
	                    case "add_empty":
	                    case "edit":
	                    case "check":
	                    case "del":
	                        $session->setData("type", $args[1]);
	                        break;
	                    case "cancel":
	                        $session->setValid(false);
	                        $sender->sendMessage("キャンセルしました");
	                        break;
	                }
	                $form = Form::getSelectEventForm();
	                Form::sendForm($sender, $form, Form::getFormId("SelectEventForm"));
	                return true;
				}
                $form = Form::getSelectEventActionForm();
                Form::sendForm($sender, $form, Form::getFormId("SelectEventActionForm"));
				break;
			default:
				$form = Form::getSelectIfTypeForm();
				Form::sendForm($sender, $form, Form::getFormId("SelectIfTypeForm"));
				break;
		}
		return true;
	}
}