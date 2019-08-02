<?php

namespace aieuo\ip\processes;

use pocketmine\event\Event;
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

	public function getCancelEvent() {
		return $this->getValues();
	}

	public function setCancelEvent(Event $event) {
		$this->setValues($event);
	}

	public function execute() {
		$event = $this->getCancelEvent();
		if($event instanceof Cancellable) $event->setCancelled();
	}
}