<?php

namespace aieuo\ip\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;

use aieuo\ip\ifAPI;
use aieuo\ip\Session;
use aieuo\ip\form\Form;
use aieuo\ip\utils\Messages;
use aieuo\ip\manager\ifManager;

class ifCommand extends PluginCommand implements CommandExecutor {

	public function __construct($owner) {
		parent::__construct('if', $owner);
		$this->setPermission('op');
		$this->setDescription("条件にあった時、イベントが起きた時に何かをする");
		$this->setUsage("if <block | command | event>");
		$this->setExecutor($this);
		$this->owner = $owner;
		$this->form = new Form();
	}

	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
		if(!$sender->isOp() or $sender->getName() === "CONSOLE") return true;
		$name = $sender->getName();

		if(!isset($args[0])){
			$form = $this->form->getSelectIfTypeForm();
			Form::sendForm($sender, $form, $this->form, "onSelectIfType");
			return true;
		}

		$session = Session::get($sender);
		switch ($args[0]) {
			case 'block':
				if(!isset($args[1])) {
	                $form = $this->form->getBlockForm()->getSelectActionForm();
	                Form::sendForm($sender, $form, $this->form->getBlockForm(), "onSelectAction");
	                break;
	            }
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
					case "copy":
						$sender->sendMessage("コピーするブロックを触ってください");
						break;
					case "cancel":
						$session->setValid(false);
						$sender->sendMessage("キャンセルしました");
						return true;
					default:
						$sender->sendMessage("Usage: /if block <edit|check|del|copy|cancel>");
						return true;
				}
				$session->setValid()->setIfType(ifManager::BLOCK)->setData("action", $args[1]);
				break;

			case 'command':
				if(!isset($args[1])){
	                $form = $this->form->getCommandForm()->getSelectActionForm();
	                Form::sendForm($sender, $form, $this->form->getCommandForm(), "onSelectAction");
					break;
				}
				$session->setValid()->setIfType(ifManager::COMMAND)->setData("action", $args[1]);
				$manager = $this->owner->getCommandManager();
				switch ($args[1]) {
					case "add":
					case "add_empty":
		                $form = $this->form->getCommandForm()->getAddCommandForm();
		                Form::sendForm($sender, $form, $this->form->getCommandForm(), "onAddCommand");
						break;
					case "edit":
						if(!isset($args[2])) {
			                $form = $this->form->getCommandForm()->getSelectCommandForm();
			                Form::sendForm($sender, $form, $this->form->getCommandForm(), "onSelectCommand");
			                break;
			            }
						if(!$manager->isAdded($args[2])) {
							$sender->sendMessage("そのコマンドはまだ追加されていません");
							$session->setValid(false);
							break;
						}
        				$session->setData("if_key", $args[2]);
						$datas = $manager->get($args[2]);
						$mes = Messages::createMessage($datas["if"], $datas["match"], $datas["else"]);
			            $form = $this->form->getCommandForm()->getEditIfForm($mes);
			            Form::sendForm($sender, $form, $this->form->getCommandForm(), "onEditIf");
						break;
					case "check":
						if(!isset($args[2])) {
			                $form = $this->form->getCommandForm()->getSelectCommandForm();
			                Form::sendForm($sender, $form, $this->form->getCommandForm(), "onSelectCommand");
							break;
						}
						if(!$manager->isAdded($args[2])) {
							$sender->sendMessage("そのコマンドはまだ追加されていません");
							$session->setValid(false);
							break;
						}
						$datas = $manager->get($args[2]);
						$mes = Messages::createMessage($datas["if"], $datas["match"], $datas["else"]);
						$sender->sendMessage($mes);
						$session->setValid(false);
						break;
					case "del":
						if(!isset($args[2])) {
			                $form = $this->form->getCommandForm()->getSelectCommandForm();
			                Form::sendForm($sender, $form, $this->form->getCommandForm(), "onSelectCommand");
							break;
						}
						if(!$manager->isAdded($args[2])) {
							$sender->sendMessage("そのコマンドはまだ追加されていません");
							$session->setValid(false);
							break;
						}
						$session->setData("if_key", $data[0]);
			            $form = $this->form->getConfirmDeleteForm();
			            Form::sendForm($sender, $form, $this->form, "onDeleteIf");
						break;
					case "cancel":
						$session->setValid(false);
						$sender->sendMessage("キャンセルしました");
						return true;
				}
				break;
			case 'event':
				$form = $this->form->getEventForm()->getSelectEventForm();
				Form::sendForm($sender, $form, $this->form->getEventForm(), "onSelectEvent");
				break;
			case "chain":
				if(isset($args[1])) {
					$session = Session::get($sender);
					switch ($args[1]) {
						case 'add':
			                $session->setData("action", "add");
			                $form = $this->form->getChainForm()->getAddChainIfForm();
			                Form::sendForm($sender, $form, $this->form->getChainForm(), "onAddChainIf");
							break;
						case 'edit':
                			$session->setData("action", "edit");
			                $form = $this->form->getChainForm()->getEditChainIfForm();
			                Form::sendForm($sender, $form, $this->form->getChainForm(), "onEditChainIf");
							break;
						case 'del':
                			$session->setData("action", "del");
			                $form = $this->form->getChainForm()->getEditChainIfForm();
			                Form::sendForm($sender, $form, $this->form->getChainForm(), "onEditChainIf");
							break;
						case 'list':
			                $form = $this->form->getChainForm()->getChainIfListForm();
			                Form::sendForm($sender, $form, $this->form->getChainForm(), "onChainIfList");
							break;
						default:
			                $form = $this->form->getChainForm()->getSelectActionForm();
			                Form::sendForm($sender, $form, $this->form->getChainForm(), "onselectAction");
							break;
					}
			        $session->setIfType(Session::CHAIN);
			        $session->setValid();
					return true;
				}
                $form = $this->form->getChainForm()->getSelectActionForm();
                Form::sendForm($sender, $form, $this->form->getChainForm(), "onselectAction");
				return true;
			case "import":
                $form = $this->form->getImportForm()->getImportListForm();
                Form::sendForm($sender, $form, $this->form->getImportForm(), "onImportList");
                break;
			default:
				$data = $this->form->getSelectIfTypeForm();
				Form::sendForm($sender, $data, $this->form, "onSelectIfType");
				break;
		}
		return true;
	}
}