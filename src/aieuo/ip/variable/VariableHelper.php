<?php

namespace aieuo\ip\variable;

class VariableHelper {

	const STRING_VARIABLE = 0;
	const INTEGER_VARIABLE = 1;
	const ARRAY_VARIABLE = 2;

	private $variables = [];

	public function __construct($owner){
        if(!file_exists($owner->getDataFolder()."if.db")) {
            $this->db = new \SQLite3($owner->getDataFolder()."if.db", SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
        }else{
            $this->db = new \SQLite3($owner->getDataFolder()."if.db", SQLITE3_OPEN_READWRITE);
        }
        $this->db->query("CREATE TABLE IF NOT EXISTS variables (name TEXT, value TEXT, type INT)");
	}

	/**
	 * @param  string $name
	 * @param  bool $save
	 * @return bool
	 */
	public function exists(String $name, $save = false){
		if(isset($this->variables[$name]) and !$save)return true;
        $datas = $this->db->query("SELECT * FROM variables WHERE name=\"$name\"")->fetchArray();
        return !empty($datas);
	}

	/**
	 * @param  string $name
	 * @param  bool $save
	 * @return string | Variable
	 */
	public function get(String $name, $save = false){
		if(isset($this->variables[$name]) and !$save)return "";
		if(!$this->exists($name, true))return "";
        $datas = $this->db->query("SELECT * FROM variables WHERE name=\"$name\"")->fetchArray();
        return new Variable($datas["name"], $datas["value"], $datas["type"]);
	}

	/**
	 * @param Variable $val
	 * @param bool $save
	 */
	public function add(Variable $val, $save = false){
		if(!$save){
			$this->variables[$val->getName()] = $val;
			return;
		}
		$name = $val->getName();
		$value = $val->getValue();
		$type = $val->getType();
		if($this->exists($name, true)){
	        $this->db->query("UPDATE variables set value=\"$value\" WHERE name=\"$name\"");
		}else{
        	$this->db->query("INSERT OR REPLACE INTO variables VALUES(\"$name\",\"$value\",$type)");
		}
	}

	/**
	 * @param  String $name
	 * @return bool
	 */
	public function del(String $name){
		if(isset($this->variables[$name])){
			unset($this->variables[$name]);
		}
		if(!$this->exists($name))return false;
        $this->db->query("DELETE FROM variables WHERE name=\"$name\"");
        return true;
	}

	public function save(){
		unset($this->variables["result"]);
		foreach ($this->variables as $variable) {
			$this->add($variable, true);
		}
		$this->variables = [];
	}
}