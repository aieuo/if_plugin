<?php

namespace aieuo\ip\form;

use aieuo\ip\utils\Language;
use aieuo\ip\form\Form;
use aieuo\ip\Session;
use aieuo\ip\IFPlugin;
use aieuo\ip\IFAPI;

class EventForm {

    private $events = [
        "PlayerChatEvent" => "form.event.PlayerChatEvent",
        "PlayerCommandPreprocessEvent" => "form.event.PlayerCommandPreprocessEvent",
        "PlayerInteractEvent" => "form.event.PlayerInteractEvent",
        "PlayerJoinEvent" => "form.event.PlayerJoinEvent",
        "PlayerQuitEvent" => "form.event.PlayerQuitEvent",
        "BlockBreakEvent" => "form.event.BlockBreakEvent",
        "BlockPlaceEvent" => "form.event.BlockPlaceEvent",
        "EntityDamageEvent" => "form.event.EntityDamageEvent",
        "EntityAttackEvent" => "form.event.EntityAttackEvent",
        "PlayerToggleFlightEvent" => "form.event.PlayerToggleFlightEvent",
        "PlayerDeathEvent" => "form.event.PlayerDeathEvent",
        "EntityLevelChangeEvent" => "form.event.EntityLevelChangeEvent",
        "CraftItemEvent" => "form.event.CraftItemEvent",
        "PlayerDropItemEvent" => "form.event.PlayerDropItemEvent",
        "InventoryPickupItemEvent" => "form.event.InventoryPickupItemEvent"
    ];

    public function getEvents() {
        return $this->events;
    }

    public function getSelectEventForm(){
        $buttons = [Elements::getButton(Language::get("form.back"))];
        foreach ($this->getEvents() as $key => $event) {
            $buttons[] = Elements::getButton(Language::get($event));
        }
        $data = [
            "type" => "form",
            "title" => Language::get("form.event.selectEvent.title"),
            "content" => Language::get("form.selectButton"),
            "buttons" => $buttons
        ];
        return Form::encodeJson($data);
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
        $manager = IFPlugin::getInstance()->getEventManager();
        $data = $manager->getFromEvent($event);
        $buttons = [Elements::getButton(Language::get("form.back")), Elements::getButton(Language::get("form.event.IFList.add"))];
        foreach ($data as $n => $value) {
            $buttons[] = Elements::getButton(empty($value["name"]) ? $n : $value["name"]);
        }
        $form = [
            "type" => "form",
            "title" => Language::get("form.event.IFList.title", [Language::get("form.event.".$event)]),
            "content" => Language::get("form.selectButton"),
            "buttons" => $buttons
        ];
        return Form::encodeJson($form);
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
            $ifData = $manager->repairIF([]);
            $mes = IFAPI::createIFMessage($ifData["if"], $ifData["match"], $ifData["else"]);
            $form = (new Form)->getEditIfForm($mes, $ifData["name"] ?? null);
            Form::sendForm($player, $form, new Form(), "onEditIf");
            return;
        }
        $session->set("if_key", $data - 2);
        $ifData = $manager->get(strval($data - 2), ["eventname" => $eventname]);
        $mes = IFAPI::createIFMessage($ifData["if"], $ifData["match"], $ifData["else"]);
        $form = (new Form)->getEditIfForm($mes, $ifData["name"] ?? null);
        Form::sendForm($player, $form, new Form(), "onEditIf");
    }
}