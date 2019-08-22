<?php

namespace aieuo\ip\processes;

use pocketmine\math\Vector3;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class Motion extends TypePosition {

    protected $id = self::MOTION;
    protected $name = "@process.motion.name";
    protected $description = "@process.motion.description";

    public function getDetail(): string {
        $pos = $this->getPosition();
        if ($pos === false) return false;
        return Language::get("process.motion.detail", [$pos->x, $pos->y, $pos->z]);
    }

    public function execute() {
        $player = $this->getPlayer();
        $pos = $this->getPosition();
        if (!($pos instanceof Vector3)) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return;
        }
        $player->setMotion($pos);
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $pos = $this->parse($default);
        $x = $default;
        $y = "";
        $z = "";
        if ($pos instanceof Vector3) {
            $x = $pos->x;
            $y = $pos->y;
            $z = $pos->z;
        } elseif ($default !== "") {
            $mes .= Language::get("form.error");
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.motion.form.x"), Language::get("input.example", ["1"]), $x),
                Elements::getInput(Language::get("process.motion.form.y"), Language::get("input.example", ["10"]), $y),
                Elements::getInput(Language::get("process.motion.form.z"), Language::get("input.example", ["100"]), $z),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $datas) {
        $status = true;
        if ($datas[1] === "" and $datas[2] === "" and $datas[3] === "") {
            $status = null;
        } else {
            $pos = $this->parse($datas[1].",".$datas[2].",".$datas[3]);
            if ($pos === false) $status = false;
        }
        return ["status" => $status, "contents" => $datas[1].",".$datas[2].",".$datas[3], "delete" => $datas[4], "cancel" => $datas[5]];
    }
}