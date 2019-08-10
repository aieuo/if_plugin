<?php

namespace aieuo\ip\form;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

use aieuo\ip\ifPlugin;
use aieuo\ip\Session;
use aieuo\ip\utils\Messages;

use aieuo\ip\conditions\Condition;
use aieuo\ip\conditions\ConditionFactory;
use aieuo\ip\processes\Process;
use aieuo\ip\processes\ProcessFactory;

class Form {

    private static $forms = [];

    public static function sendForm($player, $form, $class, $func){
        while(true) {
            $id = mt_rand(0, 999999999);
            if(!isset(self::$forms[$id])) break;
        }
        self::$forms[$id] = [$class, $func];
        $pk = new ModalFormRequestPacket();
        $pk->formId = $id;
        $pk->formData = $form;
        $player->dataPacket($pk);
    }

    public static function onRecive($id, $player, $datas) {
        if(isset(self::$forms[$id])) {
            call_user_func_array(self::$forms[$id], [$player, $datas]);
            unset(self::$forms[$id]);
        }
    }

//////////////////////////////////////////////////////////////
    public static function encodeJson($data){
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

    public function getSelectIfTypeForm(){
        $data = [
            "type" => "form",
            "title" => "選択",
            "content" => "§7ボタンを押してください",
            "buttons" => [
                Elements::getButton("ブロック"),
                Elements::getButton("コマンド"),
                Elements::getButton("イベント"),
                Elements::getButton("フォーム"),
                Elements::getButton("チェーン"),
                Elements::getButton("ファイルインポート"),
                Elements::getButton("終了")
            ]
        ];
        $json = self::encodeJson($data);
        return $json;
    }

    public function onSelectIfType($player, $data) {
        if($data === null) return;
        $session = Session::get($player);
        switch ($data) {
            case 0:
                $session->setIfType(Session::BLOCK);
                $form = $this->getBlockForm()->getSelectActionForm();
                Form::sendForm($player, $form, $this->getBlockForm(), "onSelectAction");
                break;
            case 1:
                $session->setIfType(Session::COMMAND);
                $form = $this->getCommandForm()->getSelectActionForm();
                Form::sendForm($player, $form, $this->getCommandForm(), "onSelectAction");
                break;
            case 2:
                $session->setIfType(Session::EVENT);
                $form = $this->getEventForm()->getSelectEventForm();
                Form::sendForm($player, $form, $this->getEventForm(), "onSelectEvent");
                break;
            case 3:
                $session->setIfType(Session::FORM);
                $form = $this->getFormIFForm()->getSelectActionForm();
                Form::sendForm($player, $form, $this->getFormIFForm(), "onSelectAction");
                break;
            case 4:
                $session->setIfType(Session::CHAIN);
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
            "title" => empty($name) ? "IF編集" : $name,
            "content" => $mes,
            "buttons" => [
                Elements::getButton("もし~を編集する"),
                Elements::getButton("条件に合った時を編集する"),
                Elements::getButton("条件に合わなかった時を編集する"),
                Elements::getButton("削除する"),
                Elements::getButton("名前を設定する"),
                Elements::getButton("ファイル出力する"),
                Elements::getButton("終了")
            ]
        ];
        $data = self::encodeJson($data);
        return $data;
    }

    public function onEditIf($player, $data) {
        $session = Session::get($player);
        if($data === null) {
            $session->setValid(false, false);
            return;
        }
        $type = $session->getIfType();
        $manager = ifPlugin::getInstance()->getManagerBySession($session);
        $options = ifPlugin::getInstance()->getOptionsBySession($session);
        $key = $session->getData("if_key");
        $datas = $manager->get($key, $options);
        if($data == 0) {
            $form = $this->getEditContentsForm($datas["if"]);
            $session->setData("type", "if");
        } elseif($data == 1) {
            $form = $this->getEditContentsForm($datas["match"]);
            $session->setData("type", "match");
        } elseif($data == 2) {
            $form = $this->getEditContentsForm($datas["else"]);
            $session->setData("type", "else");
        } elseif($data == 3) {
            $form = $this->getConfirmDeleteForm();
            Form::sendForm($player, $form, $this, "onDeleteIf");
            return;
        } elseif($data == 4) {
            $form = $this->getChangeNameForm(isset($datas["name"]) ? $datas["name"] : "");
            Form::sendForm($player, $form, $this, "onChangeName");
            return;
        } elseif($data == 5) {
            $form = $this->getExportForm()->getExportForm();
            Form::sendForm($player, $form, $this->getExportForm(), "onExport");
            return;
        } else {
            $session->setValid(false);
            return;
        }
        Form::sendForm($player, $form, $this, "onEditIfContents");
    }

    public function getEditContentsForm($datas, $mes = ""){
        $data = [
            "type" => "form",
            "title" => "編集",
            "content" => ($mes === "" ? "" : $mes."\n")."§7ボタンを押してください",
            "buttons" => []
        ];
        $data["buttons"] = [Elements::getButton("<1つ前のページに戻る>"), Elements::getButton("<追加する>")];
        foreach ($datas as $key => $value) {
            if($value["id"] < 100) {
                $content = Condition::get($value["id"]);
            } else {
                $content = Process::get($value["id"]);
            }
            $content->setValues($content->parse($value["content"]));
            $message = $content->getMessage();
            $data["buttons"][] = Elements::getButton($message === false ? $content->getDescription() : $message);
        }
        $data = self::encodeJson($data);
        return $data;
    }

    public function onEditIfContents($player, $data) {
        $session = Session::get($player);
        if($data === null) {
            $session->setValid(false, false);
            return;
        }
        $type = $session->getIfType();
        $manager = ifPlugin::getInstance()->getManagerBySession($session);
        $options = ifPlugin::getInstance()->getOptionsBySession($session);
        $key = $session->getData("if_key");
        $datas = $manager->get($key, $options);
        if($data == 0) {
            // ひとつ前のformに戻る
            $mes = Messages::createMessage($datas["if"], $datas["match"], $datas["else"]);
            $form = $this->getEditIfForm($mes, $datas["name"] ?? null);
            Form::sendForm($player, $form, $this, "onEditIf");
            return;
        }
        if($data == 1) {
            // 新しく追加する
            $form = $this->getAddContentsForm($session->getData("type"));
            Form::sendForm($player, $form, $this, "onAddContent");
            return;
        }

        // 追加されているものを選択した
        $ifData = $datas[$session->getData("type")][$data - 2];
        if($session->getData("type") == "if") {
            $datas = Condition::get($ifData["id"]);
        } else {
            $datas = Process::get($ifData["id"]);
        }
        $session->setData("contents", $datas);
        $session->setData("num", $data - 2);
        $form = $datas->getEditForm((string)$ifData["content"]);
        Form::sendForm($player, $form, $this, "onUpdateContent");
    }

    public function getAddContentsForm($type, $mes = ""){
        if($type == "if"){
            $datas = ConditionFactory::getAll();
        }else{
            $datas = ProcessFactory::getAll();
        }
        $buttons[] = Elements::getButton("<ひとつ前のページに戻る>");
        $buttons[] = Elements::getButton("<検索する>");
        foreach ($datas as $data) {
            $buttons[] = Elements::getButton($data->getName());
        }
        $data = [
            "type" => "form",
            "title" => "編集 > 追加",
            "content" => ($mes === "" ? "" : $mes."\n")."§7ボタンを押してください",
            "buttons" => $buttons
        ];
        $json = self::encodeJson($data);
        return $json;
    }

    public function onAddContent($player, $data) {
        $session = Session::get($player);
        if($data === null) {
            $session->setValid(false, false);
            return;
        }
        $type = $session->getIfType();
        $manager = ifPlugin::getInstance()->getManagerBySession($session);
        $options = ifPlugin::getInstance()->getOptionsBySession($session);
        if($data == 0) {
            $key = $session->getData("if_key");
            $datas = $manager->get($key, $options);
            $form = $this->getEditContentsForm($datas[$session->getData("type")]);
            Form::sendForm($player, $form, $this, "onEditIfContents");
            return;
        }
        if($data == 1) {
            $form = $this->getSearchForm();
            Form::sendForm($player, $form, $this, "onSearch");
            return;
        }
        $data -= 2;
        if($session->getData("type") == "if") {
            $all = ConditionFactory::getAll();
            $datas = Condition::get(current(array_slice($all, $data, 1, true))->getId());
        } else {
            $all = ProcessFactory::getAll();
            $datas = Process::get(current(array_slice($all, $data, 1, true))->getId());
        }
        $session->setData("contents", $datas);
        $form = $datas->getEditForm();
        Form::sendForm($player, $form, $this, "onEdit");
    }

    public function onEdit($player, $data) {
        $session = Session::get($player);
        if($data === null) {
            $session->setValid(false, false);
            return;
        }
        $type = $session->getIfType();
        $manager = ifPlugin::getInstance()->getManagerBySession($session);
        $options = ifPlugin::getInstance()->getOptionsBySession($session);
        $content = $session->getData("contents");
        $datas = $content->parseFormData($data);
        if($datas["cancel"]) {
            $form = $this->getAddContentsForm($session->getData("type"));
            Form::sendForm($player, $form, $this, "onAddContent");
            return;
        }
        if($datas["delete"]) {
            $player->sendMessage("§cまだ追加していないので削除できません");
            $form = $this->getAddContentsForm($session->getData("type"), "§cまだ追加していないので削除できません§f");
            Form::sendForm($player, $form, $this, "onAddContent");
            return;
        }
        if($datas["status"] === null) {
            $form = $content->getEditForm($datas["contents"], "§c必要事項を記入してください§f\n");
            Form::sendForm($player, $form, $this, "onEdit");
            return;
        }
        $mes = "§b追加しました§f";
        if($datas["status"] === false) $mes = "§e追加しましたが、正しく入力できていない可能性があります§f";
        $key = $session->getData("if_key");
        $manager->add($key, $session->getData("type"), $content->getId(), $datas["contents"], $options);
        $contents = $manager->get($key, $options);
        $form = $this->getEditContentsForm($contents[$session->getData("type")], $mes);
        Form::sendForm($player, $form, $this, "onEditIfContents");
        $player->sendMessage($mes);
    }

    public function onUpdateContent($player, $data) {
        $session = Session::get($player);
        if($data === null) {
            $session->setValid(false, false);
            return;
        }
        $type = $session->getIfType();
        $manager = ifPlugin::getInstance()->getManagerBySession($session);
        $options = ifPlugin::getInstance()->getOptionsBySession($session);
        $content = $session->getData("contents");
        $datas = $content->parseFormData($data);
        if($datas["cancel"]) {
            $key = $session->getData("if_key");
            $form = $this->getEditContentsForm($manager->get($key, $options)[$session->getData("type")]);
            Form::sendForm($player, $form, $this, "onEditIfContents");
            return;
        }
        if($datas["delete"]) {
            $form = $this->getConfirmDeleteForm();
            Form::sendForm($player, $form, $this, "onDeleteContent");
            return;
        }
        if($datas["status"] === null) {
            $form = $content->getEditForm($datas["contents"], "§c必要事項を記入してください§f\n");
            Form::sendForm($player, $form, $this, "onEdit");
            return;
        }

        $mes = "§b変更しました§f";
        if($datas["status"] === false) $mes = "§e変更しましたが、正しく入力できていない可能性があります§f";
        $key = $session->getData("if_key");
        $manager->updateContent($key, $session->getData("type"), $session->getData("num"), $datas["contents"], $options);
        $contents = $manager->get($key, $options);
        $form = $this->getEditContentsForm($contents[$session->getData("type")], $mes);
        Form::sendForm($player, $form, $this, "onEditIfContents");
        $player->sendMessage($mes);
    }

    public function getConfirmDeleteForm() {
        $data = [
            "type" => "modal",
            "title" => "削除",
            "content" => "本当に削除しますか?\n削除すると元に戻せません",
            "button1" => "はい",
            "button2" => "いいえ"
        ];
        $data = self::encodeJson($data);
        return $data;
    }

    public function onDeleteContent($player, $data) {
        $session = Session::get($player);
        if($data === null) {
            $session->setValid(false, false);
            return;
        }
        $type = $session->getIfType();
        $manager = ifPlugin::getInstance()->getManagerBySession($session);
        $options = ifPlugin::getInstance()->getOptionsBySession($session);

        $key = $session->getData("if_key");
        if($data) {
            $manager->del($key, $session->getData("type"), $session->getData("num"), $options);
            $form = $this->getEditContentsForm($manager->get($key, $options)[$session->getData("type")], "§b削除しました§f");
            Form::sendForm($player, $form, $this, "onEditIfContents");
            $player->sendMessage("削除しました");
        } else {
            $contents = $manager->get($key, $options);
            $ifData = $contents[$session->getData("type")][$session->getData("num")];
            $form = $manager->get($key, $options)->getEditForm($ifData["contents"], "§eキャンセルしました§f\n");
            Form::sendForm($player, $form, $this, "onEdit");
            $player->sendMessage("キャンセルしました");
        }
    }

    public function onDeleteIf($player, $data) {
        $session = Session::get($player);
        if($data === null) {
            $session->setValid(false, false);
            return;
        }
        $type = $session->getIfType();
        $manager = ifPlugin::getInstance()->getManagerBySession($session);
        $options = ifPlugin::getInstance()->getOptionsBySession($session);

        if($data) {
            $manager->remove($session->getData("if_key"), $options);
            $player->sendMessage("削除しました");
        } else {
            $player->sendMessage("削除キャンセルしました");
        }
        $session->setValid(false);
    }

    public function getSearchForm($mes = "") {
        $data = [
            "type" => "custom_form",
            "title" => "IF検索",
            "content" => [
                Elements::getLabel("名前で検索します".(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("検索する言葉を入力してください")
            ]
        ];
        $data = self::encodeJson($data);
        return $data;
    }

    public function onSearch($player, $data) {
        $session = Session::get($player);
        if($data === null) {
            $session->setValid(false, false);
            return;
        }
        if($data[1] === "") {
            $form = $this->getSearchForm("§c必要事項を記入してください§f");
            Form::sendForm($player, $form, $this, "onSearch");
            return;
        }
        if($session->getData("type") == "if") {
            $all = ConditionFactory::getAll();
        } else {
            $all = ProcessFactory::getAll();
        }
        $keywords = explode(" ", $data[1]);
        $result = array_filter($all, function($item) use($keywords) {
            $contains = true;
            foreach ($keywords as $keyword) {
                if(strpos($item->getName(), $keyword) === false) $contains = false;
            }
            return($contains);
        });
        $session->setData("searchResult", $result);

        $buttons[] = Elements::getButton("<ひとつ前のページに戻る>");
        foreach ($result as $item) {
            $buttons[] = Elements::getButton($item->getName());
        }
        $data = [
            "type" => "form",
            "title" => "編集 > 追加",
            "content" => "§7ボタンを押してください",
            "buttons" => $buttons
        ];
        Form::sendForm($player, self::encodeJson($data), $this, "onSearchResult");
    }

    public function onSearchResult($player, $data) {
        $session = Session::get($player);
        if($data === null) {
            $session->setValid(false, false);
            return;
        }
        $type = $session->getIfType();
        $manager = ifPlugin::getInstance()->getManagerBySession($session);
        $options = ifPlugin::getInstance()->getOptionsBySession($session);
        if($data == 0) {
            $key = $session->getData("if_key");
            $datas = $manager->get($key, $options);
            $form = $this->getEditContentsForm($datas[$session->getData("type")]);
            Form::sendForm($player, $form, $this, "onEditIfContents");
            return;
        }
        $data -= 1;
        $all = $session->getData("searchResult");
        if($session->getData("type") == "if") {
            $datas = Condition::get(current(array_slice($all, $data, 1, true))->getId());
        } else {
            $datas = Process::get(current(array_slice($all, $data, 1, true))->getId());
        }
        $session->setData("contents", $datas);
        $form = $datas->getEditForm();
        Form::sendForm($player, $form, $this, "onEdit");
    }

    public function getChangeNameForm($name = "") {
        $data = [
            "type" => "custom_form",
            "title" => "名前設定",
            "content" => [
                Elements::getLabel("IFに名前を設定します"),
                Elements::getInput("名前を入力してください", "", $name),
                Elements::getToggle("名前を削除する"),
                Elements::getToggle("キャンセル"),
            ]
        ];
        $data = self::encodeJson($data);
        return $data;
    }

    public function onChangeName($player, $data) {
        $session = Session::get($player);
        if($data === null) {
            $session->setValid(false, false);
            return;
        }
        $type = $session->getIfType();
        $manager = ifPlugin::getInstance()->getManagerBySession($session);
        $options = ifPlugin::getInstance()->getOptionsBySession($session);
        $key = $session->getData("if_key");
        $datas = $manager->get($key, $options);
        $mes = Messages::createMessage($datas["if"], $datas["match"], $datas["else"]);
        if($data[3]) {
            $form = $this->getEditIfForm($mes, $datas["name"] ?? null);
            Form::sendForm($player, $form, $this, "onEditIf");
            return;
        }
        if($data[2]) {
            $mes = "§e名前を削除しました§f\n".$mes;
            $player->sendMessage("§e名前を削除しました");
            $manager->setName($key, "", $options);
        } else {
            $mes = "§b名前を設定しました§f\n".$mes;
            $player->sendMessage("§b名前を設定しました");
            $manager->setName($key, $data[1], $options);
        }
        $form = $this->getEditIfForm($mes, $datas["name"] ?? null);
        Form::sendForm($player, $form, $this, "onEditIf");
    }
}