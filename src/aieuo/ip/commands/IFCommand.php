<?php

namespace aieuo\ip\commands;

use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\command\Command;
use pocketmine\Player;
use aieuo\ip\utils\Language;
use aieuo\ip\manager\IFManager;
use aieuo\ip\form\IFForm;
use aieuo\ip\form\BlockIFForm;
use aieuo\ip\Session;
use aieuo\ip\Main;

class IFCommand extends PluginCommand implements CommandExecutor {

    /** @var Main */
    private $owner;

    public function __construct(Main $owner) {
        parent::__construct('if', $owner);
        $this->setPermission('ifplugin.command.if');
        $this->setDescription(Language::get("command.if.description"));
        $this->setUsage(Language::get("command.if.usage"));
        $this->setExecutor($this);
        $this->owner = $owner;
    }

    private function getOwner(): Main {
        return $this->owner;
    }

    private function getForm(): IFForm {
        return new IFForm();
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool {
        if (!$sender->isOp()) return true;

        if (!($sender instanceof Player)) {
            $sender->sendMessage(Language::get("command.console"));
            return true;
        }

        if (!isset($args[0])) {
            $this->getForm()->sendSelectIFTypeForm($sender);
            return true;
        }

        $session = Session::getSession($sender);
        switch ($args[0]) {
            case "language":
                if (!isset($args[1])) {
                    $sender->sendMessage(Language::get("command.language.usage"));
                    return true;
                }
                $languages = [];
                foreach ($this->getOwner()->getResources() as $resource) {
                    $filename = $resource->getFilename();
                    if (strrchr($filename, ".") == ".ini") $languages[] = basename($filename, ".ini");
                    if ($filename === $args[1].".ini") {
                        $messages = parse_ini_file($resource->getPathname());
                    }
                }
                if (!isset($messages)) {
                    $available = implode(", ", $languages);
                    $sender->sendMessage(Language::get("command.language.notfound", [$args[1], $available]));
                    return true;
                }
                $this->getOwner()->language->setMessages($messages);
                $this->getOwner()->config->set("language", $args[1]);
                $sender->sendMessage(Language::get("language.selected", [Language::get("language.name")]));
                break;
            case "block":
                if (!isset($args[1])) {
                    (new BlockIFForm)->sendSelectActionForm($sender);
                    break;
                }
                switch ($args[1]) {
                    case "edit":
                        $sender->sendMessage(Language::get("command.block.edit"));
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
                $session->setValid()->set("if_type", IFManager::BLOCK)->set("blockIF_action", $args[1]);
                break;
        }
        return true;
    }
}