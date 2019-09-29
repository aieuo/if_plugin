<?php

namespace aieuo\ip\conditions;

use pocketmine\Player;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

use aieuo\ip\utils\Language;

class Condition implements ConditionIds {

    const MATCHED = 0;
    const NOT_MATCHED = 1;
    const NOT_FOUND = 2;
    const ERROR = -1;

    /** @var Player */
    private $player;

    /** @var array */
    private $values = [];

    /** @var int */
    protected $id;
    /** @var string */
    protected $name;
    /** @var string */
    protected $description;
    /** @var string */
    protected $detail;

    public function __construct($player = null) {
        $this->player = $player;
    }

    public static function get($id) {
        return ConditionFactory::get($id);
    }

    public function getId() {
        return $this->id;
    }

    public function getName(): string {
        if ($this->name[0] === "@") {
            return Language::get(substr($this->name, 1));
        }
        return $this->name;
    }

    public function getDescription(): string {
        if ($this->description[0] === "@") {
            return Language::get(substr($this->description, 1));
        }
        return $this->description;
    }

    // TODO 失敗したときfalse返すかnull返すか統一する
    public function getDetail(): string {
        if ($this->detail[0] === "@") {
            return Language::get(substr($this->detail, 1));
        }
        return $this->detail;
    }

    public function parse(string $str) {
        return $str;
    }

    public function setPlayer(Player $player) : self {
        $this->player = $player;
        return $this;
    }

    // TODO オンラインのチェック
    public function getPlayer() : Player {
        return $this->player;
    }

    public function setValues($values) : self {
        $this->values = $values;
        return $this;
    }

    public function getValues() {
        return $this->values;
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $datas) {
        return ["status" => true, "contents" => "", "delete" => $datas[1], "cancel" => $datas[2]];
    }

    public function check() {
        return Ifs::NOT_FOUND;
    }
}