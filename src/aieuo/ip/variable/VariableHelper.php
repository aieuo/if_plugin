<?php

namespace aieuo\ip\variable;

class VariableHelper {

    const STRING_VARIABLE = 0;
    const INTEGER_VARIABLE = 1;
    const ARRAY_VARIABLE = 2;

    private $variables = [];

    /** @var \SQLite3 */
    private $db;

    public function __construct($owner) {
        $this->owner = $owner;
    }

    public function loadDataBase() {
        $owner = $this->owner;
        if (!file_exists($owner->getDataFolder()."if.db")) {
            $this->db = new \SQLite3($owner->getDataFolder()."if.db", SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
        } else {
            $this->db = new \SQLite3($owner->getDataFolder()."if.db", SQLITE3_OPEN_READWRITE);
        }
        $this->db->query("CREATE TABLE IF NOT EXISTS variables (name TEXT, value TEXT, type INT)");
    }

    /**
     * @param  string $name
     * @param  bool $save
     * @return bool
     */
    public function exists(String $name, $save = false) {
        if (isset($this->variables[$name]) and !$save)return true;
        $data = $this->db->query("SELECT * FROM variables WHERE name=\"$name\"")->fetchArray();
        return !empty($data);
    }

    /**
     * @param  string $name
     * @param  bool $save
     * @return string | Variable
     */
    public function get(String $name, $save = false) {
        if (isset($this->variables[$name]) and !$save) return $this->variables[$name];
        if (!$this->exists($name, true)) return "";
        $data = $this->db->query("SELECT * FROM variables WHERE name=\"$name\"")->fetchArray();
        return Variable::create($data["name"], $this->currentType($data["value"]), $data["type"]);
    }

    /**
     * @param Variable $val
     * @param bool $save
     */
    public function add(Variable $val, $save = false) {
        if (!$save) {
            $this->variables[$val->getName()] = $val;
            return;
        }
        $name = $val->getName();
        $type = $val->getType();
        if (!($val instanceof StringVariable)) $val = $val->toStringVariable();
        $value = $val->getValue();
        if ($this->exists($name, true)) {
            $stmt = $this->db->prepare("UPDATE variables set value=:value, type=:type WHERE name=:name");
        } else {
            $stmt = $this->db->prepare("INSERT OR REPLACE INTO variables VALUES(:name,:value,:type)");
        }
        $stmt->bindValue(":name", $name, SQLITE3_TEXT);
        $stmt->bindValue(":value", $value, SQLITE3_TEXT);
        $stmt->bindValue(":type", $type, SQLITE3_INTEGER);
        $stmt->execute();
    }

    /**
     * @param  String $name
     * @return bool
     */
    public function del(String $name) {
        if (isset($this->variables[$name])) {
            unset($this->variables[$name]);
        }
        if (!$this->exists($name))return false;
        $this->db->query("DELETE FROM variables WHERE name=\"$name\"");
        return true;
    }

    public function save() {
        unset($this->variables["result"]);
        foreach ($this->variables as $variable) {
            $this->add($variable, true);
        }
        $this->variables = [];
    }

    public function findVariables($string, $variables = []) {
        foreach (["/\[({[^{}]+})\]/" => 2, "/({[^{}]+})/" => 1] as $pattern => $n) {
            $result = [];
            if (preg_match_all($pattern, $string, $matches)) {
                foreach ($matches[0] as $name) {
                    $name = mb_substr($name, $n, -$n);
                    $val = isset($variables[$name]) ? $variables[$name] : $this->get($name);
                    if (!($val instanceof Variable)) continue;
                    $result[$name] = $val;
                }
            }
        }
        return $result;
    }

    /**
     * 文字列の中にある変数を置き換える
     * @param  string $string
     * @param  array $variables
     * @return string
     */
    public function replaceVariables($string, $variables = []) {
        foreach (["/\[({[^{}]+})\]/" => 2, "/({[^{}]+})/" => 1] as $pattern => $n) {
            $limit = 10;
            while (preg_match_all($pattern, $string, $matches)) {
                foreach ($matches[0] as $name) {
                    $name = mb_substr($name, $n, -$n);
                    $val = isset($variables[$name]) ? $variables[$name] : $this->get($name);
                    if (!($val instanceof Variable)) {
                        $string = str_replace("{".$name."}", "§cUndefined variable: ".$name."§r", $string);
                        continue;
                    }
                    $string = $this->replace($string, $val);
                }
                if ($limit -- < 0) break;
            }
        }
        return $string;
    }

    /**
     * 変数を置き換える
     * @param  string $string
     * @param  Variable $variable
     * @return string
     */
    public function replace($string, $variable) {
        if (strpos($string, "{".$variable->getName()."}") === false) return $string;
        if ($variable instanceof ListVariable) {
            $haystack = explode("{".$variable->getName()."}", $string)[1];
            if (preg_match("/^\[([0-9]+)\].*/", $haystack, $index)) {
                $value = $variable->getValueFromIndex($index[1]);
                if ($value === null) $value = "§cUndefined index: ".$variable->getName()."[".$index[1]."]§r";
                $string = str_replace("{".$variable->getName()."}"."[".$index[1]."]", $value, $string);
                return $string;
            }
            if (preg_match("/^\.([a-z]+[0-9a-z]*).*/", $haystack, $method)) {
                if ($method[1] === "length") {
                    $string = str_replace("{".$variable->getName()."}".".".$method[1], $variable->getCount(), $string);
                }
                return $string;
            }
            $variable = new StringVariable($variable->getName(), $variable->getString());
        }
        $string = str_replace("{".$variable->getName()."}", $variable->getValue(), $string);
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
        if (substr($string, 0, 5) === "(str)") {
            $type = Variable::STRING;
        } elseif (substr($string, 0, 5) === "(num)") {
            $type = Variable::NUMBER;
        } elseif (substr($string, 0, 6) === "(list)") {
            $type = Variable::LIST;
        } elseif (is_numeric($string)) {
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
    public function currentType(string $value) {
        if (mb_substr($value, 0, 5) === "(str)") {
            $value = mb_substr($value, 5);
        } elseif (mb_substr($value, 0, 5) === "(num)") {
            $value = mb_substr($value, 5);
            if (!$this->containsVariable($value)) $value = (float)$value;
        } elseif (substr($value, 0, 6) === "(list)") {
            $value = mb_substr($value, 6);
            if (!$this->containsVariable($value)) $value = Variable::create("list", $value, Variable::LIST)->getValue();
        } elseif (is_numeric($value)) {
            $value = (float)$value;
        }
        return $value;
    }
}