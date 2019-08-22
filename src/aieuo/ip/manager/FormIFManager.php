<?php

namespace aieuo\ip\manager;

use aieuo\ip\variable\StringVariable;
use aieuo\ip\variable\NumberVariable;
use aieuo\ip\variable\ListVariable;
use aieuo\ip\IFPlugin;
use aieuo\ip\form\Elements;

class FormIFManager extends IFManager {

    public function __construct($owner) {
        parent::__construct($owner, "forms");
    }

    public function set(string $key, array $datas = [], array $options = []) {
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
        $datas = parent::get($key);
        $datas["form"] = str_replace("\\\\n", "\\n", $datas["form"]);
        return $datas;
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
                $variables["form_button"] = new StringVariable("form_button", $form["buttons"][$datas["form_data"]]["text"]);
                break;
            case "modal":
                $variables["form_data"] = new StringVariable("form_data", $datas["form_data"]?"true":"false");
                break;
            case "custom_form":
                $add = [];
                $dropdowns = [];
                foreach ($datas["form_data"] as $key => $value) {
                    $content = $form["content"][$key];
                    switch ($content["type"]) {
                        case 'label':
                        case 'input':
                        case "slider":
                            $add[] = $value;
                            break;
                        case "dropdown":
                            $add[] = $value;
                            $dropdowns[] = $content["options"][$value];
                            break;
                        case "toggle":
                            $add[] = $value ? "true" : "false";
                            break;
                    }
                }
                $variables["form_data"] = new ListVariable("form_data", $add);
                $variables["form_dropdown"] = new ListVariable("form_dropdown", $dropdowns);
                break;
        }
        return $variables;
    }

    public function getForm($name, $replaces) {
        $form = json_decode($this->getIF($name)["form"], true);
        $variableHelper = IFPlugin::getInstance()->getVariableHelper();
        $form["title"] = $variableHelper->replaceVariables($form["title"], $replaces);
        switch ($form["type"]) {
            case "modal":
                $form["content"] = $variableHelper->replaceVariables($form["content"], $replaces);
                $form["button1"] = $variableHelper->replaceVariables($form["button1"], $replaces);
                $form["button2"] = $variableHelper->replaceVariables($form["button2"], $replaces);
                break;
            case "form":
                $form["content"] = $variableHelper->replaceVariables($form["content"], $replaces);
                $buttons = [];
                foreach ($form["buttons"] as $button) {
                    $variables = $variableHelper->findVariables($button["text"], $replaces);
                    foreach ($variables as $variableName => $variable) {
                        if ($variable instanceof ListVariable) {
                            $add = array_map(function ($value) use ($button, $variableName, $variableHelper, $replaces) {
                                $text = str_replace("{".$variableName."}", $value, $button["text"]);
                                $text = $variableHelper->replaceVariables($text, $replaces);
                                return Elements::getButton($text);
                            }, $variable->getValue());
                            $buttons = array_merge($buttons, $add);
                            continue 2;
                        }
                    }
                    $buttons[] = Elements::getButton($variableHelper->replaceVariables($button["text"], $replaces));
                }
                $form["buttons"] = $buttons;
                break;
            case "custom_form":
                $contents = [];
                foreach ($form["content"] as $content) {
                    $content["text"] = $variableHelper->replaceVariables($content["text"], $replaces);
                    switch ($content["type"]) {
                        case "input":
                            $content["default"] = $variableHelper->replaceVariables($content["default"], $replaces);
                            $content["placeholder"] = $variableHelper->replaceVariables($content["placeholder"], $replaces);
                            break;
                        case "dropdown":
                            $options = [];
                            foreach ($content["options"] as $option) {
                                $variables = $variableHelper->findVariables($option, $replaces);
                                foreach ($variables as $variableName => $variable) {
                                    if ($variable instanceof ListVariable) {
                                        $add = array_map(function ($value) use ($option, $variableName, $variableHelper, $replaces) {
                                            $text = str_replace("{".$variableName."}", $value, $option);
                                            return $variableHelper->replaceVariables($text, $replaces);
                                        }, $variable->getValue());
                                        $options = array_merge($options, $add);
                                        continue 2;
                                    }
                                }
                                $options[] = $variableHelper->replaceVariables($option, $replaces);
                            }
                            $content["options"] = $options;
                            break;
                    }
                    $contents[] = $content;
                }
                $form["content"] = $contents;
                break;
        }
        return $form;
    }
}