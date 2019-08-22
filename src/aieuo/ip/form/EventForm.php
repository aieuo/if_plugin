<?php

namespace aieuo\ip\form;

use aieuo\ip\utils\Language;
use aieuo\ip\form\Form;
use aieuo\ip\Session;
use aieuo\ip\IFPlugin;
use aieuo\ip\IFAPI;

class EventForm {

	private $events = [
        "PlayerChatEvent" => "プレイヤーがチャットしたとき",
        "PlayerCommandPreprocessEvent" => "プレイヤーがコマンドを実行したとき",
        "PlayerInteractEvent" => "プレイヤーがブロックを触ったとき",
        "PlayerJoinEvent" => "プレイヤーがサーバーに参加したとき",
        "PlayerQuitEvent" => "プレイヤーがサーバーから退室したとき",
        "BlockBreakEvent" => "プレイヤーがブロックを壊したとき",
        "BlockPlaceEvent" => "プレイヤーがブロックを置いたとき",
        "EntityDamageEvent" => "プレイヤーがダメージを受けたとき",
        "EntityAttackEvent" => "プレイヤーが攻撃したとき",
        "PlayerToggleFlightEvent" => "プレイヤーがフライ状態を切り替えたとき",
        "PlayerDeathEvent" => "プレイヤーが死亡したとき",
        "EntityLevelChangeEvent" => "プレイヤーがワールドを移動したとき",
        "CraftItemEvent" => "プレイヤーがクラフトしたとき",
        "PlayerDropItemEvent" => "プレイヤーがアイテムを捨てたとき",
    ];

    public function getEvents() {
        return $this->events;
    }

    public function getSelectEventForm(){
    	$buttons = [Elements::getButton("1つ前のページに戻る")];
        foreach ($this->getEvents() as $key => $event) {
            $buttons[] = Elements::getButton(Language::get($event));
        }
        $data = [
            "type" => "form",
            "title" => "event > イベント選択",
            "content" => "§7ボタンを押してください",
            "buttons" => $buttons
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function onSelectEvent($player, $data) {
        $session = Session::getSession($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
        if ($data === 0) {
            $form = (new Form())->getSelectIfTypeForm();
            Form::sendForm($player, $form, new Form(), "onSelectIfType");
            return;
        }
        $eventname = key(array_slice($this->getEvents(), $data-1, 1, true));
        $session->set("eventname", $eventname);
        $form = $this->getIfListForm($eventname);
        Form::sendForm($player, $form, $this, "onSelectIf");
        $session->setValid()->set("if_type", Session::EVENT);
    }

    public function getIfListForm($event) {
    	$buttons = [Elements::getButton("<1つ前のページに戻る>"), Elements::getButton("<追加する>")];
        $manager = IFPlugin::getInstance()->getEventManager();
        $datas = $manager->getFromEvent($event);
        foreach ($datas as $n => $data) {
            $buttons[] = Elements::getButton(empty($data["name"]) ? $n : $data["name"]);
        }
        $data = [
            "type" => "form",
            "title" => "event > $event > 選択",
            "content" => "§7ボタンを押してください",
            "buttons" => $buttons
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function onSelectIf($player, $data) {
        $session = Session::getSession($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
        if ($data === 0) {
            $form = $this->getSelectEventForm();
            Form::sendForm($player, $form, $this, "onSelectEvent");
            return;
        }
        $manager = IFPlugin::getInstance()->getEventManager();
        $eventname = $session->get("eventname");
        if ($data === 1) {
            $key = $manager->addEmpty($eventname);
            $session->set("if_key", $key);
            $datas = $manager->repairIF([]);
            $mes = IFAPI::createIFMessage($datas["if"], $datas["match"], $datas["else"]);
            $form = (new Form)->getEditIfForm($mes, $datas["name"] ?? null);
            Form::sendForm($player, $form, new Form(), "onEditIf");
            return;
        }
        $session->set("if_key", $data - 2);
        $datas = $manager->get(strval($data - 2), ["eventname" => $eventname]);
        $mes = IFAPI::createIFMessage($datas["if"], $datas["match"], $datas["else"]);
        $form = (new Form)->getEditIfForm($mes, $datas["name"] ?? null);
        Form::sendForm($player, $form, new Form(), "onEditIf");
    }
}