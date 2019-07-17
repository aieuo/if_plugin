<?php

namespace aieuo\ip\manager;

use pocketmine\command\Command;
use pocketmine\command\PluginCommand;

use aieuo\ip\variable\StringVariable;
use aieuo\ip\variable\NumberVariable;
use aieuo\ip\variable\ListVariable;

class CommandManager extends ifManager{

    private $command_list = [];

	public function __construct($owner){
        parent::__construct($owner, "commands");
        $this->registerCommands();
	}

    public function set($key, $datas = [], $options = []){
        $datas = $this->repairIF($datas);
        if($options["desc"] === "") $options["desc"] = "ifPluginで追加したコマンドです";
        if($options["perm"] === "") $options["perm"] = "ifplugin.customcommand.op";
        $datas["description"] = $options["desc"];
        $datas["permission"] = $options["perm"];
        parent::set($key, $datas);
    }

    public function add($key, $type, $id, $content, $args = []){
        $datas = [];
        if($this->isAdded($key))$datas = $this->get($key);
        $datas[$type][] = [
            "id" => $id,
            "content" => $content
        ];
        $this->register($key, $args["desc"], $args["perm"]);
        $this->set($key, $datas, $args);
    }

    public function remove($key){
        $this->unregister($key);
        parent::remove($key);
    }

    public function registerCommands(){
        foreach($this->getAll() as $command => $value){
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
            if($this->isSubcommand($command))$command = $this->getOriginCommand($command);
            if(!$this->exists($command)){
                $this->register($command, $permission, $value["description"]);
            }
        }
    }

    public function register($command, $permission = "ifplugin.customcommand.op", $description = "ifPluginで追加したコマンドです"){
        if($this->isSubcommand($command))$command = $this->getOriginCommand($command);
        if(!$this->exists($command)){
            $newCommand = new PluginCommand($command, $this->getOwner());
            $newCommand->setDescription($description);
            $newCommand->setPermission($permission);
            $this->getServer()->getCommandMap()->register("ifPlugin", $newCommand);
            $this->command_list[$command] = $newCommand;
            return true;
        }
        return false;
    }

    public function unregister($command){
        $count = count($this->getSubcommands($command));
        if(!$this->isSubcommand($command) and $this->isAdded($command)) $count ++;
        if($count <= 1) {
        $this->getServer()->getCommandMap()->unregister($this->command_list[$command]);
        }
        unset($this->command_list[$command]);
    }

    public function exists($command){
        $exist = $this->getServer()->getPluginCommand($command);
        if($exist === null)return false;
        return true;
    }

    public function isSubcommand($command){
    	$subcommand = false;
    	if(strpos($command, " ") !== false)$subcommand = true;
    	return $subcommand;
    }

    public function getSubcommands($command){
    	$array = [];
    	$command = explode(" ", $command)[0];
    	$commands = $this->getAll();
    	foreach ($commands as $cmd => $value) {
    		$cmds = explode(" ", $cmd);
    		if(array_shift($cmds) == $command){
    			if(isset($cmds[0])){
    				$sub = implode(" ", $cmds);
        			$array[] = $sub;
    			}
    		}
    	}
    	return $array;
    }

    public function getOriginCommand($command){
    	if(!$this->isSubcommand($command))return $command;
    	$commands = explode(" ", $command);
    	return $commands[0];
    }

    public function getReplaceDatas($datas) {
        $result = parent::getReplaceDatas($datas);
        $command = $datas["command"];
        $cmds = explode(" ", substr($command, 1));
        $result[] = new StringVariable("cmd", array_shift($cmds));
        $result[] = new ListVariable("args", $cmds);
        return $result;
    }
}