<?php

namespace aieuo\ip\processes;

use pocketmine\math\Vector3;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class Motion extends TypePosition {

    protected $id = self::MOTION;
    protected $name = "プレイヤーを動かす";
    protected $description = "プレイヤーを§7<x> <y> <z>§fブロック動かす";

    public function getMessage() {
        $pos = $this->getPosition();
        if($pos === false) return false;
        return "プレイヤーを".$pos->x.",".$pos->y.",".$pos->z."ブロック動かす";
    }

    public function execute() {
        $player = $this->getPlayer();
        $pos = $this->getPosition();
        if(!($pos instanceof Vector3)) {
            $player->sendMessage("§c[".$this->getName()."] 正しく入力できていません");
            return;
        }
        $player->setMotion($pos);
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $pos = $this->parse($default);
        $x = $default;
        $y = "";
        $z = "";
        if($pos instanceof Vector3) {
            $x = $pos->x;
            $y = $pos->y;
            $z = $pos->z;
        } elseif($default !== "") {
            $mes .= "§c正しく入力できていません§f";
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("\n§7<x>§f x軸方向に動かす値を入力してください", "例) 1", $x),
                Elements::getInput("\n§7<y>§f y軸方向に動かす値を入力してください", "例) 10", $y),
                Elements::getInput("\n§7<z>§f z軸方向に動かす値を入力してください", "例) 100", $z),
                Elements::getToggle("削除する"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $datas) {
        $status = true;
        if($datas[1] === "" and $datas[2] === "" and $datas[3] === "") {
            $status = null;
        } else {
            $pos = $this->parse($datas[1].",".$datas[2].",".$datas[3]);
            if($pos === false) $status = false;
        }
        return ["status" => $status, "contents" => $datas[1].",".$datas[2].",".$datas[3], "delete" => $datas[4], "cancel" => $datas[5]];
    }
}