<?php

namespace aieuo\ip\variable;

class VariableHelper {

	const STRING_VARIABLE = 0;
	const INTEGER_VARIABLE = 1;
	const ARRAY_VARIABLE = 2;

	private $variables = [];

	public function __construct($owner){
		$this->owner = $owner;
	}

	public function loadDataBase() {
		$owner = $this->owner;
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
		if(isset($this->variables[$name]) and !$save) return $this->variables[$name];
		if(!$this->exists($name, true)) return "";
        $datas = $this->db->query("SELECT * FROM variables WHERE name=\"$name\"")->fetchArray();
        return Variable::create($datas["name"], $datas["value"], $datas["type"]);
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
		if(!($val instanceof StringVariable)) $val = $val->toStringVariable();
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

	/**
	 * 変数を置き換える
	 * @param  string $string
	 * @return string
	 */
    public function replaceVariable($string){
        $count = 0;
        while(preg_match_all("/({[^{}]+})/", $string, $matches)){
            if(++$count >= 10) break;
            foreach ($matches[0] as $name) {
                $val = $this->get(substr($name, 1, -1));
                if(!($val instanceof Variable)) {
                	$string = str_replace($name, "§cUndefined variable: ".substr($name, 1, -1)."§r", $string);
                	continue;
                }
                if($val instanceof ListVariable) {
                	$haystack = explode($name, $string)[1];
                	if(preg_match("/^\[([0-9]+)\].*/", $haystack, $index)) {
                		$value = $val->getValueFromIndex($index[1]);
                		if($value === null) $value = "§cUndefined index: ".substr($name, 1, -1)."[".$index[1]."]§r";
                		$string = str_replace($name."[".$index[1]."]", $value, $string);
                		continue;
                	}
                	if(preg_match("/^\.([a-z]+[0-9a-z]*).*/", $haystack, $method)) {
                		if($method[1] === "length") {
                			$string = str_replace($name.".".$method[1], $val->getCount(), $string);
                		}
                		continue;
                	}
                	$val = $val->toStringVariable();
                }
                $string = str_replace($name, $val->getValue(), $string);
            }
        }
        return $string;
    }

	/**
	 * 文字列が変数か調べる
	 * @param  string  $variable
	 * @return boolean
	 */
	public function isVariable(string $variable) {
		return preg_match("/^{.+}$/", $variable);
	}

	/**
	 * 文字列に変数が含まれているか調べる
	 * @param  string  $variable
	 * @return boolean
	 */
	public function containsVariable(string $variable) {
		return preg_match("/.*{.+}.*/", $variable);
	}

	/**
	 * 文字列の型を調べる
	 * @param  string $string
	 * @return int
	 */
	public function getType(string $string) {
		if(substr($string, 0, 5) === "(str)") {
			$type = Variable::STRING;
		} elseif(substr($string, 0, 5) === "(num)") {
			$type = Variable::NUMBER;
		} elseif(substr($string, 0, 6) === "(list)") {
			$type = Variable::LIST;
		} elseif(is_numeric($string)) {
			$type = Variable::NUMBER;
		} else {
			$type = Variable::STRING;
		}
		return $type;
	}

	/**
	 * 文字列の型を変更する
	 * @param  string $string
	 * @return string | float | value
	 */
	public function changeType(string $value) {
		if(mb_substr($value, 0, 5) === "(str)") {
			$value = mb_substr($value, 5);
		} elseif(mb_substr($value, 0, 5) === "(num)") {
			$value = mb_substr($value, 5);
			if(!$this->containsVariable($value)) $value = (float)$value;
		} elseif(substr($value, 0, 6) === "(list)") {
			$value = mb_substr($value, 6);
			if(!$this->containsVariable($value)) $value = Variable::create("list", $value, Variable::LIST)->getValue();
		} elseif(is_numeric($value)) {
			$value = (float)$value;
		}
		return $value;
	}
}