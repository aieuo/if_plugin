<?php

namespace aieuo\ip\processes;

use pocketmine\event\Event;
use pocketmine\event\Cancellable;

class EventCancel extends Process {

    protected $id = self::EVENT_CANCEL;
    protected $name = "@process.eventcancel.name";
    protected $description = "@process.eventcancel.description";
    protected $detail = "@process.eventcancel.detail";

    public function getCancelEvent() {
        return $this->getValues();
    }

    public function setCancelEvent(Event $event) {
        $this->setValues($event);
    }

    public function execute() {
        $event = $this->getCancelEvent();
        if ($event instanceof Cancellable) $event->setCancelled();
    }
}