<?php

namespace aieuo\ip\processes;

use pocketmine\Player;
use pocketmine\event\Event;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class Process implements ProcessIds {

    /** @var Player */
    private $player;

    /** @var array */
    private $values = [];

    /** @var Event */
    private $event = null;

    /** @var int */
    protected $id;
    /** @var string */
    protected $name;
    /** @var string */
    protected $description;
    /** @var string */
    protected $detail;

    public $replaceDatas = [];

    public function __construct($player = null) {
        $this->player = $player;
    }

    public static function get($id) {
        return ProcessFactory::get($id);
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        if ($this->name[0] === "@") {
            return Language::get(substr($this->name, 1));
        }
        return $this->name;
    }

    public function getDescription() {
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

    public function setEvent(Event $event) {
        $this->event = $event;
    }

    public function getEvent() : ?Event {
        return $this->event;
    }

    public function getEditForm(string $default = "", string $mes = "") {
        if ($mes !== "") $mes = "\n".$mes;
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().$mes),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        return Form::encodeJson($data);
    }

    public function parseFormData(array $data) {
        return ["status" => true, "contents" => "", "delete" => $data[1], "cancel" => $data[2]];
    }

    public function execute() {
    }
}