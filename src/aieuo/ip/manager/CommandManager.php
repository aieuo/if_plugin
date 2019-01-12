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

    public function set($key, $datas = [], $args = []){
        $datas = $this->repairIF($datas);
        if($args["desc"] === "") $args["desc"] = "ifPluginで追加したコマンドです";
        if($args["perm"] === "") $args["perm"] = "op";
        $datas["description"] = $args["desc"];
        $datas["permission"] = $args["perm"];
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
            if($this->isSubcommand($command))$command = $this->getParentCommand($command);
            if(!$this->exists($command)){
                $this->register($command, $value["permission"], $value["description"]);
            }
        }
    }

    public function register($command, $permission = "default", $description = "ifPluginで追加したコマンドです"){
        if($this->isSubcommand($command))$command = $this->getParentCommand($command);
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
        $commands = $this->getSubcommand($command);
        if($this->isSubcommand($command)){
            $subs = array_shift(explode(" ", $command));
            unset($commands[implode(" ", $subs)]);
            $command = $this->getParentCommand($command);
        }
        $count = count($commands);
        if(!$this->isSubcommand($command) and $this->isAdded($command)) $count ++;
        if($count >= 1)return false;
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
    				$sub = implode(" ", $cmds);
        			$array[] = $sub;
    			}
    		}
    	}
    	return $array;
    }

    public function getParentCommand($command){
    	if(!$this->isSubcommand($command))return $command;
    	$commands = explode(" ", $command);
    	return $commands[0];
    }

    public function replaceDatas($string, $datas) {
        $string = parent::replaceDatas($string, $datas);
        $command = $datas["command"];
        $cmds = explode(" ", substr($command, 1));
        $string = str_replace("{cmd}", array_shift($cmds), $string);
        foreach ($cmds as $n => $cmd) {
            $string = str_replace("{args_".$n."}", $cmd, $string);
        }
        return $string;
    }
}