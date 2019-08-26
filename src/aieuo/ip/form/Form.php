<?php

namespace aieuo\ip\form;

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

class Form {

    private static $forms = [];

    public static function sendForm($player, $form, $class, $func, $opOnly = true) {
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

    public static function onRecive($id, $player, $datas) {
        if (isset(self::$forms[$id])) {
            if ($player->isOp() or !self::$forms[$id][1]) {
                call_user_func_array(self::$forms[$id][0], [$player, $datas]);
            }
            unset(self::$forms[$id]);
        }
    }

//////////////////////////////////////////////////////////////
    public static function encodeJson($data) {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
        return $json;
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

    public function getSelectIfTypeForm() {
        $data = [
            "type" => "form",
            "title" => Language::get("form.form.selectAction.title"),
            "content" => Language::get("form.selectButton"),
            "buttons" => [
                Elements::getButton(Language::get("form.block")),
                Elements::getButton(Language::get("form.command")),
                Elements::getButton(Language::get("form.event")),
                Elements::getButton(Language::get("form.formif")),
                Elements::getButton(Language::get("form.chain")),
                Elements::getButton(Language::get("form.import")),
                Elements::getButton(Language::get("form.exit"))
            ]
        ];
        $json = self::encodeJson($data);
        return $json;
    }

    public function onSelectIfType($player, $data) {
        if ($data === null) return;
        $session = Session::getSession($player);
        switch ($data) {
            case 0:
                $session->set("if_type", Session::BLOCK);
                $form = $this->getBlockForm()->getSelectActionForm();
                Form::sendForm($player, $form, $this->getBlockForm(), "onSelectAction");
                break;
            case 1:
                $session->set("if_type", Session::COMMAND);
                $form = $this->getCommandForm()->getSelectActionForm();
                Form::sendForm($player, $form, $this->getCommandForm(), "onSelectAction");
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
                $form = $this->getChainForm()->getSelectActionForm();
                Form::sendForm($player, $form, $this->getChainForm(), "onSelectAction");
                break;
            case 5:
                $form = $this->getImportForm()->getImportListForm();
                Form::sendForm($player, $form, $this->getImportForm(), "onImportList");
                break;
            case 6:
                $session->setValid(false);
                break;
        }
    }

    public function getEditIfForm($mes, $name = null) {
        $data = [
            "type" => "form",
            "title" => empty($name) ? Language::get("form.form.editIF.title") : $name,
            "content" => $mes,
            "buttons" => [
                Elements::getButton(Language::get("form.form.editIF.if")),
                Elements::getButton(Language::get("form.form.editIF.match")),
                Elements::getButton(Language::get("form.form.editIF.else")),
                Elements::getButton(Language::get("form.action.delete")),
                Elements::getButton(Language::get("form.form.editIF.changeName")),
                Elements::getButton(Language::get("form.form.editIF.export")),
                Elements::getButton(Language::get("form.exit"))
            ]
        ];
        $data = self::encodeJson($data);
        return $data;
    }

    public function onEditIf($player, $data) {
        $session = Session::getSession($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
        $manager = IFManager::getBySession($session);
        $options = IFPlugin::getInstance()->getOptionsBySession($session);
        $key = $session->get("if_key");
        $datas = $manager->get($key, $options);
        if ($data == 0) {
            $form = $this->getEditContentsForm($datas["if"], "", "if");
            $session->set("type", "if");
        } elseif ($data == 1) {
            $form = $this->getEditContentsForm($datas["match"], "", "match");
            $session->set("type", "match");
        } elseif ($data == 2) {
            $form = $this->getEditContentsForm($datas["else"], "", "else");
            $session->set("type", "else");
        } elseif ($data == 3) {
            $form = $this->getConfirmDeleteForm();
            Form::sendForm($player, $form, $this, "onDeleteIf");
            return;
        } elseif ($data == 4) {
            $form = $this->getChangeNameForm(isset($datas["name"]) ? $datas["name"] : "");
            Form::sendForm($player, $form, $this, "onChangeName");
            return;
        } elseif ($data == 5) {
            $form = $this->getExportForm()->getExportForm();
            Form::sendForm($player, $form, $this->getExportForm(), "onExport");
            return;
        } else {
            $session->setValid(false);
            return;
        }
        Form::sendForm($player, $form, $this, "onEditIfContents");
    }

    public function getEditContentsForm($datas, $mes = "", $type = "") {
        $data = [
            "type" => "form",
            "title" => Language::get("form.form.editContents.title", [Language::get("form.form.editIF.".$type)]),
            "content" => ($mes === "" ? "" : $mes."\n").Language::get("form.selectButton"),
            "buttons" => []
        ];
        $data["buttons"] = [
            Elements::getButton(Language::get("form.back")),
            Elements::getButton(Language::get("form.form.editContents.add")),
        ];
        foreach ($datas as $key => $value) {
            if ($value["id"] < 100) {
                $content = Condition::get($value["id"]);
            } else {
                $content = Process::get($value["id"]);
            }
            $content->setValues($content->parse($value["content"]));
            $message = $content->getDetail();
            $data["buttons"][] = Elements::getButton($message === false ? $content->getDescription() : $message);
        }
        $data = self::encodeJson($data);
        return $data;
    }

    public function onEditIfContents($player, $data) {
        $session = Session::getSession($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
        $manager = IFManager::getBySession($session);
        $options = IFPlugin::getInstance()->getOptionsBySession($session);
        $key = $session->get("if_key");
        $datas = $manager->get($key, $options);
        if ($data == 0) { // ひとつ前のformに戻る
            $mes = IFAPI::createIFMessage($datas["if"], $datas["match"], $datas["else"]);
            $form = $this->getEditIfForm($mes, $datas["name"] ?? null);
            Form::sendForm($player, $form, $this, "onEditIf");
            return;
        }
        if ($data == 1) { // 新しく追加する
            $form = $this->getAddContentsForm($session->get("type"));
            Form::sendForm($player, $form, $this, "onAddContent");
            return;
        }

        // 追加されているものを選択した
        $data -= 2;
        $ifData = $datas[$session->get("type")][$data];
        if ($session->get("type") == "if") {
            $datas = Condition::get($ifData["id"]);
        } else {
            $datas = Process::get($ifData["id"]);
        }
        $session->set("contents", $datas);
        $session->set("num", $data);
        $form = $datas->getEditForm((string)$ifData["content"]);
        Form::sendForm($player, $form, $this, "onUpdateContent");
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
            $datas = $manager->get($key, $options);
            $form = $this->getEditContentsForm($datas[$session->get("type")], "", $session->get("type"));
            Form::sendForm($player, $form, $this, "onEditIfContents");
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
        $datas = $content->parseFormData($data);
        if ($datas["cancel"]) {
            $form = $this->getAddContentsForm($session->get("type"));
            Form::sendForm($player, $form, $this, "onAddContent");
            return;
        }
        if ($datas["delete"]) {
            $player->sendMessage(Language::get("form.form.delete.notExist"));
            $form = $this->getAddContentsForm($session->get("type"), Language::get("form.form.delete.notExist"));
            Form::sendForm($player, $form, $this, "onAddContent");
            return;
        }
        if ($datas["status"] === null) {
            $form = $content->getEditForm($datas["contents"], Language::get("form.insufficient"));
            Form::sendForm($player, $form, $this, "onEdit");
            return;
        }
        $mes = Language::get("form.form.added");
        if ($datas["status"] === false) $mes = Language::get("form.form.added.suspicious");
        $key = $session->get("if_key");
        $manager->add($key, $session->get("type"), $content->getId(), $datas["contents"], $options);
        $contents = $manager->get($key, $options);
        $form = $this->getEditContentsForm($contents[$session->get("type")], $mes, $session->get("type"));
        Form::sendForm($player, $form, $this, "onEditIfContents");
        $player->sendMessage($mes);
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
        $datas = $content->parseFormData($data);
        if ($datas["cancel"]) {
            $key = $session->get("if_key");
            $form = $this->getEditContentsForm($manager->get($key, $options)[$session->get("type")], "", $session->get("type"));
            Form::sendForm($player, $form, $this, "onEditIfContents");
            return;
        }
        if ($datas["delete"]) {
            $form = $this->getConfirmDeleteForm();
            Form::sendForm($player, $form, $this, "onDeleteContent");
            return;
        }
        if ($datas["status"] === null) {
            $form = $content->getEditForm($datas["contents"], Language::get("form.insufficient"));
            Form::sendForm($player, $form, $this, "onEdit");
            return;
        }

        $mes = Language::get("form.form.changed");
        if ($datas["status"] === false) $mes = Language::get("form.form.changed.suspicious");
        $key = $session->get("if_key");
        $manager->updateContent($key, $session->get("type"), $session->get("num"), $datas["contents"], $options);
        $contents = $manager->get($key, $options);
        $form = $this->getEditContentsForm($contents[$session->get("type")], $mes, $session->get("type"));
        Form::sendForm($player, $form, $this, "onEditIfContents");
        $player->sendMessage($mes);
    }

    public function getConfirmDeleteForm() {
        $data = [
            "type" => "modal",
            "title" => Language::get("form.form.confirmDelete.title"),
            "content" => Language::get("form.form.confirmDelete.content"),
            "button1" => Language::get("form.form.confirmDelete.yes"),
            "button2" => Language::get("form.form.confirmDelete.no")
        ];
        $data = self::encodeJson($data);
        return $data;
    }

    public function onDeleteContent($player, $data) {
        $session = Session::getSession($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
        $manager = IFManager::getBySession($session);
        $options = IFPlugin::getInstance()->getOptionsBySession($session);

        $key = $session->get("if_key");
        if ($data) {
            $manager->del($key, $session->get("type"), $session->get("num"), $options);
            $form = $this->getEditContentsForm($manager->get($key, $options)[$session->get("type")], Language::get("form.form.delete.success"), $session->get("type"));
            Form::sendForm($player, $form, $this, "onEditIfContents");
            $player->sendMessage(Language::get("form.form.delete.success"));
        } else {
            $contents = $manager->get($key, $options);
            $ifData = $contents[$session->get("type")][$session->get("num")];
            $form = $manager->get($key, $options)->getEditForm($ifData["contents"], Language::get("form.cancelled"));
            Form::sendForm($player, $form, $this, "onEdit");
            $player->sendMessage(Language::get("form.cancelled"));
        }
    }

    public function onDeleteIf($player, $data) {
        $session = Session::getSession($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
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
            $datas = $manager->get($key, $options);
            $form = $this->getEditContentsForm($datas[$session->get("type")], "", $session->get("type"));
            Form::sendForm($player, $form, $this, "onEditIfContents");
            return;
        }
        $data -= 1;
        $all = $session->get("searchResult");
        if ($session->get("type") == "if") {
            $datas = Condition::get(current(array_slice($all, $data, 1, true))->getId());
        } else {
            $datas = Process::get(current(array_slice($all, $data, 1, true))->getId());
        }
        $session->set("contents", $datas);
        $form = $datas->getEditForm();
        Form::sendForm($player, $form, $this, "onEdit");
    }

    public function getChangeNameForm($name = "") {
        $data = [
            "type" => "custom_form",
            "title" => Language::get("form.form.setName.title"),
            "content" => [
                Elements::getLabel(Language::get("form.form.setName.content0")),
                Elements::getInput(Language::get("form.form.setName.content1"), "", $name),
                Elements::getToggle(Language::get("form.form.setName.content2")),
                Elements::getToggle(Language::get("form.cancel")),
            ]
        ];
        $data = self::encodeJson($data);
        return $data;
    }

    public function onChangeName($player, $data) {
        $session = Session::getSession($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
        $manager = IFManager::getBySession($session);
        $options = IFPlugin::getInstance()->getOptionsBySession($session);
        $key = $session->get("if_key");
        $datas = $manager->get($key, $options);
        $mes = IFAPI::createIFMessage($datas["if"], $datas["match"], $datas["else"]);
        if ($data[3]) {
            $form = $this->getEditIfForm($mes, $datas["name"] ?? null);
            Form::sendForm($player, $form, $this, "onEditIf");
            return;
        }
        if ($data[2]) {
            $mes = Language::get("form.deleteName.success")."\n".$mes;
            $player->sendMessage(Language::get("form.deleteName.success"));
            $manager->setName($key, "", $options);
        } else {
            $mes = Language::get("form.form.setName.success")."\n".$mes;
            $player->sendMessage(Language::get("form.form.setName.success"));
            $manager->setName($key, $data[1], $options);
        }
        $form = $this->getEditIfForm($mes, $datas["name"] ?? null);
        Form::sendForm($player, $form, $this, "onEditIf");
    }
}
