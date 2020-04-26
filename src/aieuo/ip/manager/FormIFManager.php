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

    public function set(string $key, array $data = [], array $options = []) {
        if (!isset($data["ifs"])) $data["ifs"] = [];
        parent::set($key, $data);
    }

    public function get(string $key, array $options = []): ?array {
        if (!$this->exists($key)) return [];
        $data = parent::get($key);
        if (!isset($data["ifs"][$options["place"]])) return $this->repairIF([]);
        return $this->repairIF($data["ifs"][$options["place"]]);
    }

    public function getIF($key) {
        $data = parent::get($key);
        $data["form"] = str_replace("\\\\n", "\\n", $data["form"]);
        return $data;
    }

    public function add($key, $type, $id, $content, $options = []) {
        $data = $this->getIF($key);
        if (!isset($options["place"])) {
            $ifData = $this->repairIF([]);
            $ifData[$type][] = [
                "id" => $id,
                "content" => $content
            ];
            $data["ifs"][] = $ifData;
        } else {
            $data["ifs"][$options["place"]][$type][] = [
                "id" => $id,
                "content" => $content
            ];
            $data["ifs"][$options["place"]] = $this->repairIF($data["ifs"][$options["place"]]);
        }
        $this->set($key, $data);
    }

    public function updateContent($key, $type, $num, $new, $options = []) {
        if (!$this->exists($key)) return false;
        $data = $this->getIF($key);
        $data["ifs"][$options["place"]][$type][$num]["content"] = $new;
        $this->set($key, $data);
        return true;
    }

    public function setName($key, $name, $options = []) {
        if (!$this->exists($key)) return false;
        $data = $this->getIF($key);
        $data["ifs"][$options["place"]] = $this->repairIF($data["ifs"][$options["place"]]);
        $data["ifs"][$options["place"]]["name"] = $name;
        $this->set($key, $data);
        return true;
    }

    public function del($key, $type, $num, $options = []) {
        if (!$this->exists($key)) return false;
        $data = $this->getIF($key);
        unset($data["ifs"][$options["place"]][$type][$num]);
        $data["ifs"][$options["place"]][$type] = array_merge($data["ifs"][$options["place"]][$type]);
        $this->set($key, $data);
        return true;
    }

    public function remove(string $key, array $options = []) {
        if (!$this->exists($key)) return false;
        $data = $this->getIF($key);
        unset($data["ifs"][$options["place"]]);
        $data["ifs"] = array_merge($data["ifs"]);
        $this->set($key, $data);
        return true;
    }

    public function removeIF($key) {
        parent::remove($key);
    }

    public function getReplaceData($data) {
        $variables = parent::getReplaceData($data);
        $variables["form_name"] = new StringVariable("form_name", $data["form_name"]);
        $form = $data["form"];
        switch ($form["type"]) {
            case 'form':
                $variables["form_data"] = new NumberVariable("form_data", $data["form_data"]);
                $variables["form_button"] = new StringVariable("form_button", $form["buttons"][$data["form_data"]]["text"]);
                break;
            case "modal":
                $variables["form_data"] = new StringVariable("form_data", $data["form_data"]?"true":"false");
                break;
            case "custom_form":
                $add = [];
                $dropdowns = [];
                foreach ($data["form_data"] as $key => $value) {
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