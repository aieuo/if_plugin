<?php

namespace aieuo\ip\manager;

use aieuo\ip\variable\StringVariable;
use aieuo\ip\variable\NumberVariable;
use aieuo\ip\variable\ListVariable;

class FormIFManager extends IFManager {

    public function __construct($owner) {
        parent::__construct($owner, "forms");
    }

    public function set($key, $datas = [], $options = []) {
        if (!isset($datas["ifs"])) $datas["ifs"] = [];
        parent::set($key, $datas);
    }

    public function get($key, $options = []) {
        if (!$this->isAdded($key)) return [];
        $datas = parent::get($key);
        if (!isset($datas["ifs"][$options["place"]])) return $this->repairIF([]);
        $ifdata = $this->repairIF($datas["ifs"][$options["place"]]);
        return $ifdata;
    }

    public function getIF($key) {
        return parent::get($key);
    }

    public function add($key, $type, $id, $content, $options = []) {
        $datas = $this->getIF($key);
        if (!isset($options["place"])) {
            $data = $this->repairIF([]);
            $data[$type][] = [
                "id" => $id,
                "content" => $content
            ];
            $datas["ifs"][] = $data;
        } else {
            $datas["ifs"][$options["place"]][$type][] = [
                "id" => $id,
                "content" => $content
            ];
            $datas["ifs"][$options["place"]] = $this->repairIF($datas["ifs"][$options["place"]]);
        }
        $this->set($key, $datas);
    }

    public function updateContent($key, $type, $num, $new, $options = []) {
        if (!$this->isAdded($key)) return false;
        $datas = $this->getIF($key);
        $datas["ifs"][$options["place"]][$type][$num]["content"] = $new;
        $this->set($key, $datas);
        return true;
    }

    public function setName($key, $name, $options = []) {
        if (!$this->isAdded($key)) return false;
        $datas = $this->getIF($key);
        $datas["ifs"][$options["place"]]["name"] = $name;
        $this->set($key, $datas);
        return true;
    }

    public function del($key, $type, $num, $options = []) {
        if (!$this->isAdded($key)) return false;
        $datas = $this->getIF($key);
        unset($datas["ifs"][$options["place"]][$type][$num]);
        $datas["ifs"][$options["place"]][$type] = array_merge($datas["ifs"][$options["place"]][$type]);
        $this->set($key, $datas);
        return true;
    }

    public function remove($key, $options = []) {
        if (!$this->isAdded($key)) return false;
        $datas = $this->getIF($key);
        unset($datas["ifs"][$options["place"]]);
        $datas["ifs"] = array_merge($datas["ifs"]);
        $this->set($key, $datas);
        return true;
    }

    public function removeIF($key) {
        parent::remove($key);
    }

    public function getReplaceDatas($datas) {
        $variables = parent::getReplaceDatas($datas);
        $variables["form_name"] = new StringVariable("form_name", $datas["form_name"]);
        $form = $datas["form"];
        switch ($form["type"]) {
            case 'form':
                $variables["form_data"] = new NumberVariable("form_data", $datas["form_data"]);
                break;
            case "modal":
                $variables["form_data"] = new StringVariable("form_data", $datas["form_data"]?"true":"false");
                break;
            case "custom_form":
                $add = [];
                foreach ($datas["form_data"] as $key => $value) {
                    $content = $form["content"][$key];
                    switch ($content["type"]) {
                        case 'label':
                        case 'input':
                        case "slider":
                        case "dropdown":
                            $add[] = $value;
                            break;
                        case "toggle":
                            $add[] = $value ? "true" : "false";
                            break;
                    }
                }
                $variables["form_data"] = new ListVariable("form_data", $add);
                break;
        }
        return $variables;
    }
}