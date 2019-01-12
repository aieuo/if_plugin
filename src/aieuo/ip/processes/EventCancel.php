<?php

namespace aieuo\ip\processes;

use pocketmine\event;
use pocketmine\event\Cancellable;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class EventCancel extends Process
{
	public $id = self::EVENT_CANCEL;

	public function __construct($player = null, $effect = null)
	{
		parent::__construct($player);
		$this->setValues($effect);
	}

	public function getName()
	{
		return "イベントキャンセルする";
	}

	public function getDescription()
	{
		return "イベントをキャンセルします";
	}

	public function getEvent()
	{
		return $this->getValues();
	}

	public function setEvent(Event $effect)
	{
		$this->setValues($effect);
	}

	public function execute()
	{
		$event = $this->getEvent();
		if($event instanceof Cancellable) $event->setCancelled();
	}
}