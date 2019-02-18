<?php

namespace aieuo\ip\processes;

use pocketmine\event;
use pocketmine\event\Cancellable;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class EventCancel extends Process {

	protected $id = self::EVENT_CANCEL;
	protected $name = "イベントキャンセルする";
	protected $description = "イベントをキャンセルする";

	public function getMessage() {
		return "イベントをキャンセルする";
	}

	public function getEvent() {
		return $this->getValues();
	}

	public function setEvent(Event $effect) {
		$this->setValues($effect);
	}

	public function execute() {
		$event = $this->getEvent();
		if($event instanceof Cancellable) $event->setCancelled();
	}
}