<?php

namespace aieuo\ip\manager;

use aieuo\ip\utils\Language;
use pocketmine\command\PluginCommand;

use aieuo\ip\variable\StringVariable;
use aieuo\ip\variable\ListVariable;

class CommandManager extends IFManager {

    private $command_list = [];

    public function __construct($owner) {
        parent::__construct($owner, "commands");
        $this->registerCommands();
    }

    public function set($key, $data = [], $options = []) {
        $data = $this->repairIF($data);
        if ($options["desc"] === "") $options["desc"] = Language::get("form.command.description.default");
        if ($options["perm"] === "") $options["perm"] = "ifplugin.customcommand.op";
        $data["description"] = $options["desc"];
        $data["permission"] = $options["perm"];
        parent::set($key, $data);
    }

    public function add($key, $type, $id, $content, $args = []) {
        $data = [];
        if ($this->exists($key))$data = $this->get($key);
        $data[$type][] = [
            "id" => $id,
            "content" => $content
        ];
        $this->register($key, $args["desc"], $args["perm"]);
        $this->set($key, $data, $args);
    }

    public function remove($key) {
        $this->unregister($key);
        parent::remove($key);
    }

    public function registerCommands() {
        foreach ($this->getAll() as $command => $value) {
            $permission = $value["permission"];
            switch ($permission) {
                case 'ifplugin.customcommand.op':
                case 'ifplugin.customcommand.true':
                    break;
                case true:
                case 'true':
                case 'default':
                    $permission = "ifplugin.customcommand.true";
                    break;
                default:
                    $permission = "ifplugin.customcommand.op";
                    break;
            }
            if ($this->isSubcommand($command)) $command = $this->getOriginCommand($command);
            if (!$this->isRegisterd($command)) {
                $this->register($command, $permission, $value["description"]);
            }
        }
    }

    public function register($command, $permission = null, $description = null) {
        $permission = $permission ?? "ifplugin.customcommand.op";
        $description = $description ?? Language::get("form.command.description.default");
        if ($this->isSubcommand($command)) $command = $this->getOriginCommand($command);
        if (!$this->isRegisterd($command)) {
            $newCommand = new PluginCommand($command, $this->getOwner());
            $newCommand->setDescription($description);
            $newCommand->setPermission($permission);
            $this->getServer()->getCommandMap()->register("ifPlugin", $newCommand);
            $this->command_list[$command] = $newCommand;
            return true;
        }
        return false;
    }

    public function unregister($command) {
        $count = count($this->getSubcommands($command));
        if (!$this->isSubcommand($command) and $this->exists($command)) $count ++;
        if ($count <= 1) {
            $this->getServer()->getCommandMap()->unregister($this->command_list[$command]);
        }
        unset($this->command_list[$command]);
    }

    public function isRegisterd($command) {
        $exist = $this->getServer()->getPluginCommand($command);
        if ($exist === null)return false;
        return true;
    }

    public function isSubcommand($command) {
        $subcommand = false;
        if (strpos($command, " ") !== false)$subcommand = true;
        return $subcommand;
    }

    public function getSubcommands($command) {
        $array = [];
        $command = explode(" ", $command)[0];
        $commands = $this->getAll();
        foreach ($commands as $cmd => $value) {
            $commands = explode(" ", $cmd);
            if (array_shift($commands) == $command) {
                if (isset($commands[0])) {
                    $sub = implode(" ", $commands);
                    $array[] = $sub;
                }
            }
        }
        return $array;
    }

    public function getOriginCommand($command) {
        if (!$this->isSubcommand($command)) return $command;
        $commands = explode(" ", $command);
        return $commands[0];
    }

    public function getReplaceData($data) {
        $result = parent::getReplaceData($data);
        $command = $data["command"];
        $commands = explode(" ", substr($command, 1));
        $result["cmd"] = new StringVariable("cmd", array_shift($commands));
        $result["args"] = new ListVariable("args", $commands);
        return $result;
    }
}