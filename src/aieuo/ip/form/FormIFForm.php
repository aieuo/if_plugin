<?php

namespace aieuo\ip\form;

use aieuo\ip\ifPlugin;
use aieuo\ip\Session;
use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Messages;
use aieuo\ip\conditions\Condition;
use aieuo\ip\conditions\Comparison;
use aieuo\ip\utils\Language;

class FormIFForm {
    public function getSelectActionForm(){
        $data = [
            "type" => "form",
            "title" => Language::get("form.formif.action.title"),
            "content" => Language::get("form.selectButton"),
            "buttons" => [
                Elements::getButton(Language::get("form.action.add")),
                Elements::getButton(Language::get("form.action.edit")),
                Elements::getButton(Language::get("form.action.delete")),
                Elements::getButton(Language::get("form.cancel")),
                Elements::getButton(Language::get("form.back"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function onSelectAction($player, $data) {
        if ($data === null) return;
        $session = Session::get($player);
        $session->setValid()->setIfType(Session::FORM);
        switch ($data) {
            case 0:
                $session->setData("action", "add");
                Form::sendForm($player, $this->getAddIFformForm(), $this, "onAddIFformForm");
                return;
            case 1:
                $session->setData("action", "edit");
                break;
            case 2:
                $session->setData("action", "del");
                break;
            case 3:
                $session->setValid(false);
                $player->sendMessage(Language::get("form.cancelled"));
                return;
            case 4:
                $session->setValid(false);
                $form = (new Form())->getSelectIfTypeForm();
                Form::sendForm($player, $form, new Form(), "onSelectIfType");
                return;
        }
        Form::sendForm($player, $this->getSelectIFformForm(), $this, "onSelectIFformForm");
    }

    public function getAddIFformForm($mes = "") {
        $data = [
            "type" => "custom_form",
            "title" => Language::get("form.formif.addformif.title"),
            "content" => [
                Elements::getInput(Language::get("form.formif.addformif.content0", [$mes !== "" ? $mes."\n" : ""])),
                Elements::getInput(Language::get("form.formif.addformif.content1")),
                Elements::getDropdown(Language::get("form.formif.addformif.content2"), [
                    Language::get("form.formif.list"),
                    Language::get("form.formif.custom"),
                    Language::get("form.formif.modal"),
                ], 0),
                Elements::getToggle(Language::get("form.cancel")),
            ],
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function onAddIFformForm($player, $data) {
        $session = Session::get($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
        $manager = ifPlugin::getInstance()->getFormIFManager();
        if ($data[3]) {
            $form = $this->getSelectActionForm();
            Form::sendForm($player, $form, $this, "onSelectAction");
            return;
        }
        if ($data[0] === "") {
            $form = $this->getAddIFformForm(Language::get("form.insufficient"));
            Form::sendForm($player, $form, $this, "onAddIFformForm");
            $player->sendMessage(Language::get("form.insufficient"));
            return;
        }
        if ($manager->isAdded($data[0])) {
            $form = $this->getAddIFformForm(Language::get("form.formif.exist"));
            Form::sendForm($player, $form, $this, "onAddIFformForm");
            $player->sendMessage(Language::get("form.formif.exist"));
            return;
        }
        $form = ["title" => $data[1] == "" ? $data[0] : $data[1]];
        switch ($data[2]) {
            case 0:
                $form["type"] = "form";
                $form["content"] = "content";
                $form["buttons"] = [];
                break;
            case 1:
                $form["type"] = "custom_form";
                $form["content"] = [];
                break;
            case 2:
                $form["type"] = "modal";
                $form["content"] = "content";
                $form["button1"] = "button1";
                $form["button2"] = "button2";
                break;
        }
        $json = Form::encodeJson($form);
        $session->setData("if_key", $data[0])->setData("form", $form);
        $datas = $manager->repairIF([]);
        $datas["form"] = $json;
        $manager->set($data[0], $datas);
        Form::sendForm($player, $this->getEditIFformForm($form), $this, "onEditIFformForm");
    }

    public function getSelectIFformForm($mes = "") {
        $data = [
            "type" => "custom_form",
            "title" => Language::get("form.formif.selectformif.title"),
            "content" => [
                Elements::getInput(Language::get("form.formif.selectformif.content0", [$mes !== "" ? $mes."\n" : ""])),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function onSelectIFformForm($player, $data) {
        $session = Session::get($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
        if ($data[1]) {
            $form = $this->getSelectActionForm();
            Form::sendForm($player, $form, $this, "onSelectAction");
            return;
        }
        if ($data[0] === "") {
            $form = $this->getSelectIFformForm(Language::get("form.insufficient"));
            Form::sendForm($player, $form, $this, "onSelectIFformForm");
            $player->sendMessage(Language::get("form.insufficient"));
            return;
        }
        $manager = ifPlugin::getInstance()->getFormIFManager();
        if (!$manager->isAdded($data[0])) {
            $form = $this->getSelectIFformForm(Language::get("form.formif.notexist"));
            Form::sendForm($player, $form, $this, "onSelectIFformForm");
            $player->sendMessage(Language::get("form.formif.notexist"));
            return;
        }

        $session->setData("if_key", $data[0]);
        $action = $session->getData("action");
        if ($action == "edit") {
            $form = $manager->getIF($data[0])["form"];
            $session->setData("form", json_decode($form, true));
            Form::sendForm($player, $this->getEditIFformForm(json_decode($form, true)), $this, "onEditIFformForm");
        } elseif ($action == "del") {
            $form = (new Form())->getConfirmDeleteForm();
            Form::sendForm($player, $form, $this, "onDeleteIf");
        }
    }

    public function getEditIFformForm($form, $mes = "") {
        $type = $form["type"];
        $buttons = [
            ["text" => Language::get("form.formif.editformif.button.preview")],
            ["text" => Language::get("form.formif.editformif.button.edit")],
            ["text" => Language::get("form.formif.editformif.button.title", [$form["title"]])],
        ];
        switch ($type) {
            case 'modal':
                $buttons[] = ["text" => Language::get("form.formif.editformif.modal.content", [$form["content"]])];
                $buttons[] = ["text" => Language::get("form.formif.editformif.modal.button1", [$form["button1"]])];
                $buttons[] = ["text" => Language::get("form.formif.editformif.modal.button2", [$form["button2"]])];
                break;
            case "form":
                $buttons[] = ["text" => Language::get("form.formif.editformif.form.content", [$form["content"]])];
                foreach ($form["buttons"] as $button) {
                    $buttons[] = ["text" => Language::get("form.formif.editformif.form.button", [$button["text"]])];
                }
                $buttons[] = ["text" => Language::get("form.formif.editformif.form.addbutton")];
                break;
            case "custom_form":
                foreach ($form["content"] as $content) {
                    $buttons[] = ["text" => Language::get("form.formif.editformif.custom.content", [$content["type"], $content["text"]])];
                }
                $buttons[] = ["text" => Language::get("form.formif.editformif.custom.addparts")];
                break;
        }
        $data = [
            "type" => "form",
            "title" => Language::get("form.formif.editformif.title"),
            "content" => Language::get("form.formif.editformif.content", [$mes !== "" ? $mes."\n" : ""]),
            "buttons" => $buttons
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function onEditIFformForm($player, $data) {
        $session = Session::get($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
        $form = $session->getData("form");
        $manager = ifPlugin::getInstance()->getFormIFManager();
        if ($data === 0) {
            Form::sendForm($player, Form::encodeJson($form), $this, "onPreviewIFform");
            return;
        }
        if ($data === 1) {
            Form::sendForm($player, $this->getIfListForm($session->getData("if_key")), $this, "onSelectIf");
            return;
        }
        $session->setData("form_select_place", $data-2);
        Form::sendForm($player, $this->getSelectPartsForm($form, $data-2), $this, "onSelectParts");
    }

    public function onPreviewIFform($player, $data) {
        $session = Session::get($player);
        $form = $session->getData("form");
        Form::sendForm($player, $this->getEditIFformForm($form), $this, "onEditIFformForm");
    }

    public function getSelectPartsForm($form, $place, $mes = "") {
        $data = [
            "type" => "custom_form",
            "title" => Language::get("form.formif.selectparts.title"),
            "content" => [Elements::getToggle(($mes !== "" ? $mes."\n" : "").Language::get("form.cancel"))],
        ];
        switch ($form["type"]) {
            case 'modal':
                switch ($place) {
                    case 0:
                        $data["content"][] = Elements::getLabel(Language::get("form.formif.selectparts.parts.title"));
                        $data["content"][] = Elements::getInput(Language::get("form.formif.selectparts.parts.text"), "", $form["title"]);
                        break;
                    case 1:
                        $data["content"][] = Elements::getLabel(Language::get("form.formif.selectparts.parts.content"));
                        $data["content"][] = Elements::getInput(Language::get("form.formif.selectparts.parts.text"), "", $form["content"]);
                        break;
                    case 2:
                        $data["content"][] = Elements::getLabel(Language::get("form.formif.selectparts.parts.button1")."\n".Language::get("form.formif.recive", ["{form_data} <- true"]));
                        $data["content"][] = Elements::getInput(Language::get("form.formif.selectparts.parts.text"), "", $form["button1"]);
                        $data["content"][] = Elements::getToggle(Language::get("form.formif.selectparts.parts.editif"));
                        break;
                    case 3:
                        $data["content"][] = Elements::getLabel(Language::get("form.formif.selectparts.parts.button2")."\n".Language::get("form.formif.recive", ["{form_data} <- false"]));
                        $data["content"][] = Elements::getInput(Language::get("form.formif.selectparts.parts.text"), "", $form["button2"]);
                        $data["content"][] = Elements::getToggle(Language::get("form.formif.selectparts.parts.editif"));
                        break;
                }
                break;
            case "form":
                if ($place == 0) {
                    $data["content"][] = Elements::getLabel(Language::get("form.formif.selectparts.parts.title"));
                    $data["content"][] = Elements::getInput(Language::get("form.formif.selectparts.parts.text"), "", $form["title"]);
                    break;
                }
                if ($place == 1) {
                    $data["content"][] = Elements::getLabel(Language::get("form.formif.selectparts.parts.content"));
                    $data["content"][] = Elements::getInput(Language::get("form.formif.selectparts.parts.text"), "", $form["content"]);
                    break;
                }
                $place -= 2;
                if (!isset($form["buttons"][$place])) {
                    $data["content"][] = Elements::getLabel(Language::get("form.formif.selectparts.addbutton"));
                    $data["content"][] = Elements::getInput(Language::get("form.formif.selectparts.parts.text"));
                    $data["content"][] = Elements::getToggle(Language::get("form.formif.selectparts.parts.editif"));
                    break;
                }
                $data["content"][] = Elements::getLabel(Language::get("form.formif.selectparts.parts.button")."\n".Language::get("form.formif.recive", ["{form_data} <- $place"]));
                $data["content"][] = Elements::getInput(Language::get("form.formif.selectparts.parts.text"), "", $form["buttons"][$place]["text"]);
                $data["content"][] = Elements::getToggle(Language::get("form.formif.selectparts.parts.editif"));
                break;
            case "custom_form":
                if ($place == 0) {
                    $data["content"][] = Elements::getLabel(Language::get("form.formif.selectparts.parts.title"));
                    $data["content"][] = Elements::getInput(Language::get("form.formif.selectparts.parts.text"), "", $form["title"]);
                    break;
                }
                $place -= 1;
                if (!isset($form["content"][$place])) {
                    $data["content"][] = Elements::getLabel(Language::get("form.formif.selectparts.addparts"));
                    $data["content"][] = Elements::getDropdown(Language::get("form.formif.selectparts.select_custom_parts"), array_keys($this->getCustomFormParts()));
                    break;
                }
                $parts = $form["content"][$place];
                $data["content"] = array_merge($data["content"], $this->getCustomFormParts($parts["type"], $parts, $place));
                break;
        }
        $json = Form::encodeJson($data);
        return $json;
    }

    public function onSelectParts($player, $data) {
        $session = Session::get($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
        $form = $session->getData("form");
        $place = $session->getData("form_select_place");
        if ($data[0]) {
            Form::sendForm($player, $this->getEditIFformForm($form, Language::get("form.cancelled")), $this, "onEditIFformForm");
            return;
        }
        $manager = ifPlugin::getInstance()->getFormIFManager();
        $datas = $manager->getIF($session->getData("if_key"));
        switch ($form["type"]) {
            case 'modal':
                switch ($place) {
                    case 0:
                        $partsname = "title";
                        break;
                    case 1:
                        $partsname = "content";
                        break;
                    case 2:
                        $partsname = "button1";
                        break;
                    case 3:
                        $partsname = "button2";
                        break;
                }
                $form[$partsname] = $data[2];
                if (isset($data[3]) and $data[3]) {
                    $session->setData("form", $form);
                    $datas["form"] = Form::encodeJson($form);
                    $manager->set($session->getData("if_key"), $datas);
                    $responses = array_filter($datas["ifs"], function ($ifs) use ($partsname) {
                        $comparison = false;
                        foreach ($ifs["if"] as $ifdata) {
                            if ($ifdata["id"] === Condition::COMPARISON and ($content = Condition::get(Condition::COMPARISON)->parse($ifdata["content"])) !== false) {
                                if ($content[0] !== "{form_data}" or $content[1] !== Comparison::EQUAL or $content[2] != ($partsname === "button1")) continue;
                                $comparison = true;
                            }
                        }
                        return $comparison;
                    });
                    if (count($responses) === 0) {
                        $session->setData("form_place", count($datas["ifs"]));
                        $options = ifPlugin::getInstance()->getOptionsBySession($session);
                        $manager->add($session->getData("if_key"), "if", Condition::COMPARISON, "{form_data}[ope:0]".($partsname === "button1" ? "true" : "false"), $options);
                        $ifdata = $manager->get($session->getData("if_key"), $options);
                    } else {
                        $session->setData("form_place", $place);
                        $ifdata = array_shift($responses);
                    }
                    $mes = Messages::createMessage($ifdata["if"], $ifdata["match"], $ifdata["else"]);
                    $form = (new Form)->getEditIfForm($mes);
                    Form::sendForm($player, $form, new Form(), "onEditIf");
                    return;
                }
                break;
            case "form":
                switch ($place) {
                    case 0:
                        $form["title"] = $data[2];
                        break;
                    case 1:
                        $form["content"] = $data[2];
                        break;
                    default:
                        $place -= 2;
                        $form["buttons"][$place] = ["text" => $data[2]];
                        if ($data[3]) {
                            $session->setData("form", $form);
                            $datas["form"] = Form::encodeJson($form);
                            $manager->set($session->getData("if_key"), $datas);
                            $responses = array_filter($datas["ifs"], function ($ifs) use ($place) {
                                $comparison = false;
                                foreach ($ifs["if"] as $ifdata) {
                                    if ($ifdata["id"] === Condition::COMPARISON and ($content = Condition::get(Condition::COMPARISON)->parse($ifdata["content"])) !== false) {
                                        if ($content[0] !== "{form_data}" or $content[1] !== Comparison::EQUAL or $content[2] !== $place) continue;
                                        $comparison = true;
                                    }
                                }
                                return $comparison;
                            });
                            if (count($responses) === 0) {
                                $session->setData("form_place", count($datas["ifs"]));
                                $options = ifPlugin::getInstance()->getOptionsBySession($session);
                                $manager->add($session->getData("if_key"), "if", Condition::COMPARISON, "{form_data}[ope:0]$place", $options);
                                $ifdata = $manager->get($session->getData("if_key"), $options);
                            } else {
                                $session->setData("form_place", $place);
                                $ifdata = array_shift($responses);
                            }
                            $mes = Messages::createMessage($ifdata["if"], $ifdata["match"], $ifdata["else"]);
                            $form = (new Form)->getEditIfForm($mes);
                            Form::sendForm($player, $form, new Form(), "onEditIf");
                            return;
                        }
                        break;
                }
                break;
            case "custom_form":
                switch ($place) {
                    case 0:
                        $form["title"] = $data[2];
                        break;
                    default:
                        $place -= 1;
                        if (!isset($form["content"][$place])) {
                            $partsname = array_keys($this->getCustomFormParts())[$data[2]];
                            $form["content"][] = Elements::{"get".$partsname}($partsname);
                            break;
                        }
                        $parts = $form["content"][$place];
                        switch ($parts["type"]) {
                            case 'label':
                                $form["content"][$place] = Elements::getLabel($data[2]);
                                break;
                            case "input":
                                $form["content"][$place] = Elements::getInput($data[2], $data[3], $data[4]);
                                break;
                            case "toggle":
                                $form["content"][$place] = Elements::getToggle($data[2], $data[3]);
                                break;
                            case "slider":
                                $form["content"][$place] = Elements::getSlider($data[2], (int)$data[3], (int)$data[4], (int)$data[5], (int)$data[6]);
                                break;
                            case "dropdown":
                                $options = $form["content"][$place]["options"];
                                $count = count($options);
                                for ($i=0; $i<$count; $i++) {
                                    $options[$i] = $data[$i+3];
                                }
                                $options = array_merge($options, explode(",", $data[$count+3]));
                                $options = array_filter($options, function ($option) {
                                    return(trim(rtrim($option)) !== "");
                                });
                                $options = array_values($options);
                                $form["content"][$place] = Elements::getDropdown($data[2], $options);
                                break;
                        }
                        break;
                }
                break;
        }
        $session->setData("form", $form);
        $datas["form"] = Form::encodeJson($form);
        $manager->set($session->getData("if_key"), $datas);
        $player->sendMessage(Language::get("form.changed"));
        Form::sendForm($player, $this->getEditIFformForm($form, Language::get("form.changed")), $this, "onEditIFformForm");
    }

    public function getCustomFormParts($name = "", $default = null, $place = null) {
        $parts = [
            "label" => [
                Elements::getLabel(Language::get("form.formif.custom.label")
                    .($place === null ? "" : "\n".Language::get("form.formif.recive", ["{form_data}[$place] <-\"\""]))),
                Elements::getInput(Language::get("form.formif.custom.text"), "", $default["text"] ?? ""),
            ],
            "input" => [
                Elements::getLabel(Language::get("form.formif.custom.input")
                    .($place === null ? "" : "\n".Language::get("form.formif.recive.input", [$place]))),
                Elements::getInput(Language::get("form.formif.custom.text"), "", $default["text"] ?? ""),
                Elements::getInput(Language::get("form.formif.custom.input.placeholder"), "", $default["placeholder"] ?? ""),
                Elements::getInput(Language::get("form.formif.custom.input.default"), "", $default["default"] ?? ""),
            ],
            "toggle" => [
                Elements::getLabel(Language::get("form.formif.custom.toggle")
                    .($place === null ? "" : "\n".Language::get("form.formif.recive", ["{form_data}[$place] <- (true | false)"]))),
                Elements::getInput(Language::get("form.formif.custom.text"), "", $default["text"] ?? ""),
                Elements::getToggle(Language::get("form.formif.custom.toggle.default"), $default["default"] ?? false),
            ],
            "slider" => [
                Elements::getLabel(Language::get("form.formif.custom.slider")
                    .($place === null ? "" : "\n".Language::get("form.formif.recive.slider", [$place]))),
                Elements::getInput(Language::get("form.formif.custom.text"), "", $default["text"] ?? ""),
                Elements::getInput(Language::get("form.formif.custom.slider.min"), "", $default["min"] ?? ""),
                Elements::getInput(Language::get("form.formif.custom.slider.max"), "", $default["max"] ?? ""),
                Elements::getInput(Language::get("form.formif.custom.slider.default"), "", $default["default"] ?? ""),
                Elements::getInput(Language::get("form.formif.custom.slider.step"), "", $default["step"] ?? ""),
            ],
            "dropdown" => [
                Elements::getLabel(Language::get("form.formif.custom.dropdown")
                    .($place === null ? "" : "\n".Language::get("form.formif.recive.dropdown", [$place]))),
                Elements::getInput(Language::get("form.formif.custom.text"), "", $default["text"] ?? ""),
            ],
        ];
        if (isset($default["options"])) {
            foreach ($default["options"] as $i => $option) {
                $parts["dropdown"][] = Elements::getInput(Language::get("form.formif.custom.dropdown.option", [$i]), "", $option);
            }
        }
        $parts["dropdown"][] = Elements::getInput(Language::get("form.formif.custom.dropdowm.addOption"));
        if (empty($name)) return $parts;
        return $parts[$name];
    }

    public function getIfListForm($name) {
        $manager = ifPlugin::getInstance()->getFormIFManager();
        $datas = $manager->getIF($name);
        $buttons = [Elements::getButton(Language::get("form.back")), Elements::getButton(Language::get("form.formif.iflist.add"))];
        foreach ($datas["ifs"] as $n => $data) {
            $buttons[] = Elements::getButton(empty($data["name"]) ? $n : $data["name"]);
        }
        $data = [
            "type" => "form",
            "title" => Language::get("form.formif.iflist.title"),
            "content" => Language::get("form.formif.iflist.content"),
            "buttons" => $buttons
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function onSelectIf($player, $data) {
        $session = Session::get($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
        if ($data === 0) {
            $form = $session->getData("form");
            Form::sendForm($player, $this->getEditIFformForm($form), $this, "onEditIFformForm");
            return;
        }
        $manager = ifPlugin::getInstance()->getFormIFManager();
        $datas = $manager->getIF($session->getData("if_key"));
        if ($data === 1) {
            $session->setData("form_place", count($datas["ifs"]));
            $mes = Messages::createMessage([], [], []);
            $form = (new Form)->getEditIfForm($mes);
            Form::sendForm($player, $form, new Form(), "onEditIf");
            return;
        }
        $session->setData("form_place", $data - 2);
        $datas = $datas["ifs"][$data-2];
        $mes = Messages::createMessage($datas["if"], $datas["match"], $datas["else"]);
        $form = (new Form)->getEditIfForm($mes);
        Form::sendForm($player, $form, new Form(), "onEditIf");
    }

    public function onDeleteIf($player, $data) {
        $session = Session::get($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
        $type = $session->getIfType();
        $manager = ifPlugin::getInstance()->getFormIFManager();

        if ($data) {
            $manager->removeIF($session->getData("if_key"));
            $player->sendMessage(Language::get("form.formif.deleted"));
        } else {
            $player->sendMessage(Language::get("form.cancelled"));
        }
        $session->setValid(false);
    }
}