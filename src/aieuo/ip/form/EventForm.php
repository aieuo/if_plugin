<?php

namespace aieuo\ip\form;

use aieuo\ip\ifAPI;
use aieuo\ip\ifPlugin;
use aieuo\ip\Session;
use aieuo\ip\utils\Messages;
use aieuo\ip\form\Form;

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
        "PlayerToggleFlightEvent" => "プレイヤーがフライ状態を切り替えたとき",
        "PlayerDeathEvent" => "プレイヤーが死亡したとき",
        "EntityLevelChangeEvent" => "プレイヤーがワールドを移動したとき",
        "CraftItemEvent" => "プレイヤーがクラフトしたとき"
    ];

    public function getEvents() {
    	return $this->events;
    }

    public function getSelectEventForm(){
    	$buttons = [Elements::getButton("1つ前のページに戻る")];
    	foreach ($this->getEvents() as $key => $event) {
    		$buttons[] = Elements::getButton($event);
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
        $session = ifAPI::getSession($player);
        if($data === null) {
            $session->setValid(false, false);
            return;
        }
        if($data === 0) {
            $form = (new Form())->getSelectIfTypeForm();
            Form::sendForm($player, $form, new Form(), "onSelectIfType");
            return;
        }
        $eventname = key(array_slice($this->getEvents(), $data-1, 1, true));
        $session->setData("eventname", $eventname);
        $form = $this->getIfListForm($eventname);
        Form::sendForm($player, $form, $this, "onSelectIf");
        $session->setIfType(Session::EVENT);
        $session->setValid();
    }

    public function getIfListForm($event) {
    	$manager = ifPlugin::getInstance()->getEventManager();
    	$datas = $manager->getFromEvent($event);
    	$buttons = [Elements::getButton("<1つ前のページに戻る>"), Elements::getButton("<追加する>")];
    	foreach ($datas as $n => $data) {
    		$buttons[] = Elements::getButton($n);
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
        $session = ifAPI::getSession($player);
        if($data === null) {
            $session->setValid(false, false);
            return;
        }
        if($data === 0) {
			$form = $this->getSelectEventForm();
			Form::sendForm($player, $form, $this, "onSelectEvent");
			return;
        }
        $manager = ifPlugin::getInstance()->getEventManager();
        $eventname = $session->getData("eventname");
        if($data === 1) {
            $key = $manager->add_empty($eventname);
        	$session->setData("if_key", $key);
	        $datas = $manager->repairIF([]);
	        $mes = Messages::createMessage($datas["if"], $datas["match"], $datas["else"]);
	        $form = (new Form)->getEditIfForm($mes);
	        Form::sendForm($player, $form, new Form(), "onEditIf");
        	return;
        }
        $session->setData("if_key", $data - 2);
        $datas = $manager->get($data - 2, ["eventname" => $eventname]);
        $mes = Messages::createMessage($datas["if"], $datas["match"], $datas["else"]);
        $form = (new Form)->getEditIfForm($mes);
        Form::sendForm($player, $form, new Form(), "onEditIf");
    }
}