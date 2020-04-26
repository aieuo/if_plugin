<?php

namespace aieuo\ip\form;

use aieuo\ip\formAPI\CustomForm;
use aieuo\ip\formAPI\element\Button;
use aieuo\ip\formAPI\element\Input;
use aieuo\ip\formAPI\element\Label;
use aieuo\ip\formAPI\element\Toggle;
use aieuo\ip\formAPI\ListForm;
use aieuo\ip\formAPI\ModalForm;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

use aieuo\ip\utils\Language;
use aieuo\ip\processes\ProcessFactory;
use aieuo\ip\processes\Process;
use aieuo\ip\manager\IFManager;
use aieuo\ip\conditions\ConditionFactory;
use aieuo\ip\conditions\Condition;
use aieuo\ip\Session;
use aieuo\ip\IFPlugin;
use aieuo\ip\IFAPI;
use pocketmine\Player;

class Form {

    private static $forms = [];

    public static function sendForm(Player $player, $form, $class, $func, $opOnly = true) {
        $id = 0;
        while (true) {
            $id = mt_rand(0, 999999999);
            if (!isset(self::$forms[$id])) break;
        }
        self::$forms[$id] = [[$class, $func], $opOnly];
        $pk = new ModalFormRequestPacket();
        $pk->formId = $id;
        $pk->formData = $form;
        $player->dataPacket($pk);
    }

    public static function onReceive(int $id, Player $player, $data) {
        if (isset(self::$forms[$id])) {
            if ($player->isOp() or !self::$forms[$id][1]) {
                call_user_func_array(self::$forms[$id][0], [$player, $data]);
            }
            unset(self::$forms[$id]);
        }
    }

//////////////////////////////////////////////////////////////
    public static function encodeJson($data) {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
    }

//////////////////////////////////////////////////////////////
    public function getBlockForm() {
        return new BlockForm();
    }

    public function getCommandForm() {
        return new CommandForm();
    }

    public function getEventForm() {
        return new EventForm();
    }

    public function getChainForm() {
        return new ChainIfForm();
    }

    public function getFormIFForm() {
        return new FormIFForm();
    }

    public function getExportForm() {
        return new ExportForm();
    }

    public function getImportForm() {
        return new ImportForm();
    }

    public function sendSelectIfTypeForm(Player $player) {
        (new ListForm("@form.form.selectAction.title"))
            ->setContent("@form.selectButton")
            ->setButtons([
                new Button("@form.block"),
                new Button("@form.command"),
                new Button("@form.event"),
                new Button("@form.formif"),
                new Button("@form.chain"),
                new Button("@form.import"),
                new Button("@form.exit"),
            ])->onReceive(function (Player $player, int $data) {
                $session = Session::getSession($player);
                switch ($data) {
                    case 0:
                        $session->set("if_type", Session::BLOCK);
                        $this->getBlockForm()->sendSelectActionForm($player);
                        break;
                    case 1:
                        $session->set("if_type", Session::COMMAND);
                        $this->getCommandForm()->sendSelectActionForm($player);
                        break;
                    case 2:
                        $session->set("if_type", Session::EVENT);
                        $form = $this->getEventForm()->getSelectEventForm();
                        Form::sendForm($player, $form, $this->getEventForm(), "onSelectEvent");
                        break;
                    case 3:
                        $session->set("if_type", Session::FORM);
                        $form = $this->getFormIFForm()->getSelectActionForm();
                        Form::sendForm($player, $form, $this->getFormIFForm(), "onSelectAction");
                        break;
                    case 4:
                        $session->set("if_type", Session::CHAIN);
                        $this->getChainForm()->sendSelectActionForm($player);
                        break;
                    case 5:
                        $form = $this->getImportForm()->getImportListForm();
                        Form::sendForm($player, $form, $this->getImportForm(), "onImportList");
                        break;
                    case 6:
                        $session->setValid(false);
                        break;
                }
            })->show($player);
    }

    public function sendEditIfForm(Player $player, array $ifData, array $messages = []) {
        (new ListForm($ifData["name"] ?? "@form.form.editIF.title"))
            ->setContent(IFAPI::createIFMessage($ifData["if"], $ifData["match"], $ifData["else"]))
            ->setButtons([
                new Button("@form.form.editIF.if"),
                new Button("@form.form.editIF.match"),
                new Button("@form.form.editIF.else"),
                new Button("@form.action.delete"),
                new Button("@form.form.editIF.changeName"),
                new Button("@form.form.editIF.export"),
                new Button("@form.exit"),
            ])->onReceive(function (Player $player, int $data) {
                $session = Session::getSession($player);
                $manager = IFManager::getBySession($session);
                $options = IFPlugin::getInstance()->getOptionsBySession($session);
                $key = $session->get("if_key");
                $ifData = $manager->get($key, $options);
                switch ($data) {
                    case 0:
                        $session->set("type", "if");
                        $this->sendEditContentsForm($player, $ifData["if"], "if");
                        break;
                    case 1:
                        $session->set("type", "match");
                        $this->sendEditContentsForm($player, $ifData["match"], "match");
                        break;
                    case 2:
                        $session->set("type", "else");
                        $this->sendEditContentsForm($player, $ifData["else"], "else");
                        break;
                    case 3:
                        $this->confirmDelete($player, [$this, "onDeleteIf"]);
                        return;
                    case 4:
                        $this->sendChangeNameForm($player, $ifData["name"] ?? "");
                        return;
                    case 5:
                        $this->getExportForm()->sendExportForm($player);
                        return;
                    default:
                        $session->setValid(false);
                        return;
                }
            })->onClose(function (Player $player) {
                Session::getSession($player)->setValid(false, false);
            })->addMessages($messages)->show($player);
    }

    public function sendEditContentsForm(Player $player, array $ifData, string $type = "", array $messages = []) {
        $buttons = [
            new Button("@form.back"),
            new Button("@form.form.editContents.add"),
        ];
        foreach ($ifData as $key => $value) {
            if ($value["id"] < 100) {
                $content = Condition::get($value["id"]);
            } else {
                $content = Process::get($value["id"]);
            }
            $content->setValues($content->parse($value["content"]));
            $message = $content->getDetail();
            $buttons[] = new Button($message === false ? $content->getDescription() : $message);
        }
        (new ListForm(Language::get("form.form.editContents.title", [Language::get("form.form.editIF.".$type)])))
            ->setContent("@form.selectButton")
            ->setButtons($buttons)
            ->onReceive(function (Player $player, int $data) {
                $session = Session::getSession($player);
                $manager = IFManager::getBySession($session);
                $options = IFPlugin::getInstance()->getOptionsBySession($session);
                $key = $session->get("if_key");
                $ifData = $manager->get($key, $options);
                if ($data == 0) { // ひとつ前のformに戻る
                    (new Form)->sendEditIfForm($player, $ifData);
                    return;
                }
                if ($data == 1) { // 新しく追加する
                    $form = $this->getAddContentsForm($session->get("type"));
                    Form::sendForm($player, $form, $this, "onAddContent");
                    return;
                }

                // 追加されているものを選択した
                $data -= 2;
                $contents = $ifData[$session->get("type")][$data];
                if ($session->get("type") == "if") {
                    $content = Condition::get($contents["id"]);
                } else {
                    $content = Process::get($contents["id"]);
                }
                $session->set("contents", $content);
                $session->set("num", $data);
                $form = $content->getEditForm((string)$contents["content"]);
                Form::sendForm($player, $form, $this, "onUpdateContent");
            })->onClose(function (Player $player) {
                Session::getSession($player)->setValid(false, false);
            })->addMessages($messages)->show($player);
    }

    public function getAddContentsForm($type, $mes = "") {
        if ($type == "if") {
            $datas = ConditionFactory::getAll();
        } else {
            $datas = ProcessFactory::getAll();
        }
        $buttons[] = Elements::getButton(Language::get("form.back"));
        $buttons[] = Elements::getButton(Language::get("form.form.addContents.search"));
        foreach ($datas as $data) {
            $buttons[] = Elements::getButton($data->getName());
        }
        $data = [
            "type" => "form",
            "title" => Language::get("form.form.addContents.title"),
            "content" => ($mes === "" ? "" : $mes."\n").Language::get("form.selectButton"),
            "buttons" => $buttons
        ];
        $json = self::encodeJson($data);
        return $json;
    }

    public function onAddContent($player, $data) {
        $session = Session::getSession($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
        $manager = IFManager::getBySession($session);
        $options = IFPlugin::getInstance()->getOptionsBySession($session);
        if ($data == 0) {
            $key = $session->get("if_key");
            $ifData = $manager->get($key, $options);
            $this->sendEditContentsForm($player, $ifData[$session->get("type")], $session->get("type"));
            return;
        }
        if ($data == 1) {
            $form = $this->getSearchForm();
            Form::sendForm($player, $form, $this, "onSearch");
            return;
        }
        $data -= 2;
        if ($session->get("type") == "if") {
            $all = ConditionFactory::getAll();
            $datas = Condition::get(current(array_slice($all, $data, 1, true))->getId());
        } else {
            $all = ProcessFactory::getAll();
            $datas = Process::get(current(array_slice($all, $data, 1, true))->getId());
        }
        $session->set("contents", $datas);
        $form = $datas->getEditForm();
        Form::sendForm($player, $form, $this, "onEdit");
    }

    public function onEdit($player, $data) {
        $session = Session::getSession($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
        $manager = IFManager::getBySession($session);
        $options = IFPlugin::getInstance()->getOptionsBySession($session);
        $content = $session->get("contents");
        $parsed = $content->parseFormData($data);
        if ($parsed["cancel"]) {
            $form = $this->getAddContentsForm($session->get("type"));
            Form::sendForm($player, $form, $this, "onAddContent");
            return;
        }
        if ($parsed["delete"]) {
            $player->sendMessage(Language::get("form.form.delete.notExist"));
            $form = $this->getAddContentsForm($session->get("type"), Language::get("form.form.delete.notExist"));
            Form::sendForm($player, $form, $this, "onAddContent");
            return;
        }
        if ($parsed["status"] === null) {
            $form = $content->getEditForm($parsed["contents"], Language::get("form.insufficient"));
            Form::sendForm($player, $form, $this, "onEdit");
            return;
        }
        $mes = Language::get("form.form.added");
        if ($parsed["status"] === false) $mes = Language::get("form.form.added.suspicious");
        $key = $session->get("if_key");
        $manager->add($key, $session->get("type"), $content->getId(), $parsed["contents"], $options);
        $ifData = $manager->get($key, $options);
        $this->sendEditContentsForm($player, $ifData[$session->get("type")], $session->get("type"), [$mes]);
    }

    public function onUpdateContent($player, $data) {
        $session = Session::getSession($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
        $manager = IFManager::getBySession($session);
        $options = IFPlugin::getInstance()->getOptionsBySession($session);
        $content = $session->get("contents");
        $parsed = $content->parseFormData($data);
        if ($parsed["cancel"]) {
            $key = $session->get("if_key");
            $this->sendEditContentsForm($player, $manager->get($key, $options)[$session->get("type")], $session->get("type"));
            return;
        }
        if ($parsed["delete"]) {
            $this->confirmDelete($player, [$this, "onDeleteContent"]);
            return;
        }
        if ($parsed["status"] === null) {
            $form = $content->getEditForm($parsed["contents"], Language::get("form.insufficient"));
            Form::sendForm($player, $form, $this, "onEdit");
            return;
        }

        $mes = Language::get("form.form.changed");
        if ($parsed["status"] === false) $mes = Language::get("form.form.changed.suspicious");
        $key = $session->get("if_key");
        $manager->updateContent($key, $session->get("type"), $session->get("num"), $parsed["contents"], $options);
        $ifData = $manager->get($key, $options);
        $this->sendEditContentsForm($player, $ifData[$session->get("type")], $session->get("type"), [$mes]);
    }

    public function confirmDelete(Player $player, ?callable $onReceive = null) {
        (new ModalForm("@form.form.confirmDelete.title"))
            ->setContent("@form.form.confirmDelete.content")
            ->setButton1("@form.yes")
            ->setButton2("@form.no")
            ->onReceive(function (Player $player, bool $data) use ($onReceive) {
                if (is_callable($onReceive)) call_user_func($onReceive, $player, $data);
            })->show($player);
    }

    public function onDeleteContent(Player $player, bool $data) {
        $session = Session::getSession($player);
        $manager = IFManager::getBySession($session);
        $options = IFPlugin::getInstance()->getOptionsBySession($session);

        $key = $session->get("if_key");
        if ($data) {
            $manager->del($key, $session->get("type"), $session->get("num"), $options);
            $this->sendEditContentsForm($player, $manager->get($key, $options)[$session->get("type")], $session->get("type"), ["@form.form.delete.success"]);
        } else {
            $ifData = $manager->get($key, $options);
            $contents = $ifData[$session->get("type")][$session->get("num")];
            if ($session->get("type") == "if") {
                $content = Condition::get($contents["id"]);
            } else {
                $content = Process::get($contents["id"]);
            }
            $form = $content->getEditForm($contents["contents"], Language::get("form.cancelled"));
            Form::sendForm($player, $form, $this, "onEdit");
            $player->sendMessage(Language::get("form.cancelled"));
        }
    }

    public function onDeleteIf(Player $player, bool $data) {
        $session = Session::getSession($player);
        $manager = IFManager::getBySession($session);
        $options = IFPlugin::getInstance()->getOptionsBySession($session);

        if ($data) {
            $manager->remove($session->get("if_key"), $options);
            $player->sendMessage(Language::get("form.form.delete.success"));
        } else {
            $player->sendMessage(Language::get("form.cancelled"));
        }
        $session->setValid(false);
    }

    public function getSearchForm($mes = "") {
        $data = [
            "type" => "custom_form",
            "title" => Language::get("form.form.search.title"),
            "content" => [
                Elements::getLabel(Language::get("form.form.search.label").(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("form.form.search.input"))
            ]
        ];
        $data = self::encodeJson($data);
        return $data;
    }

    public function onSearch($player, $data) {
        $session = Session::getSession($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
        if ($data[1] === "") {
            $form = $this->getSearchForm(Language::get("form.insufficient"));
            Form::sendForm($player, $form, $this, "onSearch");
            return;
        }
        if ($session->get("type") == "if") {
            $all = ConditionFactory::getAll();
        } else {
            $all = ProcessFactory::getAll();
        }
        $keywords = explode(" ", $data[1]);
        $result = array_filter($all, function ($item) use ($keywords) {
            $contains = true;
            foreach ($keywords as $keyword) {
                if (strpos($item->getName(), $keyword) === false) $contains = false;
            }
            return($contains);
        });
        $session->set("searchResult", $result);

        $buttons[] = Elements::getButton(Language::get("form.back"));
        foreach ($result as $item) {
            $buttons[] = Elements::getButton($item->getName());
        }
        $data = [
            "type" => "form",
            "title" => Language::get("form.form.searchResult.title"),
            "content" => Language::get("form.selectButton"),
            "buttons" => $buttons
        ];
        Form::sendForm($player, self::encodeJson($data), $this, "onSearchResult");
    }

    public function onSearchResult($player, $data) {
        $session = Session::getSession($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
        $manager = IFManager::getBySession($session);
        $options = IFPlugin::getInstance()->getOptionsBySession($session);
        if ($data == 0) {
            $key = $session->get("if_key");
            $ifData = $manager->get($key, $options);
            $this->sendEditContentsForm($player, $ifData[$session->get("type")], $session->get("type"));
            return;
        }
        $data -= 1;
        $all = $session->get("searchResult");
        if ($session->get("type") == "if") {
            $contents = Condition::get(current(array_slice($all, $data, 1, true))->getId());
        } else {
            $contents = Process::get(current(array_slice($all, $data, 1, true))->getId());
        }
        $session->set("contents", $contents);
        $form = $contents->getEditForm();
        Form::sendForm($player, $form, $this, "onEdit");
    }

    public function sendChangeNameForm(Player $player, string $name = "") {
        (new CustomForm("@form.form.setName.title"))
            ->setContents([
                new Label("@form.form.setName.content0"),
                new Input("@form.form.setName.content1", "", $name),
                new Toggle("@form.form.setName.content2"),
                new Toggle("@form.cancel"),
            ])->onReceive(function (Player $player, array $data) {
                $session = Session::getSession($player);
                $manager = IFManager::getBySession($session);
                $options = IFPlugin::getInstance()->getOptionsBySession($session);
                $key = $session->get("if_key");
                $ifData = $manager->get($key, $options);
                if ($data[3]) {
                    (new Form)->sendEditIfForm($player, $ifData);
                    return;
                }
                if ($data[2]) {
                    $mes = Language::get("form.deleteName.success");
                    $player->sendMessage(Language::get("form.deleteName.success"));
                    $manager->setName($key, "", $options);
                } else {
                    $mes = Language::get("form.form.setName.success");
                    $player->sendMessage(Language::get("form.form.setName.success"));
                    $manager->setName($key, $data[1], $options);
                }
                (new Form)->sendEditIfForm($player, $ifData, [$mes]);
            })->onClose(function (Player $player) {
                Session::getSession($player)->setValid(false, false);
            })->show($player);
    }
}
