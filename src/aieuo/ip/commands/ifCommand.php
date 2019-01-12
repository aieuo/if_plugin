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
		$this->form = new Form();
	}

	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
		if(!$sender->isOp() or $sender->getName() === "CONSOLE")return true;
		$name = $sender->getName();
		if(!isset($args[0])){
			$form = $this->form->getSelectIfTypeForm();
			Form::sendForm($sender, $form, $this->form, "onSelectIfType");
			return true;
		}
		switch ($args[0]) {
			case 'block':
				if(isset($args[1])){
					$session = $sender->ifSession;
					switch ($args[1]) {
						case "edit":
							$sender->sendMessage("編集するブロックを触ってください");
							break;
						case "check":
							$sender->sendMessage("確認するブロックを触ってください");
							break;
						case "del":
							$sender->sendMessage("削除するブロックを触ってください");
							break;
						case "cancel":
							$session->setValid(false);
							$sender->sendMessage("キャンセルしました");
							return true;
					}
					$session->setData("action", $args[1]);
					$session->setIfType(Session::BLOCK);
					$session->setValid();
					return true;
				}
                $form = $this->form->getBlockForm()->getSelectActionForm();
                Form::sendForm($sender, $form, $this->form->getBlockForm(), "onSelectAction");
				break;
			case 'command':
				if(isset($args[1])){
					$session = $sender->ifSession;
					$session->setValid();
					$session->setIfType(Session::COMMAND);
					$manager = $this->owner->getCommandManager();
					switch ($args[1]) {
						case "add":
						case "add_empty":
							$session->setData("action", $args[1]);
			                $form = $this->form->getCommandFOrm()->getAddCommandForm();
			                Form::sendForm($player, $form, $this->form->getCommandForm(), "onAddCommand");
							return true;
						case "edit":
							$session->setData("action", "edit");
							if(isset($args[2])){
								if(!$manager->isAdded($args[2])){
									$sender->sendMessage("そのコマンドはまだ追加されていません");
									$session->setValid(false);
									return true;
								}
                				$session->setData("if_key", $args[2]);
								$datas = $manager->get($args[2]);
								$mes = Messages::createMessage($datas["if"], $datas["match"], $datas["else"]);
					            $form = $this->form->getCommandForm()->getEditIfForm($mes);
					            Form::sendForm($player, $form, $this->form->getCommandForm(), "onEditIf");
								return true;
							}
			                $form = $this->form->getCommandForm()->getSelectCommandForm();
			                Form::sendForm($player, $form, $this->form->getCommandForm(), "onSelectCommand");
							return true;
						case "check":
							$session->setData("action", "check");
							if(isset($args[2])){
								if(!$manager->isAdded($args[2])){
									$sender->sendMessage("そのコマンドはまだ追加されていません");
									$session->setValid(false);
									return true;
								}
								$datas = $manager->get($args[2]);
								$mes = Messages::createMessage($datas["if"], $datas["match"], $datas["else"]);
								$sender->sendMessage($mes);
        						$session->setValid(false);
								return true;
							}
			                $form = $this->form->getCommandForm()->getSelectCommandForm();
			                Form::sendForm($player, $form, $this->form->getCommandForm(), "onSelectCommand");
							return true;
						case "del":
							$session->setData("action", "del");
							if(isset($args[2])){
								if(!$manager->isAdded($args[2])){
									$sender->sendMessage("そのコマンドはまだ追加されていません");
									$session->setValid(false);
									return true;
								}
        						$session->setData("if_key", $data[0]);
					            $form = $this->form->getConfirmDeleteForm();
					            Form::sendForm($player, $form, $this->form, "onDeleteIf");
								return true;
							}
			                $form = $this->form->getCommandForm()->getSelectCommandForm();
			                Form::sendForm($player, $form, $this->form->getCommandForm(), "onSelectCommand");
							return true;
						case "cancel":
							$session->setValid(false);
							$sender->sendMessage("キャンセルしました");
							return true;
					}
				}
                $form = $this->form->getCommandForm()->getSelectActionForm();
                Form::sendForm($sender, $form, $this->form->getCommandForm(), "onSelectAction");
				break;
			case 'event':
				$form = $this->form->getEventForm()->getSelectEventForm();
				Form::sendForm($sender, $form, $this->form->getEventForm(), "onSelectEvent");
				break;
			default:
				$data = $this->form->getSelectIfTypeForm();
				Form::sendForm($sender, $data, $this->form, "onSelectIfType");
				break;
		}
		return true;
	}
}