<?php

namespace aieuo\ip\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;

use aieuo\ip\Session;
use aieuo\ip\form\Form;
use aieuo\ip\utils\Messages;
use aieuo\ip\utils\Language;
use aieuo\ip\manager\IFManager;

class IFCommand extends PluginCommand implements CommandExecutor {

	public function __construct($owner) {
		parent::__construct('if', $owner);
		$this->setPermission('op');
		$this->setDescription(Language::get("command.if.description"));
		$this->setUsage("if <block | command | event | chain | import | language>");
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
			case "language":
				if(!isset($args[1])) {
					$sender->sendMessage(Language::get("command.language.usage"));
					return true;
				}
				$languages = [];
		        foreach($this->owner->getResources() as $resource) {
                    $filename = $resource->getFilename();
                    if (strrchr($filename, ".") == ".ini") $languages[] = basename($filename, ".ini");
                    if ($filename === $args[1].".ini") {
		                $messages = parse_ini_file($resource->getPathname());
		            }
		        }
		        if(!isset($messages)) {
                    $available = implode(", ", $languages);
                    $sender->sendMessage(Language::get("command.language.notfound", [$args[1], $available]));
		        	return true;
		        }
		        $this->owner->language->setMessages($messages);
		        $this->owner->config->set("language", $args[1]);
		        $sender->sendMessage(Language::get("language.selected", [Language::get("language.name")]));
				break;
			case 'block':
				if(!isset($args[1])) {
	                $form = $this->form->getBlockForm()->getSelectActionForm();
	                Form::sendForm($sender, $form, $this->form->getBlockForm(), "onSelectAction");
	                break;
	            }
				switch ($args[1]) {
					case "edit":
						$sender->sendMessage(Language::get("command.block.edit"));
						break;
					case "check":
						$sender->sendMessage(Language::get("command.block.check"));
						break;
					case "del":
						$sender->sendMessage(Language::get("command.block.del"));
						break;
					case "copy":
						$sender->sendMessage(Language::get("command.block.copy"));
						break;
					case "cancel":
						$session->setValid(false);
						$sender->sendMessage(Language::get("command.block.cancel"));
						return true;
					default:
						$sender->sendMessage(Language::get("command.block.usage"));
						return true;
				}
                $session->setValid()->setIfType(IFManager::BLOCK)->setData("action", $args[1]);
				break;
			case 'command':
				if(!isset($args[1])){
	                $form = $this->form->getCommandForm()->getSelectActionForm();
	                Form::sendForm($sender, $form, $this->form->getCommandForm(), "onSelectAction");
					break;
				}
                $session->setValid()->setIfType(IFManager::COMMAND)->setData("action", $args[1]);
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
							$sender->sendMessage(Language::get("command.command.not_added"));
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
							$sender->sendMessage(Language::get("command.command.not_added"));
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
							$sender->sendMessage(Language::get("command.command.not_added"));
							$session->setValid(false);
							break;
						}
						$session->setData("if_key", $args[2]);
			            $form = $this->form->getConfirmDeleteForm();
			            Form::sendForm($sender, $form, $this->form, "onDeleteIf");
						break;
					case "cancel":
						$session->setValid(false);
						$sender->sendMessage(Language::get("command.command.cancel"));
						return true;
					default:
						$sender->sendMessage(Language::get("command.command.usage"));
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
