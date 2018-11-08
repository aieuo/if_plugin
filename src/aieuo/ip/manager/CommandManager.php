<?php

namespace aieuo\ip\manager;

use pocketmine\command\Command;
use pocketmine\command\PluginCommand;

class CommandManager extends ifManager{

    private $command_list = [];

	public function __construct($owner){
        parent::__construct($owner, "commands");
        $this->registerCommands();
	}

    public function registerCommands(){
        foreach($this->getAll() as $command => $value){
    		if($this->isSubcommand($command))$command = $this->getOriginalCommand($command);
            if(!$this->exists($command)){
                $this->register($command, $value["permission"], $value["description"]);
            }
        }
    }

    public function register($command, $permission = "default", $description = "ifPluginで追加したコマンドです"){
    	if($this->isSubcommand($command))$command = $this->getOriginalCommand($command);
        if(!$this->exists($command)){
            $newCommand = new PluginCommand($command, $this->getOwner());
            $newCommand->setDescription($description);
            $newCommand->setPermission($permission);
            $this->getServer()->getCommandMap()->register("ifPlugin", $newCommand);
            var_dump($command);
            $this->command_list[$command] = $newCommand;
            return true;
        }
        return false;
    }

    public function unregister($command){
        if($this->isSubcommand($command))$command = $this->getOriginalCommand($command);
        if($this->exists($command) or !isset($this->command_list[$command]))return false;
        $this->getServer()->getCommandMap()->unregister($this->command_list[$command]);
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

    public function getSubcommand($command){
    	$array = [];
    	$command = explode(" ", $command)[0];
    	$commands = $this->getAll();
    	foreach ($commands as $cmd => $value) {
    		$cmds = explode(" ", $cmd);
    		if(array_shift($cmds) == $command){
    			if(isset($cmds[0])){
    				$sub = implode(" " ,$cmds);
        			$array[] = $sub;
    			}
    		}
    	}
    	return $array;
    }

    public function getOriginalCommand($command){
    	if(!$this->isSubcommand($command))return $command;
    	$commands = explode(" ", $command);
    	return $commands[0];
    }

    public function setOptions($command, $description, $permission){
        $this->tmp[$command] = [$description, $permission];
    }

    public function set($key, $data = []){
        if(isset($this->tmp[$key])){
            $data["description"] = $this->tmp[$key][0];
            $data["permission"] = $this->tmp[$key][1];
            unset($this->tmp[$key]);
        }
        if(!isset($datas["if"]))$datas["if"] = [];
        if(!isset($datas["match"]))$datas["match"] = [];
        if(!isset($datas["else"]))$datas["else"] = [];
        parent::set($key, $data);
    }

    public function add($key, $type, $id, $content){
        $datas = [];
        if($this->isAdded($key))$datas = $this->get($key);
        if(isset($this->tmp[$key])){
            $datas["description"] = $this->tmp[$key][0];
            $datas["permission"] = $this->tmp[$key][1];
            unset($this->tmp[$key]);
        }
        if(!isset($datas["if"]))$datas["if"] = [];
        if(!isset($datas["match"]))$datas["match"] = [];
        if(!isset($datas["else"]))$datas["else"] = [];
        if(!isset($datas["description"]))$datas["description"] = "ifPluginで追加したコマンドです";
        if(!isset($datas["permission"]))$datas["permission"] = "op";
        $datas[$type][] = [
            "id" => $id,
            "content" => $content
        ];
        $this->register($key, $datas["description"], $datas["permission"]);
        parent::set($key, $datas);
    }

    public function remove($key){
        $this->unregister($key);
        parent::remove($key);
    }
}